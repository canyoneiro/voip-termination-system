<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\CustomerIp;
use App\Models\CustomerIpRequest;
use Illuminate\Http\Request;

class IpController extends Controller
{
    public function index()
    {
        $user = auth('customer')->user();
        $customer = $user->customer;

        // Get authorized IPs
        $ips = CustomerIp::where('customer_id', $customer->id)
            ->orderBy('created_at', 'desc')
            ->get();

        // Get pending requests
        $pendingRequests = CustomerIpRequest::where('customer_id', $customer->id)
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->get();

        // Get request history
        $requestHistory = CustomerIpRequest::where('customer_id', $customer->id)
            ->whereIn('status', ['approved', 'rejected'])
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        $settings = $customer->portalSettings;

        return view('portal.ips.index', compact('ips', 'pendingRequests', 'requestHistory', 'settings'));
    }

    public function createRequest()
    {
        $user = auth('customer')->user();

        if (!$user->canRequestIps()) {
            abort(403, 'IP requests are not enabled for your account.');
        }

        return view('portal.ips.create');
    }

    public function storeRequest(Request $request)
    {
        $user = auth('customer')->user();

        if (!$user->canRequestIps()) {
            abort(403, 'IP requests are not enabled for your account.');
        }

        $data = $request->validate([
            'ip_address' => [
                'required',
                'ip',
                function ($attribute, $value, $fail) use ($user) {
                    // Check if already authorized
                    $exists = CustomerIp::where('customer_id', $user->customer_id)
                        ->where('ip_address', $value)
                        ->exists();
                    if ($exists) {
                        $fail('This IP address is already authorized.');
                    }

                    // Check if already requested
                    $pending = CustomerIpRequest::where('customer_id', $user->customer_id)
                        ->where('ip_address', $value)
                        ->where('status', 'pending')
                        ->exists();
                    if ($pending) {
                        $fail('A request for this IP address is already pending.');
                    }
                },
            ],
            'description' => 'nullable|string|max:255',
            'justification' => 'required|string|max:1000',
        ]);

        CustomerIpRequest::create([
            'customer_id' => $user->customer_id,
            'customer_user_id' => $user->id,
            'ip_address' => $data['ip_address'],
            'description' => $data['description'],
            'justification' => $data['justification'],
        ]);

        return redirect()->route('portal.ips.index')
            ->with('success', 'IP request submitted successfully. It will be reviewed by our team.');
    }

    public function cancelRequest(CustomerIpRequest $request)
    {
        $user = auth('customer')->user();

        // Verify ownership
        if ($request->customer_id !== $user->customer_id) {
            abort(403);
        }

        // Can only cancel pending requests
        if (!$request->isPending()) {
            abort(400, 'Only pending requests can be cancelled.');
        }

        $request->delete();

        return redirect()->route('portal.ips.index')
            ->with('success', 'IP request cancelled.');
    }
}
