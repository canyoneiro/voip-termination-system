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

        // Observer handles Kamailio reload automatically

        return redirect()
            ->route('carriers.show', $carrier)
            ->with('success', 'Carrier created successfully. Kamailio synced.');
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

        // Observer handles Kamailio reload automatically

        return redirect()
            ->route('carriers.show', $carrier)
            ->with('success', 'Carrier updated successfully. Kamailio synced.');
    }

    public function destroy(Carrier $carrier)
    {
        $carrier->delete();

        // Observer handles Kamailio reload automatically

        return redirect()
            ->route('carriers.index')
            ->with('success', 'Carrier deleted successfully. Kamailio synced.');
    }

    public function test(Carrier $carrier)
    {
        $host = $carrier->host;
        $port = $carrier->port;
        $transport = strtolower($carrier->transport);

        // Send OPTIONS via sipsak
        $transportFlag = $transport === 'tcp' ? '-T' : '';
        $command = "timeout 5 sipsak -vv -s sip:ping@{$host}:{$port} {$transportFlag} 2>&1";
        $result = shell_exec($command);

        // Parse result to determine success
        $success = str_contains($result ?? '', '200') || str_contains($result ?? '', 'SIP/2.0 200');
        $statusMatch = [];
        preg_match('/SIP\/2\.0 (\d{3})/', $result ?? '', $statusMatch);
        $sipCode = $statusMatch[1] ?? null;

        // Check for timeout
        $timedOut = empty(trim($result ?? '')) || str_contains($result ?? '', 'timeout');

        // Update carrier last_options_time if successful
        if ($success) {
            $carrier->update([
                'last_options_time' => now(),
                'last_options_reply' => (int) $sipCode,
                'state' => 'active',
            ]);
            return back()->with('success', "OPTIONS OK - Respuesta: {$sipCode}. Carrier marcado como activo.");
        } elseif ($timedOut) {
            return back()->with('error', "OPTIONS timeout - El carrier no respondio en 5 segundos.");
        } else {
            $carrier->update([
                'last_options_time' => now(),
                'last_options_reply' => $sipCode ? (int) $sipCode : null,
            ]);
            return back()->with('error', "OPTIONS fallido - Respuesta: " . ($sipCode ?? 'Sin codigo SIP'));
        }
    }
}
