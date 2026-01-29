<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\FraudIncident;
use App\Models\FraudRule;
use App\Services\FraudDetectionService;
use Illuminate\Http\Request;

class FraudController extends Controller
{
    public function __construct(
        protected FraudDetectionService $fraudService
    ) {}

    public function index()
    {
        // Stats summary
        $stats = [
            'pending' => FraudIncident::where('status', 'pending')->count(),
            'investigating' => FraudIncident::where('status', 'investigating')->count(),
            'today' => FraudIncident::whereDate('created_at', today())->count(),
            'week' => FraudIncident::where('created_at', '>=', now()->subWeek())->count(),
        ];

        // Severity breakdown
        $bySeverity = FraudIncident::where('created_at', '>=', now()->subDays(7))
            ->selectRaw('severity, COUNT(*) as count')
            ->groupBy('severity')
            ->pluck('count', 'severity')
            ->toArray();

        // Recent incidents
        $recentIncidents = FraudIncident::with('customer', 'rule')
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();

        // Top risk customers
        $riskScores = $this->fraudService->calculateAllRiskScores();
        $topRiskCustomers = collect($riskScores)
            ->sortByDesc('score')
            ->take(10)
            ->values();

        // Active rules count
        $activeRules = FraudRule::where('active', true)->count();

        return view('fraud.index', compact(
            'stats',
            'bySeverity',
            'recentIncidents',
            'topRiskCustomers',
            'activeRules'
        ));
    }

    public function incidents(Request $request)
    {
        $query = FraudIncident::with('customer', 'rule');

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('severity')) {
            $query->where('severity', $request->input('severity'));
        }

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->input('customer_id'));
        }

        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        $incidents = $query->orderByDesc('created_at')->paginate(50);
        $customers = Customer::orderBy('name')->get();

        return view('fraud.incidents', compact('incidents', 'customers'));
    }

    public function showIncident(FraudIncident $incident)
    {
        $incident->load('customer', 'rule', 'cdr', 'resolvedBy');

        // Related incidents (same customer, last 30 days)
        $relatedIncidents = FraudIncident::where('customer_id', $incident->customer_id)
            ->where('id', '!=', $incident->id)
            ->where('created_at', '>=', now()->subDays(30))
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        return view('fraud.show', compact('incident', 'relatedIncidents'));
    }

    public function updateIncident(Request $request, FraudIncident $incident)
    {
        $data = $request->validate([
            'status' => 'required|in:pending,investigating,false_positive,confirmed,resolved',
            'resolution_notes' => 'nullable|string|max:1000',
        ]);

        $incident->status = $data['status'];

        if (in_array($data['status'], ['false_positive', 'confirmed', 'resolved'])) {
            $incident->resolved_by = auth()->id();
            $incident->resolved_at = now();
            $incident->resolution_notes = $data['resolution_notes'];
        }

        $incident->save();

        return redirect()->route('fraud.incidents.show', $incident)
            ->with('success', 'Incidente actualizado correctamente.');
    }

    public function rules()
    {
        $rules = FraudRule::with('customer')
            ->orderBy('type')
            ->orderByDesc('active')
            ->get();

        return view('fraud.rules', compact('rules'));
    }

    public function createRule()
    {
        $customers = Customer::orderBy('name')->get();
        $types = [
            'high_cost_destination' => 'Destino de Alto Costo',
            'traffic_spike' => 'Pico de Trafico',
            'wangiri' => 'Wangiri (Llamadas Cortas)',
            'unusual_destination' => 'Destino Inusual',
            'high_failure_rate' => 'Alta Tasa de Fallos',
            'off_hours_traffic' => 'Trafico Fuera de Horario',
            'caller_id_manipulation' => 'Manipulacion de Caller ID',
            'accelerated_consumption' => 'Consumo Acelerado',
            'simultaneous_calls' => 'Llamadas Simultaneas',
            'short_calls_burst' => 'Rafaga de Llamadas Cortas',
        ];

        return view('fraud.rules-create', compact('customers', 'types'));
    }

    public function storeRule(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'type' => 'required|string',
            'description' => 'nullable|string',
            'threshold' => 'nullable|numeric',
            'parameters' => 'nullable|array',
            'action' => 'required|in:log,alert,throttle,block',
            'severity' => 'required|in:low,medium,high,critical',
            'customer_id' => 'nullable|exists:customers,id',
            'cooldown_minutes' => 'required|integer|min:1',
            'active' => 'boolean',
        ]);

        $data['parameters'] = $data['parameters'] ?? [];
        $data['active'] = $request->boolean('active', true);

        FraudRule::create($data);

        return redirect()->route('fraud.rules')
            ->with('success', 'Regla creada correctamente.');
    }

    public function editRule(FraudRule $rule)
    {
        $customers = Customer::orderBy('name')->get();
        $types = [
            'high_cost_destination' => 'Destino de Alto Costo',
            'traffic_spike' => 'Pico de Trafico',
            'wangiri' => 'Wangiri (Llamadas Cortas)',
            'unusual_destination' => 'Destino Inusual',
            'high_failure_rate' => 'Alta Tasa de Fallos',
            'off_hours_traffic' => 'Trafico Fuera de Horario',
            'caller_id_manipulation' => 'Manipulacion de Caller ID',
            'accelerated_consumption' => 'Consumo Acelerado',
            'simultaneous_calls' => 'Llamadas Simultaneas',
            'short_calls_burst' => 'Rafaga de Llamadas Cortas',
        ];

        return view('fraud.rules-edit', compact('rule', 'customers', 'types'));
    }

    public function updateRule(Request $request, FraudRule $rule)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'threshold' => 'nullable|numeric',
            'parameters' => 'nullable|array',
            'action' => 'required|in:log,alert,throttle,block',
            'severity' => 'required|in:low,medium,high,critical',
            'customer_id' => 'nullable|exists:customers,id',
            'cooldown_minutes' => 'required|integer|min:1',
            'active' => 'boolean',
        ]);

        $data['parameters'] = $data['parameters'] ?? $rule->parameters;
        $data['active'] = $request->boolean('active', true);

        $rule->update($data);

        return redirect()->route('fraud.rules')
            ->with('success', 'Regla actualizada correctamente.');
    }

    public function destroyRule(FraudRule $rule)
    {
        $rule->delete();

        return redirect()->route('fraud.rules')
            ->with('success', 'Regla eliminada correctamente.');
    }

    public function riskScores()
    {
        $scores = $this->fraudService->calculateAllRiskScores();

        return view('fraud.risk-scores', compact('scores'));
    }
}
