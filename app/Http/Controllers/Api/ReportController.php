<?php

namespace App\Http\Controllers\Api;

use App\Jobs\GenerateScheduledReportJob;
use App\Models\ReportExecution;
use App\Models\ScheduledReport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ReportController extends BaseApiController
{
    /**
     * List scheduled reports
     */
    public function index(Request $request): JsonResponse
    {
        $query = ScheduledReport::with(['customer', 'carrier', 'createdBy'])
            ->withCount('executions');

        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        if ($request->boolean('active', true)) {
            $query->active();
        }

        $reports = $query->orderBy('name')->get();

        return $this->success($reports);
    }

    /**
     * Get single report
     */
    public function show(ScheduledReport $report): JsonResponse
    {
        $report->load(['customer', 'carrier', 'createdBy']);
        $report->loadCount('executions');

        // Get recent executions
        $report->recent_executions = $report->executions()
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        return $this->success($report);
    }

    /**
     * Create scheduled report
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'type' => 'required|in:cdr_summary,customer_usage,carrier_performance,billing,qos_report,profit_loss,traffic_analysis',
            'frequency' => 'required|in:daily,weekly,monthly,custom',
            'cron_expression' => 'required_if:frequency,custom|nullable|string|max:100',
            'send_time' => 'date_format:H:i',
            'day_of_week' => 'required_if:frequency,weekly|nullable|integer|min:0|max:6',
            'day_of_month' => 'required_if:frequency,monthly|nullable|integer|min:1|max:31',
            'recipients' => 'required|array|min:1',
            'recipients.*' => 'email',
            'formats' => 'array',
            'formats.*' => 'in:pdf,csv',
            'customer_id' => 'nullable|exists:customers,id',
            'carrier_id' => 'nullable|exists:carriers,id',
            'filters' => 'nullable|array',
            'include_details' => 'boolean',
            'include_charts' => 'boolean',
            'active' => 'boolean',
        ]);

        $data['created_by'] = auth()->id();
        $data['formats'] = $data['formats'] ?? ['pdf'];

        $report = ScheduledReport::create($data);
        $report->update(['next_run_at' => $report->calculateNextRun()]);

        return $this->success($report, [], 201);
    }

    /**
     * Update scheduled report
     */
    public function update(Request $request, ScheduledReport $report): JsonResponse
    {
        $data = $request->validate([
            'name' => 'string|max:100',
            'description' => 'nullable|string',
            'type' => 'in:cdr_summary,customer_usage,carrier_performance,billing,qos_report,profit_loss,traffic_analysis',
            'frequency' => 'in:daily,weekly,monthly,custom',
            'cron_expression' => 'nullable|string|max:100',
            'send_time' => 'date_format:H:i',
            'day_of_week' => 'nullable|integer|min:0|max:6',
            'day_of_month' => 'nullable|integer|min:1|max:31',
            'recipients' => 'array|min:1',
            'recipients.*' => 'email',
            'formats' => 'array',
            'formats.*' => 'in:pdf,csv',
            'customer_id' => 'nullable|exists:customers,id',
            'carrier_id' => 'nullable|exists:carriers,id',
            'filters' => 'nullable|array',
            'include_details' => 'boolean',
            'include_charts' => 'boolean',
            'active' => 'boolean',
        ]);

        $report->update($data);

        // Recalculate next run if schedule changed
        if (isset($data['frequency']) || isset($data['send_time']) ||
            isset($data['day_of_week']) || isset($data['day_of_month'])) {
            $report->update(['next_run_at' => $report->calculateNextRun()]);
        }

        return $this->success($report->fresh());
    }

    /**
     * Delete scheduled report
     */
    public function destroy(ScheduledReport $report): JsonResponse
    {
        // Delete associated files
        foreach ($report->executions as $execution) {
            if ($execution->file_path) {
                Storage::delete($execution->file_path);
            }
            if ($execution->file_path_csv) {
                Storage::delete($execution->file_path_csv);
            }
        }

        $report->delete();

        return $this->success(['deleted' => true]);
    }

    /**
     * Trigger report manually
     */
    public function trigger(Request $request, ScheduledReport $report): JsonResponse
    {
        $userId = auth()->id();

        GenerateScheduledReportJob::dispatch($report, $userId, 'manual');

        return $this->success([
            'message' => 'Report generation queued',
            'report_id' => $report->id,
        ]);
    }

    /**
     * Get report executions
     */
    public function executions(Request $request, ScheduledReport $report): JsonResponse
    {
        $query = $report->executions()->with('triggeredBy');

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $perPage = min($request->input('per_page', 20), 100);
        $executions = $query->orderByDesc('created_at')->paginate($perPage);

        return $this->paginated($executions);
    }

    /**
     * Get single execution
     */
    public function showExecution(ReportExecution $execution): JsonResponse
    {
        $execution->load(['scheduledReport', 'triggeredBy']);

        return $this->success($execution);
    }

    /**
     * Download execution file
     */
    public function downloadExecution(ReportExecution $execution, string $format = 'pdf'): mixed
    {
        $path = $format === 'csv' ? $execution->file_path_csv : $execution->file_path;

        if (!$path || !Storage::exists($path)) {
            return $this->notFound('File not found');
        }

        return Storage::download($path);
    }

    /**
     * Get available report types
     */
    public function types(): JsonResponse
    {
        return $this->success([
            'types' => [
                'cdr_summary' => 'CDR Summary',
                'customer_usage' => 'Customer Usage',
                'carrier_performance' => 'Carrier Performance',
                'billing' => 'Billing Report',
                'qos_report' => 'Quality of Service',
                'profit_loss' => 'Profit/Loss Analysis',
                'traffic_analysis' => 'Traffic Analysis',
            ],
            'frequencies' => [
                'daily' => 'Daily',
                'weekly' => 'Weekly',
                'monthly' => 'Monthly',
                'custom' => 'Custom (Cron)',
            ],
            'formats' => ['pdf', 'csv'],
        ]);
    }
}
