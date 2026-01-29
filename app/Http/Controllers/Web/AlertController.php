<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Alert;
use Illuminate\Http\Request;

class AlertController extends Controller
{
    public function index(Request $request)
    {
        $query = Alert::query();

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('severity')) {
            $query->where('severity', $request->severity);
        }

        if ($request->has('acknowledged')) {
            $query->where('acknowledged', $request->boolean('acknowledged'));
        }

        if ($request->filled('from')) {
            $query->where('created_at', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->where('created_at', '<=', $request->to . ' 23:59:59');
        }

        $alerts = $query->orderBy('created_at', 'desc')->paginate(50);

        $unacknowledgedCount = Alert::where('acknowledged', false)->count();

        return view('alerts.index', compact('alerts', 'unacknowledgedCount'));
    }

    public function show(Alert $alert)
    {
        return view('alerts.show', compact('alert'));
    }

    public function acknowledge(Alert $alert)
    {
        $alert->update([
            'acknowledged' => true,
            'acknowledged_by' => auth()->id(),
            'acknowledged_at' => now(),
        ]);

        return back()->with('success', 'Alert acknowledged');
    }

    public function acknowledgeMultiple(Request $request)
    {
        $ids = $request->input('ids', []);

        Alert::whereIn('id', $ids)->update([
            'acknowledged' => true,
            'acknowledged_by' => auth()->id(),
            'acknowledged_at' => now(),
        ]);

        return back()->with('success', count($ids) . ' alerts acknowledged');
    }

    public function destroy(Alert $alert)
    {
        $alert->delete();

        return back()->with('success', 'Alert deleted');
    }
}
