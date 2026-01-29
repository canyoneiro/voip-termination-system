<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Jobs\GenerateScheduledReportJob;
use App\Models\Customer;
use App\Models\ReportExecution;
use App\Models\ScheduledReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ReportController extends Controller
{
    public function index()
    {
        $reports = ScheduledReport::with('customer')
            ->withCount('executions')
            ->orderByDesc('created_at')
            ->paginate(20);

        $stats = [
            'total' => ScheduledReport::count(),
            'active' => ScheduledReport::where('active', true)->count(),
            'executions_today' => ReportExecution::whereDate('created_at', today())->count(),
            'pending' => ReportExecution::where('status', 'pending')->count(),
        ];

        return view('reports.index', compact('reports', 'stats'));
    }

    public function create()
    {
        $customers = Customer::orderBy('name')->get();
        $types = [
            'cdr_summary' => 'Resumen de CDRs',
            'customer_usage' => 'Uso por Cliente',
            'carrier_performance' => 'Rendimiento de Carriers',
            'billing' => 'Facturacion',
            'qos_report' => 'Calidad de Servicio (QoS)',
            'fraud_report' => 'Incidentes de Fraude',
        ];
        $frequencies = [
            'daily' => 'Diario',
            'weekly' => 'Semanal (Lunes)',
            'monthly' => 'Mensual (Dia 1)',
        ];

        return view('reports.create', compact('customers', 'types', 'frequencies'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'type' => 'required|string',
            'frequency' => 'required|in:daily,weekly,monthly',
            'recipients' => 'required|array|min:1',
            'recipients.*' => 'email',
            'formats' => 'required|array|min:1',
            'formats.*' => 'in:pdf,csv',
            'parameters' => 'nullable|array',
            'customer_id' => 'nullable|exists:customers,id',
            'active' => 'boolean',
        ]);

        $data['active'] = $request->boolean('active', true);
        $data['parameters'] = $data['parameters'] ?? [];

        ScheduledReport::create($data);

        return redirect()->route('reports.index')
            ->with('success', 'Reporte programado creado correctamente.');
    }

    public function show(ScheduledReport $report)
    {
        $report->load('customer');

        $executions = $report->executions()
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('reports.show', compact('report', 'executions'));
    }

    public function edit(ScheduledReport $report)
    {
        $customers = Customer::orderBy('name')->get();
        $types = [
            'cdr_summary' => 'Resumen de CDRs',
            'customer_usage' => 'Uso por Cliente',
            'carrier_performance' => 'Rendimiento de Carriers',
            'billing' => 'Facturacion',
            'qos_report' => 'Calidad de Servicio (QoS)',
            'fraud_report' => 'Incidentes de Fraude',
        ];
        $frequencies = [
            'daily' => 'Diario',
            'weekly' => 'Semanal (Lunes)',
            'monthly' => 'Mensual (Dia 1)',
        ];

        return view('reports.edit', compact('report', 'customers', 'types', 'frequencies'));
    }

    public function update(Request $request, ScheduledReport $report)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'frequency' => 'required|in:daily,weekly,monthly',
            'recipients' => 'required|array|min:1',
            'recipients.*' => 'email',
            'formats' => 'required|array|min:1',
            'formats.*' => 'in:pdf,csv',
            'parameters' => 'nullable|array',
            'customer_id' => 'nullable|exists:customers,id',
            'active' => 'boolean',
        ]);

        $data['active'] = $request->boolean('active', true);
        $data['parameters'] = $data['parameters'] ?? $report->parameters;

        $report->update($data);

        return redirect()->route('reports.show', $report)
            ->with('success', 'Reporte actualizado correctamente.');
    }

    public function destroy(ScheduledReport $report)
    {
        // Delete associated files
        foreach ($report->executions as $execution) {
            if ($execution->file_path && Storage::exists($execution->file_path)) {
                Storage::delete($execution->file_path);
            }
        }

        $report->delete();

        return redirect()->route('reports.index')
            ->with('success', 'Reporte eliminado correctamente.');
    }

    public function trigger(ScheduledReport $report)
    {
        GenerateScheduledReportJob::dispatch($report);

        return redirect()->route('reports.show', $report)
            ->with('success', 'Reporte enviado a la cola de procesamiento.');
    }

    public function showExecution(ReportExecution $execution)
    {
        $execution->load('report.customer');

        return view('reports.execution', compact('execution'));
    }

    public function downloadExecution(ReportExecution $execution, string $format = null)
    {
        if ($execution->status !== 'completed') {
            return back()->with('error', 'El reporte aun no esta disponible.');
        }

        $filePath = $execution->file_path;
        if ($format) {
            // Try to find the specific format
            $basePath = pathinfo($filePath, PATHINFO_DIRNAME) . '/' . pathinfo($filePath, PATHINFO_FILENAME);
            $filePath = $basePath . '.' . $format;
        }

        if (!$filePath || !Storage::exists($filePath)) {
            return back()->with('error', 'Archivo no encontrado.');
        }

        return Storage::download($filePath);
    }
}
