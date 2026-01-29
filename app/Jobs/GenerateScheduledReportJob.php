<?php

namespace App\Jobs;

use App\Mail\ScheduledReportMail;
use App\Models\ReportExecution;
use App\Models\ScheduledReport;
use App\Services\ReportGeneratorService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class GenerateScheduledReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public array $backoff = [30, 60, 120];
    public int $timeout = 600;

    public function __construct(
        public ScheduledReport $report,
        public ?int $triggeredBy = null,
        public string $triggerType = 'scheduled'
    ) {
        $this->onQueue('reports');
    }

    public function handle(ReportGeneratorService $generator): void
    {
        // Calculate date range
        [$from, $to] = $this->calculateDateRange();

        // Create execution record
        $execution = ReportExecution::create([
            'scheduled_report_id' => $this->report->id,
            'status' => 'pending',
            'trigger_type' => $this->triggerType,
            'triggered_by' => $this->triggeredBy,
            'report_date_from' => $from,
            'report_date_to' => $to,
        ]);

        try {
            $execution->markAsProcessing();

            // Generate report data
            $data = $generator->generateReportData($this->report, $from, $to);

            $pdfPath = null;
            $csvPath = null;
            $totalSize = 0;

            // Generate PDF if requested
            if (in_array('pdf', $this->report->formats)) {
                $pdfPath = $generator->generatePdf($this->report, $data, $from, $to);
                $totalSize += Storage::size($pdfPath);
            }

            // Generate CSV if requested
            if (in_array('csv', $this->report->formats)) {
                $csvPath = $generator->generateCsv($this->report, $data, $from, $to);
                $totalSize += Storage::size($csvPath);
            }

            // Send emails
            $sentCount = 0;
            $failedCount = 0;

            foreach ($this->report->recipients as $email) {
                try {
                    Mail::to($email)->send(new ScheduledReportMail(
                        $this->report,
                        $execution,
                        $data,
                        $pdfPath,
                        $csvPath
                    ));
                    $sentCount++;
                } catch (\Exception $e) {
                    $failedCount++;
                    Log::error("Failed to send report email", [
                        'report_id' => $this->report->id,
                        'email' => $email,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            // Update execution record
            $execution->markAsCompleted([
                'file_path' => $pdfPath,
                'file_path_csv' => $csvPath,
                'file_size' => $totalSize,
                'records_count' => $this->countRecords($data),
                'metadata' => $data['summary'] ?? null,
                'email_sent_count' => $sentCount,
                'email_failed_count' => $failedCount,
            ]);

            // Update report last sent and next run
            $this->report->update([
                'last_sent_at' => now(),
                'next_run_at' => $this->report->calculateNextRun(),
            ]);

            Log::info("Report generated successfully", [
                'report_id' => $this->report->id,
                'execution_id' => $execution->id,
                'emails_sent' => $sentCount,
            ]);

        } catch (\Exception $e) {
            $execution->markAsFailed($e->getMessage());

            Log::error("Report generation failed", [
                'report_id' => $this->report->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    protected function calculateDateRange(): array
    {
        $to = now()->subDay()->endOfDay();

        $from = match($this->report->frequency) {
            'daily' => now()->subDay()->startOfDay(),
            'weekly' => now()->subWeek()->startOfDay(),
            'monthly' => now()->subMonth()->startOfDay(),
            default => now()->subDay()->startOfDay(),
        };

        return [$from->toDateString(), $to->toDateString()];
    }

    protected function countRecords(array $data): int
    {
        $count = 0;

        foreach ($data as $key => $value) {
            if (is_array($value) || $value instanceof \Countable) {
                if (str_starts_with($key, 'by_') || str_ends_with($key, '_list')) {
                    $count += count($value);
                }
            }
        }

        return $count ?: ($data['summary']['total_calls'] ?? 0);
    }

    public function tags(): array
    {
        return ['reports', 'report:' . $this->report->id];
    }
}
