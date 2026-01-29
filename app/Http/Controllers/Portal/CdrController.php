<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Cdr;
use Illuminate\Http\Request;

class CdrController extends Controller
{
    public function index(Request $request)
    {
        $user = auth('customer')->user();
        $customer = $user->customer;
        $settings = $customer->portalSettings;

        $query = Cdr::where('customer_id', $customer->id);

        // Apply filters
        if ($request->filled('from')) {
            $query->where('start_time', '>=', $request->input('from') . ' 00:00:00');
        } else {
            $query->where('start_time', '>=', now()->subDays(7)->format('Y-m-d') . ' 00:00:00');
        }

        if ($request->filled('to')) {
            $query->where('start_time', '<=', $request->input('to') . ' 23:59:59');
        }

        if ($request->filled('number')) {
            $number = $request->input('number');
            $query->where(function ($q) use ($number) {
                $q->where('caller', 'like', '%' . $number . '%')
                  ->orWhere('callee', 'like', '%' . $number . '%');
            });
        }

        if ($request->filled('status')) {
            if ($request->input('status') === 'answered') {
                $query->where('sip_code', 200);
            } elseif ($request->input('status') === 'failed') {
                $query->where('sip_code', '>=', 400);
            }
        }

        $perPage = min($request->input('per_page', 50), 100);
        $cdrs = $query->orderByDesc('start_time')->paginate($perPage);

        // Summary stats for filtered data
        $summaryQuery = Cdr::where('customer_id', $customer->id);
        if ($request->filled('from')) {
            $summaryQuery->where('start_time', '>=', $request->input('from') . ' 00:00:00');
        } else {
            $summaryQuery->where('start_time', '>=', now()->subDays(7)->format('Y-m-d') . ' 00:00:00');
        }
        if ($request->filled('to')) {
            $summaryQuery->where('start_time', '<=', $request->input('to') . ' 23:59:59');
        }
        if ($request->filled('number')) {
            $number = $request->input('number');
            $summaryQuery->where(function ($q) use ($number) {
                $q->where('caller', 'like', '%' . $number . '%')
                  ->orWhere('callee', 'like', '%' . $number . '%');
            });
        }

        $summaryData = $summaryQuery->selectRaw('
                COUNT(*) as total_calls,
                SUM(CASE WHEN sip_code = 200 THEN 1 ELSE 0 END) as answered_calls,
                SUM(duration) as total_duration
            ')
            ->first();

        $summary = [
            'total' => $summaryData->total_calls ?? 0,
            'answered' => $summaryData->answered_calls ?? 0,
            'minutes' => round(($summaryData->total_duration ?? 0) / 60),
            'asr' => $summaryData->total_calls > 0
                ? round(($summaryData->answered_calls / $summaryData->total_calls) * 100, 1)
                : 0,
        ];

        return view('portal.cdrs.index', compact('cdrs', 'summary', 'settings'));
    }

    public function show(Cdr $cdr)
    {
        // Verify the CDR belongs to this customer (extra safety)
        $user = auth('customer')->user();
        if ($cdr->customer_id !== $user->customer_id) {
            abort(403);
        }

        $settings = $user->customer->portalSettings;

        // Load SIP traces if allowed
        if ($settings?->show_sip_traces) {
            $cdr->load('sipTraces');
        }

        return view('portal.cdrs.show', compact('cdr', 'settings'));
    }

    public function export(Request $request)
    {
        $user = auth('customer')->user();
        $settings = $user->customer->portalSettings;

        $query = Cdr::query();

        if ($request->filled('from')) {
            $query->where('start_time', '>=', $request->input('from') . ' 00:00:00');
        }

        if ($request->filled('to')) {
            $query->where('start_time', '<=', $request->input('to') . ' 23:59:59');
        }

        // Limit export
        $cdrs = $query->orderByDesc('start_time')
            ->limit($settings?->cdr_retention_days ? 10000 : 5000)
            ->get();

        $filename = 'cdrs_' . now()->format('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($cdrs, $settings) {
            $file = fopen('php://output', 'w');

            // Header row
            $header = ['Date/Time', 'Caller', 'Callee', 'Duration (s)', 'Status', 'SIP Code'];
            if ($settings?->show_carrier_names) {
                $header[] = 'Carrier';
            }
            if ($settings?->show_cost_info) {
                $header[] = 'Price';
            }
            fputcsv($file, $header);

            // Data rows
            foreach ($cdrs as $cdr) {
                $row = [
                    $cdr->start_time->format('Y-m-d H:i:s'),
                    $cdr->caller,
                    $cdr->callee,
                    $cdr->duration,
                    $cdr->sip_code == 200 ? 'Answered' : 'Failed',
                    $cdr->sip_code,
                ];
                if ($settings?->show_carrier_names) {
                    $row[] = $cdr->carrier?->name ?? '';
                }
                if ($settings?->show_cost_info) {
                    $row[] = $cdr->price;
                }
                fputcsv($file, $row);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
