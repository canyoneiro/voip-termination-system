<?php

namespace App\Services;

use App\Models\Cdr;
use App\Models\Carrier;
use App\Models\Customer;
use App\Models\QosDailyStat;
use App\Models\ScheduledReport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use League\Csv\Writer;

class ReportGeneratorService
{
    /**
     * Generate report data based on type
     */
    public function generateReportData(ScheduledReport $report, string $from, string $to): array
    {
        return match($report->type) {
            'cdr_summary' => $this->generateCdrSummary($report, $from, $to),
            'customer_usage' => $this->generateCustomerUsage($report, $from, $to),
            'carrier_performance' => $this->generateCarrierPerformance($report, $from, $to),
            'billing' => $this->generateBillingReport($report, $from, $to),
            'qos_report' => $this->generateQosReport($report, $from, $to),
            'profit_loss' => $this->generateProfitLossReport($report, $from, $to),
            'traffic_analysis' => $this->generateTrafficAnalysis($report, $from, $to),
            default => [],
        };
    }

    /**
     * Generate PDF report
     */
    public function generatePdf(ScheduledReport $report, array $data, string $from, string $to): string
    {
        $view = "reports.pdf.{$report->type}";

        $pdf = Pdf::loadView($view, [
            'report' => $report,
            'data' => $data,
            'from' => $from,
            'to' => $to,
            'generated_at' => now(),
        ]);

        $filename = $this->generateFilename($report, $from, $to, 'pdf');
        $path = "reports/{$filename}";

        Storage::put($path, $pdf->output());

        return $path;
    }

    /**
     * Generate CSV report
     */
    public function generateCsv(ScheduledReport $report, array $data, string $from, string $to): string
    {
        $filename = $this->generateFilename($report, $from, $to, 'csv');
        $path = "reports/{$filename}";

        $csv = Writer::createFromString();

        // Add headers based on report type
        $headers = $this->getCsvHeaders($report->type);
        $csv->insertOne($headers);

        // Add data rows
        $rows = $this->formatDataForCsv($report->type, $data);
        $csv->insertAll($rows);

        Storage::put($path, $csv->toString());

        return $path;
    }

    /**
     * CDR Summary Report
     */
    protected function generateCdrSummary(ScheduledReport $report, string $from, string $to): array
    {
        $query = Cdr::whereBetween('start_time', [$from, $to]);

        if ($report->customer_id) {
            $query->where('customer_id', $report->customer_id);
        }
        if ($report->carrier_id) {
            $query->where('carrier_id', $report->carrier_id);
        }

        $summary = $query->selectRaw('
            COUNT(*) as total_calls,
            SUM(CASE WHEN sip_code = 200 THEN 1 ELSE 0 END) as answered_calls,
            SUM(CASE WHEN sip_code >= 400 THEN 1 ELSE 0 END) as failed_calls,
            SUM(duration) as total_duration,
            SUM(billable_duration) as billable_duration,
            AVG(pdd) as avg_pdd,
            SUM(cost) as total_cost,
            SUM(price) as total_price,
            SUM(profit) as total_profit
        ')->first();

        $asr = $summary->total_calls > 0
            ? round(($summary->answered_calls / $summary->total_calls) * 100, 2)
            : 0;

        $acd = $summary->answered_calls > 0
            ? round($summary->total_duration / $summary->answered_calls / 60, 2)
            : 0;

        $byDay = $query->clone()
            ->selectRaw('DATE(start_time) as date, COUNT(*) as calls, SUM(billable_duration) as duration')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $bySipCode = $query->clone()
            ->selectRaw('sip_code, COUNT(*) as count')
            ->groupBy('sip_code')
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        return [
            'summary' => [
                'total_calls' => $summary->total_calls,
                'answered_calls' => $summary->answered_calls,
                'failed_calls' => $summary->failed_calls,
                'total_minutes' => round($summary->total_duration / 60, 2),
                'billable_minutes' => round($summary->billable_duration / 60, 2),
                'asr' => $asr,
                'acd' => $acd,
                'avg_pdd' => $summary->avg_pdd ? round($summary->avg_pdd) : null,
                'total_cost' => $summary->total_cost,
                'total_price' => $summary->total_price,
                'total_profit' => $summary->total_profit,
            ],
            'by_day' => $byDay,
            'by_sip_code' => $bySipCode,
        ];
    }

    /**
     * Customer Usage Report
     */
    protected function generateCustomerUsage(ScheduledReport $report, string $from, string $to): array
    {
        $query = Cdr::whereBetween('start_time', [$from, $to])
            ->whereNotNull('customer_id');

        if ($report->customer_id) {
            $query->where('customer_id', $report->customer_id);
        }

        $byCustomer = $query->clone()
            ->with('customer')
            ->selectRaw('
                customer_id,
                COUNT(*) as total_calls,
                SUM(CASE WHEN sip_code = 200 THEN 1 ELSE 0 END) as answered_calls,
                SUM(billable_duration) as billable_duration,
                SUM(price) as total_price
            ')
            ->groupBy('customer_id')
            ->orderByDesc('billable_duration')
            ->get()
            ->map(function ($row) {
                return [
                    'customer_id' => $row->customer_id,
                    'customer_name' => $row->customer?->name ?? 'Unknown',
                    'total_calls' => $row->total_calls,
                    'answered_calls' => $row->answered_calls,
                    'asr' => $row->total_calls > 0
                        ? round(($row->answered_calls / $row->total_calls) * 100, 2)
                        : 0,
                    'billable_minutes' => round($row->billable_duration / 60, 2),
                    'total_price' => $row->total_price,
                ];
            });

        return [
            'by_customer' => $byCustomer,
            'total_customers' => $byCustomer->count(),
            'total_minutes' => $byCustomer->sum('billable_minutes'),
            'total_revenue' => $byCustomer->sum('total_price'),
        ];
    }

    /**
     * Carrier Performance Report
     */
    protected function generateCarrierPerformance(ScheduledReport $report, string $from, string $to): array
    {
        $query = Cdr::whereBetween('start_time', [$from, $to])
            ->whereNotNull('carrier_id');

        if ($report->carrier_id) {
            $query->where('carrier_id', $report->carrier_id);
        }

        $byCarrier = $query->clone()
            ->with('carrier')
            ->selectRaw('
                carrier_id,
                COUNT(*) as total_calls,
                SUM(CASE WHEN sip_code = 200 THEN 1 ELSE 0 END) as answered_calls,
                SUM(CASE WHEN sip_code >= 400 THEN 1 ELSE 0 END) as failed_calls,
                SUM(duration) as total_duration,
                AVG(pdd) as avg_pdd,
                SUM(cost) as total_cost
            ')
            ->groupBy('carrier_id')
            ->orderByDesc('total_calls')
            ->get()
            ->map(function ($row) {
                return [
                    'carrier_id' => $row->carrier_id,
                    'carrier_name' => $row->carrier?->name ?? 'Unknown',
                    'total_calls' => $row->total_calls,
                    'answered_calls' => $row->answered_calls,
                    'failed_calls' => $row->failed_calls,
                    'asr' => $row->total_calls > 0
                        ? round(($row->answered_calls / $row->total_calls) * 100, 2)
                        : 0,
                    'acd' => $row->answered_calls > 0
                        ? round($row->total_duration / $row->answered_calls / 60, 2)
                        : 0,
                    'avg_pdd' => $row->avg_pdd ? round($row->avg_pdd) : null,
                    'total_minutes' => round($row->total_duration / 60, 2),
                    'total_cost' => $row->total_cost,
                ];
            });

        return [
            'by_carrier' => $byCarrier,
            'total_carriers' => $byCarrier->count(),
        ];
    }

    /**
     * Billing Report
     */
    protected function generateBillingReport(ScheduledReport $report, string $from, string $to): array
    {
        $query = Cdr::whereBetween('start_time', [$from, $to])
            ->where('sip_code', 200);

        if ($report->customer_id) {
            $query->where('customer_id', $report->customer_id);
        }

        $summary = $query->selectRaw('
            SUM(billable_duration) as billable_duration,
            SUM(cost) as total_cost,
            SUM(price) as total_price,
            SUM(profit) as total_profit
        ')->first();

        $byDestination = $query->clone()
            ->with('destinationPrefix')
            ->selectRaw('
                destination_prefix_id,
                COUNT(*) as calls,
                SUM(billable_duration) as duration,
                SUM(cost) as cost,
                SUM(price) as price,
                SUM(profit) as profit
            ')
            ->whereNotNull('destination_prefix_id')
            ->groupBy('destination_prefix_id')
            ->orderByDesc('price')
            ->limit(20)
            ->get()
            ->map(function ($row) {
                return [
                    'destination' => $row->destinationPrefix?->full_description ?? 'Unknown',
                    'calls' => $row->calls,
                    'minutes' => round($row->duration / 60, 2),
                    'cost' => $row->cost,
                    'price' => $row->price,
                    'profit' => $row->profit,
                ];
            });

        return [
            'summary' => [
                'billable_minutes' => round($summary->billable_duration / 60, 2),
                'total_cost' => round($summary->total_cost, 2),
                'total_price' => round($summary->total_price, 2),
                'total_profit' => round($summary->total_profit, 2),
                'margin_percent' => $summary->total_price > 0
                    ? round(($summary->total_profit / $summary->total_price) * 100, 2)
                    : 0,
            ],
            'by_destination' => $byDestination,
        ];
    }

    /**
     * QoS Report
     */
    protected function generateQosReport(ScheduledReport $report, string $from, string $to): array
    {
        $query = QosDailyStat::whereBetween('date', [$from, $to]);

        if ($report->customer_id) {
            $query->where('customer_id', $report->customer_id);
        }
        if ($report->carrier_id) {
            $query->where('carrier_id', $report->carrier_id);
        }

        $daily = $query->clone()
            ->whereNull('customer_id')
            ->whereNull('carrier_id')
            ->orderBy('date')
            ->get();

        $summary = [
            'total_calls' => $daily->sum('measured_calls'),
            'avg_mos' => $daily->avg('avg_mos'),
            'avg_pdd' => $daily->avg('avg_pdd'),
            'excellent_percent' => $daily->sum('measured_calls') > 0
                ? round($daily->sum('excellent_count') / $daily->sum('measured_calls') * 100, 2)
                : 0,
            'poor_percent' => $daily->sum('measured_calls') > 0
                ? round(($daily->sum('poor_count') + $daily->sum('bad_count')) / $daily->sum('measured_calls') * 100, 2)
                : 0,
        ];

        return [
            'summary' => $summary,
            'daily' => $daily,
        ];
    }

    /**
     * Profit/Loss Report
     */
    protected function generateProfitLossReport(ScheduledReport $report, string $from, string $to): array
    {
        $query = Cdr::whereBetween('start_time', [$from, $to])
            ->where('sip_code', 200);

        $byDay = $query->clone()
            ->selectRaw('
                DATE(start_time) as date,
                SUM(cost) as cost,
                SUM(price) as price,
                SUM(profit) as profit
            ')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $byCarrier = $query->clone()
            ->with('carrier')
            ->selectRaw('
                carrier_id,
                SUM(cost) as cost,
                SUM(price) as price,
                SUM(profit) as profit
            ')
            ->whereNotNull('carrier_id')
            ->groupBy('carrier_id')
            ->orderByDesc('profit')
            ->get()
            ->map(function ($row) {
                return [
                    'carrier' => $row->carrier?->name ?? 'Unknown',
                    'cost' => round($row->cost, 2),
                    'price' => round($row->price, 2),
                    'profit' => round($row->profit, 2),
                    'margin' => $row->price > 0
                        ? round(($row->profit / $row->price) * 100, 2)
                        : 0,
                ];
            });

        return [
            'total_cost' => $byDay->sum('cost'),
            'total_price' => $byDay->sum('price'),
            'total_profit' => $byDay->sum('profit'),
            'by_day' => $byDay,
            'by_carrier' => $byCarrier,
        ];
    }

    /**
     * Traffic Analysis Report
     */
    protected function generateTrafficAnalysis(ScheduledReport $report, string $from, string $to): array
    {
        $query = Cdr::whereBetween('start_time', [$from, $to]);

        if ($report->customer_id) {
            $query->where('customer_id', $report->customer_id);
        }

        $byHour = $query->clone()
            ->selectRaw('
                HOUR(start_time) as hour,
                COUNT(*) as calls,
                SUM(CASE WHEN sip_code = 200 THEN 1 ELSE 0 END) as answered
            ')
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();

        $byDayOfWeek = $query->clone()
            ->selectRaw('
                DAYOFWEEK(start_time) as dow,
                COUNT(*) as calls
            ')
            ->groupBy('dow')
            ->orderBy('dow')
            ->get();

        $topDestinations = $query->clone()
            ->with('destinationPrefix')
            ->selectRaw('
                destination_prefix_id,
                COUNT(*) as calls
            ')
            ->whereNotNull('destination_prefix_id')
            ->groupBy('destination_prefix_id')
            ->orderByDesc('calls')
            ->limit(10)
            ->get()
            ->map(function ($row) {
                return [
                    'destination' => $row->destinationPrefix?->full_description ?? 'Unknown',
                    'calls' => $row->calls,
                ];
            });

        return [
            'by_hour' => $byHour,
            'by_day_of_week' => $byDayOfWeek,
            'top_destinations' => $topDestinations,
        ];
    }

    /**
     * Generate filename for report
     */
    protected function generateFilename(ScheduledReport $report, string $from, string $to, string $extension): string
    {
        $slug = str($report->name)->slug();
        $dateRange = date('Ymd', strtotime($from)) . '-' . date('Ymd', strtotime($to));
        $timestamp = now()->format('His');

        return "{$slug}_{$dateRange}_{$timestamp}.{$extension}";
    }

    /**
     * Get CSV headers for report type
     */
    protected function getCsvHeaders(string $type): array
    {
        return match($type) {
            'cdr_summary' => ['Date', 'Calls', 'Duration (min)'],
            'customer_usage' => ['Customer', 'Calls', 'Answered', 'ASR %', 'Minutes', 'Price'],
            'carrier_performance' => ['Carrier', 'Calls', 'ASR %', 'ACD', 'Avg PDD', 'Minutes', 'Cost'],
            'billing' => ['Destination', 'Calls', 'Minutes', 'Cost', 'Price', 'Profit'],
            'qos_report' => ['Date', 'Calls', 'Avg MOS', 'Avg PDD', 'Poor %'],
            'profit_loss' => ['Date', 'Cost', 'Price', 'Profit'],
            'traffic_analysis' => ['Hour', 'Calls', 'Answered'],
            default => [],
        };
    }

    /**
     * Format data for CSV export
     */
    protected function formatDataForCsv(string $type, array $data): array
    {
        return match($type) {
            'cdr_summary' => collect($data['by_day'] ?? [])->map(fn($r) => [
                $r->date ?? $r['date'],
                $r->calls ?? $r['calls'],
                round(($r->duration ?? $r['duration']) / 60, 2),
            ])->toArray(),

            'customer_usage' => collect($data['by_customer'] ?? [])->map(fn($r) => [
                $r['customer_name'],
                $r['total_calls'],
                $r['answered_calls'],
                $r['asr'],
                $r['billable_minutes'],
                $r['total_price'],
            ])->toArray(),

            'carrier_performance' => collect($data['by_carrier'] ?? [])->map(fn($r) => [
                $r['carrier_name'],
                $r['total_calls'],
                $r['asr'],
                $r['acd'],
                $r['avg_pdd'],
                $r['total_minutes'],
                $r['total_cost'],
            ])->toArray(),

            'billing' => collect($data['by_destination'] ?? [])->map(fn($r) => [
                $r['destination'],
                $r['calls'],
                $r['minutes'],
                $r['cost'],
                $r['price'],
                $r['profit'],
            ])->toArray(),

            default => [],
        };
    }
}
