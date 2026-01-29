<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCustomerPortalEnabled
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth('customer')->user();

        if (!$user) {
            return redirect()->route('portal.login');
        }

        // Check if user is active
        if (!$user->active) {
            auth('customer')->logout();
            return redirect()->route('portal.login')
                ->with('error', 'Your account has been disabled.');
        }

        // Check if customer portal is enabled
        $customer = $user->customer;
        if (!$customer || !$customer->portal_enabled) {
            auth('customer')->logout();
            return redirect()->route('portal.login')
                ->with('error', 'Portal access is not enabled for your account.');
        }

        // Check if customer is active
        if (!$customer->active) {
            auth('customer')->logout();
            return redirect()->route('portal.login')
                ->with('error', 'Your company account has been suspended.');
        }

        return $next($request);
    }
}
