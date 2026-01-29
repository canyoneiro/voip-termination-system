<?php

namespace App\Mail;

use App\Models\ReportExecution;
use App\Models\ScheduledReport;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class ScheduledReportMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public ScheduledReport $report,
        public ReportExecution $execution,
        public array $data,
        public ?string $pdfPath = null,
        public ?string $csvPath = null,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: sprintf(
                '%s - %s to %s',
                $this->report->name,
                $this->execution->report_date_from->format('M d, Y'),
                $this->execution->report_date_to->format('M d, Y')
            ),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.reports.scheduled',
            with: [
                'report' => $this->report,
                'execution' => $this->execution,
                'data' => $this->data,
                'summary' => $this->data['summary'] ?? [],
            ],
        );
    }

    public function attachments(): array
    {
        $attachments = [];

        if ($this->pdfPath && Storage::exists($this->pdfPath)) {
            $attachments[] = Attachment::fromStorage($this->pdfPath)
                ->as($this->getFilename('pdf'))
                ->withMime('application/pdf');
        }

        if ($this->csvPath && Storage::exists($this->csvPath)) {
            $attachments[] = Attachment::fromStorage($this->csvPath)
                ->as($this->getFilename('csv'))
                ->withMime('text/csv');
        }

        return $attachments;
    }

    protected function getFilename(string $extension): string
    {
        $slug = str($this->report->name)->slug();
        $dateRange = $this->execution->report_date_from->format('Ymd') . '-' .
                     $this->execution->report_date_to->format('Ymd');

        return "{$slug}_{$dateRange}.{$extension}";
    }
}
