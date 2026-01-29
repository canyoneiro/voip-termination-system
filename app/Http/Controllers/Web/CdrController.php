<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Cdr;
use App\Models\Customer;
use App\Models\Carrier;
use App\Models\SipTrace;
use Illuminate\Http\Request;

class CdrController extends Controller
{
    public function index(Request $request)
    {
        $query = Cdr::with(['customer', 'carrier']);

        // Apply filters
        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        if ($request->filled('carrier_id')) {
            $query->where('carrier_id', $request->carrier_id);
        }

        if ($request->filled('from')) {
            $query->where('start_time', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->where('start_time', '<=', $request->to . ' 23:59:59');
        }

        if ($request->filled('caller')) {
            $query->where('caller', 'like', '%' . $request->caller . '%');
        }

        if ($request->filled('callee')) {
            $query->where('callee', 'like', '%' . $request->callee . '%');
        }

        if ($request->filled('status')) {
            if ($request->status === 'answered') {
                $query->whereNotNull('answer_time');
            } elseif ($request->status === 'failed') {
                $query->whereNull('answer_time');
            }
        }

        if ($request->filled('min_duration')) {
            $query->where('duration', '>=', $request->min_duration);
        }

        // Stats for current filter (before pagination)
        $stats = Cdr::query()
            ->when($request->filled('customer_id'), fn($q) => $q->where('customer_id', $request->customer_id))
            ->when($request->filled('carrier_id'), fn($q) => $q->where('carrier_id', $request->carrier_id))
            ->when($request->filled('from'), fn($q) => $q->where('start_time', '>=', $request->from))
            ->when($request->filled('to'), fn($q) => $q->where('start_time', '<=', $request->to . ' 23:59:59'))
            ->when($request->filled('status') && $request->status === 'answered', fn($q) => $q->whereNotNull('answer_time'))
            ->when($request->filled('status') && $request->status === 'failed', fn($q) => $q->whereNull('answer_time'))
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('SUM(CASE WHEN answer_time IS NOT NULL THEN 1 ELSE 0 END) as answered')
            ->selectRaw('SUM(duration) as total_duration')
            ->first();

        $cdrs = $query->orderBy('start_time', 'desc')->paginate(50);

        $customers = Customer::orderBy('name')->get(['id', 'name']);
        $carriers = Carrier::orderBy('name')->get(['id', 'name']);

        return view('cdrs.index', compact('cdrs', 'stats', 'customers', 'carriers'));
    }

    public function show(Cdr $cdr)
    {
        $cdr->load(['customer', 'carrier']);

        $traces = SipTrace::where('call_id', $cdr->call_id)
            ->orderBy('timestamp')
            ->get();

        return view('cdrs.show', compact('cdr', 'traces'));
    }

    public function export(Request $request)
    {
        $query = Cdr::with(['customer', 'carrier']);

        // Apply same filters as index
        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }
        if ($request->filled('carrier_id')) {
            $query->where('carrier_id', $request->carrier_id);
        }
        if ($request->filled('from')) {
            $query->where('start_time', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->where('start_time', '<=', $request->to . ' 23:59:59');
        }

        $cdrs = $query->orderBy('start_time', 'desc')->limit(10000)->get();

        $filename = 'cdrs_' . now()->format('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($cdrs) {
            $file = fopen('php://output', 'w');

            fputcsv($file, [
                'UUID', 'Start Time', 'Customer', 'Carrier', 'Caller', 'Callee',
                'Duration', 'Billable', 'PDD', 'SIP Code', 'Reason', 'Hangup Cause'
            ]);

            foreach ($cdrs as $cdr) {
                fputcsv($file, [
                    $cdr->uuid,
                    $cdr->start_time,
                    $cdr->customer->name ?? '',
                    $cdr->carrier->name ?? '',
                    $cdr->caller,
                    $cdr->callee,
                    $cdr->duration,
                    $cdr->billable_duration,
                    $cdr->pdd,
                    $cdr->sip_code,
                    $cdr->sip_reason,
                    $cdr->hangup_cause,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
