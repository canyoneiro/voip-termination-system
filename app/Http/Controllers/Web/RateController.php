<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Carrier;
use App\Models\CarrierRate;
use App\Models\Customer;
use App\Models\CustomerRate;
use App\Models\DestinationPrefix;
use App\Models\RateImport;
use App\Models\RatePlan;
use App\Services\LcrService;
use App\Services\RateImportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RateController extends Controller
{
    public function __construct(
        protected LcrService $lcrService,
        protected RateImportService $importService
    ) {}

    public function index()
    {
        $stats = [
            'destinations' => DestinationPrefix::count(),
            'carrier_rates' => CarrierRate::where('active', true)->count(),
            'rate_plans' => RatePlan::where('active', true)->count(),
            'customer_rates' => CustomerRate::count(),
        ];

        $recentImports = RateImport::with('user')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        return view('rates.index', compact('stats', 'recentImports'));
    }

    // Destinations
    public function destinations(Request $request)
    {
        $query = DestinationPrefix::query();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('prefix', 'like', $search . '%')
                  ->orWhere('country', 'like', '%' . $search . '%')
                  ->orWhere('description', 'like', '%' . $search . '%');
            });
        }

        $destinations = $query->orderBy('prefix')->paginate(50);

        return view('rates.destinations', compact('destinations'));
    }

    public function createDestination()
    {
        return view('rates.destinations-create');
    }

    public function storeDestination(Request $request)
    {
        $data = $request->validate([
            'prefix' => 'required|string|max:20|unique:destination_prefixes,prefix',
            'country' => 'nullable|string|max:100',
            'region' => 'nullable|string|max:100',
            'description' => 'nullable|string|max:255',
            'is_mobile' => 'boolean',
            'is_premium' => 'boolean',
        ]);

        $data['is_mobile'] = $request->boolean('is_mobile');
        $data['is_premium'] = $request->boolean('is_premium');

        DestinationPrefix::create($data);

        return redirect()->route('rates.destinations')
            ->with('success', 'Destino creado correctamente.');
    }

    public function editDestination(DestinationPrefix $destination)
    {
        return view('rates.destinations-edit', compact('destination'));
    }

    public function updateDestination(Request $request, DestinationPrefix $destination)
    {
        $data = $request->validate([
            'country' => 'nullable|string|max:100',
            'region' => 'nullable|string|max:100',
            'description' => 'nullable|string|max:255',
            'is_mobile' => 'boolean',
            'is_premium' => 'boolean',
        ]);

        $data['is_mobile'] = $request->boolean('is_mobile');
        $data['is_premium'] = $request->boolean('is_premium');

        $destination->update($data);

        return redirect()->route('rates.destinations')
            ->with('success', 'Destino actualizado correctamente.');
    }

    public function destroyDestination(DestinationPrefix $destination)
    {
        $destination->delete();

        return redirect()->route('rates.destinations')
            ->with('success', 'Destino eliminado correctamente.');
    }

    // Carrier Rates
    public function carrierRates(Request $request)
    {
        $query = CarrierRate::with(['carrier', 'destination']);

        if ($request->filled('carrier_id')) {
            $query->where('carrier_id', $request->input('carrier_id'));
        }

        if ($request->filled('prefix')) {
            $query->where('prefix', 'like', $request->input('prefix') . '%');
        }

        $rates = $query->orderBy('prefix')->paginate(50);
        $carriers = Carrier::orderBy('name')->get();

        return view('rates.carrier-rates', compact('rates', 'carriers'));
    }

    public function createCarrierRate()
    {
        $carriers = Carrier::where('state', '!=', 'disabled')->orderBy('name')->get();
        $destinations = DestinationPrefix::orderBy('prefix')->get();

        return view('rates.carrier-rates-create', compact('carriers', 'destinations'));
    }

    public function storeCarrierRate(Request $request)
    {
        $data = $request->validate([
            'carrier_id' => 'required|exists:carriers,id',
            'prefix' => 'required|string|max:20',
            'rate_per_minute' => 'required|numeric|min:0',
            'connection_fee' => 'nullable|numeric|min:0',
            'billing_increment' => 'required|integer|min:1',
            'minimum_duration' => 'nullable|integer|min:0',
            'effective_date' => 'nullable|date',
            'end_date' => 'nullable|date|after:effective_date',
            'active' => 'boolean',
        ]);

        $data['active'] = $request->boolean('active', true);
        $data['connection_fee'] = $data['connection_fee'] ?? 0;
        $data['minimum_duration'] = $data['minimum_duration'] ?? 0;

        // Check for existing rate
        $exists = CarrierRate::where('carrier_id', $data['carrier_id'])
            ->where('prefix', $data['prefix'])
            ->where('active', true)
            ->exists();

        if ($exists) {
            return back()->withInput()
                ->withErrors(['prefix' => 'Ya existe una tarifa activa para este carrier y prefijo.']);
        }

        CarrierRate::create($data);

        return redirect()->route('rates.carrier-rates')
            ->with('success', 'Tarifa de carrier creada correctamente.');
    }

    public function editCarrierRate(CarrierRate $rate)
    {
        $carriers = Carrier::orderBy('name')->get();

        return view('rates.carrier-rates-edit', compact('rate', 'carriers'));
    }

    public function updateCarrierRate(Request $request, CarrierRate $rate)
    {
        $data = $request->validate([
            'rate_per_minute' => 'required|numeric|min:0',
            'connection_fee' => 'nullable|numeric|min:0',
            'billing_increment' => 'required|integer|min:1',
            'minimum_duration' => 'nullable|integer|min:0',
            'effective_date' => 'nullable|date',
            'end_date' => 'nullable|date|after:effective_date',
            'active' => 'boolean',
        ]);

        $data['active'] = $request->boolean('active', true);

        $rate->update($data);

        return redirect()->route('rates.carrier-rates')
            ->with('success', 'Tarifa actualizada correctamente.');
    }

    public function destroyCarrierRate(CarrierRate $rate)
    {
        $rate->delete();

        return redirect()->route('rates.carrier-rates')
            ->with('success', 'Tarifa eliminada correctamente.');
    }

    // Rate Plans
    public function ratePlans()
    {
        $plans = RatePlan::withCount('customers', 'rates')
            ->orderBy('name')
            ->paginate(20);

        return view('rates.rate-plans', compact('plans'));
    }

    public function createRatePlan()
    {
        return view('rates.rate-plans-create');
    }

    public function storeRatePlan(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100|unique:rate_plans,name',
            'description' => 'nullable|string',
            'default_markup_percent' => 'required|numeric|min:0',
            'active' => 'boolean',
        ]);

        $data['active'] = $request->boolean('active', true);

        RatePlan::create($data);

        return redirect()->route('rates.rate-plans')
            ->with('success', 'Plan de tarifas creado correctamente.');
    }

    public function showRatePlan(RatePlan $ratePlan)
    {
        $ratePlan->load('rates.destination', 'customers');

        return view('rates.rate-plans-show', compact('ratePlan'));
    }

    public function editRatePlan(RatePlan $ratePlan)
    {
        return view('rates.rate-plans-edit', compact('ratePlan'));
    }

    public function updateRatePlan(Request $request, RatePlan $ratePlan)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100|unique:rate_plans,name,' . $ratePlan->id,
            'description' => 'nullable|string',
            'default_markup_percent' => 'required|numeric|min:0',
            'active' => 'boolean',
        ]);

        $data['active'] = $request->boolean('active', true);

        $ratePlan->update($data);

        return redirect()->route('rates.rate-plans')
            ->with('success', 'Plan actualizado correctamente.');
    }

    public function destroyRatePlan(RatePlan $ratePlan)
    {
        if ($ratePlan->customers()->exists()) {
            return back()->with('error', 'No se puede eliminar un plan con clientes asignados.');
        }

        $ratePlan->delete();

        return redirect()->route('rates.rate-plans')
            ->with('success', 'Plan eliminado correctamente.');
    }

    // LCR Test
    public function lcrTest()
    {
        $customers = Customer::where('active', true)->orderBy('name')->get();

        return view('rates.lcr-test', compact('customers'));
    }

    public function lcrLookup(Request $request)
    {
        $request->validate([
            'number' => 'required|string|min:3',
            'customer_id' => 'nullable|exists:customers,id',
        ]);

        $number = $request->input('number');
        $customerId = $request->input('customer_id');
        $customer = $customerId ? Customer::find($customerId) : null;

        $result = $this->lcrService->selectCarrier($number, $customer);

        $customers = Customer::where('active', true)->orderBy('name')->get();

        return view('rates.lcr-test', compact('customers', 'number', 'customer', 'result'));
    }

    // Import
    public function importForm()
    {
        $carriers = Carrier::orderBy('name')->get();

        return view('rates.import', compact('carriers'));
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:10240',
            'type' => 'required|in:destinations,carrier_rates',
            'carrier_id' => 'required_if:type,carrier_rates|nullable|exists:carriers,id',
        ]);

        $file = $request->file('file');
        $type = $request->input('type');
        $carrierId = $request->input('carrier_id');

        try {
            if ($type === 'destinations') {
                $result = $this->importService->importDestinations($file->getPathname());
            } else {
                $result = $this->importService->importCarrierRates($file->getPathname(), $carrierId);
            }

            return redirect()->route('rates.index')
                ->with('success', "Importacion completada: {$result['imported']} registros importados, {$result['skipped']} omitidos.");
        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', 'Error en la importacion: ' . $e->getMessage());
        }
    }

    // Sync to Redis
    public function syncRedis()
    {
        try {
            $this->lcrService->syncToRedis();

            return redirect()->route('rates.index')
                ->with('success', 'Datos LCR sincronizados a Redis correctamente.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error sincronizando: ' . $e->getMessage());
        }
    }
}
