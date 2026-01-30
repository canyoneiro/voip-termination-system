<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Carrier;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CarrierController extends Controller
{
    public function index()
    {
        $carriers = Carrier::withCount('activeCalls')
            ->orderBy('priority')
            ->orderBy('name')
            ->paginate(20);

        return view('carriers.index', compact('carriers'));
    }

    public function create()
    {
        return view('carriers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'host' => 'required|string|max:255',
            'port' => 'required|integer|min:1|max:65535',
            'transport' => 'required|in:udp,tcp,tls',
            'codecs' => 'nullable|string|max:255',
            'priority' => 'required|integer|min:1|max:100',
            'weight' => 'required|integer|min:1|max:100',
            'tech_prefix' => 'nullable|string|max:50',
            'strip_digits' => 'required|integer|min:0|max:20',
            'number_format' => 'in:international,national_es',
            'prefix_filter' => 'nullable|string',
            'prefix_deny' => 'nullable|string',
            'max_cps' => 'required|integer|min:1|max:100',
            'max_channels' => 'required|integer|min:1|max:1000',
            'probing_enabled' => 'nullable|boolean',
            'notes' => 'nullable|string',
        ]);

        $validated['uuid'] = Str::uuid();
        $validated['probing_enabled'] = $request->boolean('probing_enabled');
        $carrier = Carrier::create($validated);

        // Reload Kamailio dispatcher
        exec('kamcmd dispatcher.reload 2>/dev/null');

        return redirect()
            ->route('carriers.show', $carrier)
            ->with('success', 'Carrier created successfully');
    }

    public function show(Carrier $carrier)
    {
        $carrier->load('activeCalls.customer');

        $recentCdrs = $carrier->cdrs()
            ->with('customer')
            ->orderBy('start_time', 'desc')
            ->limit(20)
            ->get();

        return view('carriers.show', compact('carrier', 'recentCdrs'));
    }

    public function edit(Carrier $carrier)
    {
        return view('carriers.edit', compact('carrier'));
    }

    public function update(Request $request, Carrier $carrier)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'host' => 'required|string|max:255',
            'port' => 'required|integer|min:1|max:65535',
            'transport' => 'required|in:udp,tcp,tls',
            'codecs' => 'nullable|string|max:255',
            'priority' => 'required|integer|min:1|max:100',
            'weight' => 'required|integer|min:1|max:100',
            'tech_prefix' => 'nullable|string|max:50',
            'strip_digits' => 'required|integer|min:0|max:20',
            'number_format' => 'in:international,national_es',
            'prefix_filter' => 'nullable|string',
            'prefix_deny' => 'nullable|string',
            'max_cps' => 'required|integer|min:1|max:100',
            'max_channels' => 'required|integer|min:1|max:1000',
            'state' => 'required|in:active,inactive,probing,disabled',
            'probing_enabled' => 'nullable|boolean',
            'notes' => 'nullable|string',
        ]);

        $validated['probing_enabled'] = $request->boolean('probing_enabled');
        $carrier->update($validated);

        // Reload Kamailio dispatcher
        exec('kamcmd dispatcher.reload 2>/dev/null');

        return redirect()
            ->route('carriers.show', $carrier)
            ->with('success', 'Carrier updated successfully');
    }

    public function destroy(Carrier $carrier)
    {
        $carrier->delete();

        // Reload Kamailio dispatcher
        exec('kamcmd dispatcher.reload 2>/dev/null');

        return redirect()
            ->route('carriers.index')
            ->with('success', 'Carrier deleted successfully');
    }

    public function test(Carrier $carrier)
    {
        $host = $carrier->host;
        $port = $carrier->port;

        // Send OPTIONS via Kamailio
        $result = shell_exec("kamcmd tm.t_uac_dlg OPTIONS sip:{$host}:{$port} 2>&1");

        return response()->json([
            'success' => true,
            'result' => $result,
        ]);
    }
}
