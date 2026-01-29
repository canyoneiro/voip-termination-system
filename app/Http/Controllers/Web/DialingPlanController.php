<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\DialingPlan;
use App\Models\DialingPlanRule;
use App\Models\DestinationPrefix;
use App\Services\LcrService;
use Illuminate\Http\Request;

class DialingPlanController extends Controller
{
    public function index()
    {
        $dialingPlans = DialingPlan::withCount(['rules', 'customers'])
            ->orderBy('name')
            ->paginate(20);

        return view('dialing-plans.index', compact('dialingPlans'));
    }

    public function create()
    {
        return view('dialing-plans.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:dialing_plans,name',
            'description' => 'nullable|string|max:1000',
            'default_action' => 'required|in:allow,deny',
            'block_premium' => 'boolean',
            'active' => 'boolean',
        ]);

        $validated['block_premium'] = $request->boolean('block_premium');
        $validated['active'] = $request->boolean('active', true);

        $dialingPlan = DialingPlan::create($validated);

        return redirect()
            ->route('dialing-plans.show', $dialingPlan)
            ->with('success', 'Dialing plan created successfully.');
    }

    public function show(DialingPlan $dialingPlan)
    {
        $dialingPlan->load(['rules' => fn($q) => $q->orderBy('priority'), 'customers']);

        return view('dialing-plans.show', compact('dialingPlan'));
    }

    public function edit(DialingPlan $dialingPlan)
    {
        return view('dialing-plans.edit', compact('dialingPlan'));
    }

    public function update(Request $request, DialingPlan $dialingPlan)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:dialing_plans,name,' . $dialingPlan->id,
            'description' => 'nullable|string|max:1000',
            'default_action' => 'required|in:allow,deny',
            'block_premium' => 'boolean',
            'active' => 'boolean',
        ]);

        $validated['block_premium'] = $request->boolean('block_premium');
        $validated['active'] = $request->boolean('active', true);

        $dialingPlan->update($validated);

        return redirect()
            ->route('dialing-plans.show', $dialingPlan)
            ->with('success', 'Dialing plan updated successfully.');
    }

    public function destroy(DialingPlan $dialingPlan)
    {
        if ($dialingPlan->customers()->exists()) {
            return redirect()
                ->route('dialing-plans.show', $dialingPlan)
                ->with('error', 'Cannot delete dialing plan with assigned customers.');
        }

        $dialingPlan->delete();

        return redirect()
            ->route('dialing-plans.index')
            ->with('success', 'Dialing plan deleted successfully.');
    }

    // Rules management
    public function storeRule(Request $request, DialingPlan $dialingPlan)
    {
        $validated = $request->validate([
            'type' => 'required|in:allow,deny',
            'pattern' => 'required|string|max:50',
            'description' => 'nullable|string|max:255',
            'priority' => 'integer|min:1|max:9999',
            'active' => 'boolean',
        ]);

        $validated['active'] = $request->boolean('active', true);
        $validated['priority'] = $validated['priority'] ?? 100;

        $dialingPlan->rules()->create($validated);

        return redirect()
            ->route('dialing-plans.show', $dialingPlan)
            ->with('success', 'Rule added successfully.');
    }

    public function updateRule(Request $request, DialingPlan $dialingPlan, DialingPlanRule $rule)
    {
        $validated = $request->validate([
            'type' => 'required|in:allow,deny',
            'pattern' => 'required|string|max:50',
            'description' => 'nullable|string|max:255',
            'priority' => 'integer|min:1|max:9999',
            'active' => 'boolean',
        ]);

        $validated['active'] = $request->boolean('active', true);

        $rule->update($validated);

        return redirect()
            ->route('dialing-plans.show', $dialingPlan)
            ->with('success', 'Rule updated successfully.');
    }

    public function destroyRule(DialingPlan $dialingPlan, DialingPlanRule $rule)
    {
        $rule->delete();

        return redirect()
            ->route('dialing-plans.show', $dialingPlan)
            ->with('success', 'Rule deleted successfully.');
    }

    // Test a number against a dialing plan
    public function testNumber(Request $request, DialingPlan $dialingPlan)
    {
        $request->validate([
            'number' => 'required|string|max:30',
        ]);

        $number = ltrim($request->input('number'), '+');
        $lcrService = app(LcrService::class);
        $prefix = $lcrService->findDestinationPrefix($number);

        $result = $dialingPlan->isNumberAllowed($number, $prefix);
        $result['number'] = $number;
        $result['prefix'] = $prefix ? [
            'prefix' => $prefix->prefix,
            'country' => $prefix->country_name,
            'region' => $prefix->region,
            'is_premium' => $prefix->is_premium,
            'is_mobile' => $prefix->is_mobile,
        ] : null;

        return response()->json($result);
    }

    // Clone a dialing plan
    public function clone(DialingPlan $dialingPlan)
    {
        $newPlan = $dialingPlan->replicate();
        $newPlan->name = $dialingPlan->name . ' (Copy)';
        $newPlan->uuid = null;
        $newPlan->save();

        foreach ($dialingPlan->rules as $rule) {
            $newRule = $rule->replicate();
            $newRule->dialing_plan_id = $newPlan->id;
            $newRule->save();
        }

        return redirect()
            ->route('dialing-plans.edit', $newPlan)
            ->with('success', 'Dialing plan cloned successfully.');
    }

    // Import rules from text
    public function importRules(Request $request, DialingPlan $dialingPlan)
    {
        $request->validate([
            'type' => 'required|in:allow,deny',
            'patterns' => 'required|string',
        ]);

        $type = $request->input('type');
        $patterns = array_filter(
            array_map('trim', explode("\n", $request->input('patterns')))
        );

        $count = 0;
        $priority = $dialingPlan->rules()->max('priority') ?? 0;

        foreach ($patterns as $pattern) {
            if (empty($pattern) || strlen($pattern) > 50) continue;

            $priority += 10;
            $dialingPlan->rules()->create([
                'type' => $type,
                'pattern' => $pattern,
                'priority' => $priority,
                'active' => true,
            ]);
            $count++;
        }

        return redirect()
            ->route('dialing-plans.show', $dialingPlan)
            ->with('success', "Imported {$count} rules successfully.");
    }
}
