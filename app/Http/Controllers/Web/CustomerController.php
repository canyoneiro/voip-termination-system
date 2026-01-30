<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\CustomerIp;
use App\Services\NumberNormalizationService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CustomerController extends Controller
{
    public function index()
    {
        $customers = Customer::withCount(['ips', 'activeCalls'])
            ->orderBy('name')
            ->paginate(20);

        return view('customers.index', compact('customers'));
    }

    public function create()
    {
        return view('customers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'company' => 'nullable|string|max:150',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'max_channels' => 'required|integer|min:1|max:1000',
            'max_cps' => 'required|integer|min:1|max:100',
            'max_daily_minutes' => 'nullable|integer|min:0',
            'max_monthly_minutes' => 'nullable|integer|min:0',
            'notes' => 'nullable|string',
            'alert_email' => 'nullable|email|max:255',
            'alert_telegram_chat_id' => 'nullable|string|max:100',
            'rate_plan_id' => 'nullable|exists:rate_plans,id',
            'dialing_plan_id' => 'nullable|exists:dialing_plans,id',
            'number_format' => 'in:auto,international,national_es',
            'default_country_code' => 'string|max:3',
            'strip_plus_sign' => 'boolean',
            'add_plus_sign' => 'boolean',
        ]);

        $validated['uuid'] = Str::uuid();
        $customer = Customer::create($validated);

        return redirect()
            ->route('customers.show', $customer)
            ->with('success', 'Customer created successfully');
    }

    public function show(Customer $customer)
    {
        $customer->load(['ips', 'activeCalls.carrier']);

        $recentCdrs = $customer->cdrs()
            ->with('carrier')
            ->orderBy('start_time', 'desc')
            ->limit(20)
            ->get();

        return view('customers.show', compact('customer', 'recentCdrs'));
    }

    public function edit(Customer $customer)
    {
        return view('customers.edit', compact('customer'));
    }

    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'company' => 'nullable|string|max:150',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'max_channels' => 'required|integer|min:1|max:1000',
            'max_cps' => 'required|integer|min:1|max:100',
            'max_daily_minutes' => 'nullable|integer|min:0',
            'max_monthly_minutes' => 'nullable|integer|min:0',
            'notes' => 'nullable|string',
            'alert_email' => 'nullable|email|max:255',
            'alert_telegram_chat_id' => 'nullable|string|max:100',
            'rate_plan_id' => 'nullable|exists:rate_plans,id',
            'dialing_plan_id' => 'nullable|exists:dialing_plans,id',
            'number_format' => 'in:auto,international,national_es',
            'default_country_code' => 'string|max:3',
            'strip_plus_sign' => 'boolean',
            'add_plus_sign' => 'boolean',
            'active' => 'boolean',
            'traces_enabled' => 'boolean',
        ]);

        $customer->update($validated);

        return redirect()
            ->route('customers.show', $customer)
            ->with('success', 'Customer updated successfully');
    }

    public function destroy(Customer $customer)
    {
        $customer->delete();

        return redirect()
            ->route('customers.index')
            ->with('success', 'Customer deleted successfully');
    }

    public function addIp(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'ip_address' => 'required|ip',
            'description' => 'nullable|string|max:100',
        ]);

        $customer->ips()->create($validated);

        // Reload Kamailio permissions
        exec('kamcmd permissions.addressReload 2>/dev/null');

        return back()->with('success', 'IP added successfully');
    }

    public function removeIp(Customer $customer, CustomerIp $ip)
    {
        if ($ip->customer_id !== $customer->id) {
            abort(404);
        }

        $ip->delete();

        // Reload Kamailio permissions
        exec('kamcmd permissions.addressReload 2>/dev/null');

        return back()->with('success', 'IP removed successfully');
    }

    public function resetMinutes(Request $request, Customer $customer)
    {
        $type = $request->input('type', 'daily');

        if ($type === 'daily' || $type === 'both') {
            $customer->used_daily_minutes = 0;
        }
        if ($type === 'monthly' || $type === 'both') {
            $customer->used_monthly_minutes = 0;
        }

        $customer->save();

        return back()->with('success', 'Minutes reset successfully');
    }

    /**
     * Test number normalization with given settings
     */
    public function testNormalization(Request $request, NumberNormalizationService $normalizationService)
    {
        $validated = $request->validate([
            'number' => 'required|string|max:30',
            'format' => 'required|in:auto,international,national_es',
            'country_code' => 'required|string|max:3',
            'strip_plus' => 'boolean',
            'add_plus' => 'boolean',
        ]);

        // Create a temporary customer object for testing
        $customer = new Customer([
            'number_format' => $validated['format'],
            'default_country_code' => $validated['country_code'],
            'strip_plus_sign' => $validated['strip_plus'] ?? true,
            'add_plus_sign' => $validated['add_plus'] ?? false,
        ]);

        $result = $normalizationService->normalize($validated['number'], $customer);

        return response()->json($result);
    }
}
