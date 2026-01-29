<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\IpBlacklist;
use Illuminate\Http\Request;

class BlacklistController extends Controller
{
    public function index()
    {
        $blacklist = IpBlacklist::orderBy('created_at', 'desc')->paginate(50);

        return view('blacklist.index', compact('blacklist'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'ip_address' => 'required|ip|unique:ip_blacklist,ip_address',
            'reason' => 'required|string|max:255',
            'permanent' => 'boolean',
            'expires_at' => 'nullable|date|after:now',
        ]);

        $validated['source'] = 'manual';

        IpBlacklist::create($validated);

        return back()->with('success', 'IP added to blacklist');
    }

    public function destroy(IpBlacklist $blacklist)
    {
        $blacklist->delete();

        return back()->with('success', 'IP removed from blacklist');
    }

    public function togglePermanent(IpBlacklist $blacklist)
    {
        $blacklist->update([
            'permanent' => !$blacklist->permanent,
            'expires_at' => $blacklist->permanent ? now()->addHours(24) : null,
        ]);

        return back()->with('success', 'Blacklist entry updated');
    }
}
