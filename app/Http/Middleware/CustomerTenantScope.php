<?php

namespace App\Http\Middleware;

use App\Models\Cdr;
use App\Models\ActiveCall;
use App\Models\Alert;
use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CustomerTenantScope
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth('customer')->user();

        if ($user) {
            $customerId = $user->customer_id;

            // Apply global scope to CDRs
            Cdr::addGlobalScope('customer_tenant', function (Builder $query) use ($customerId) {
                $query->where('customer_id', $customerId);
            });

            // Apply global scope to Active Calls
            ActiveCall::addGlobalScope('customer_tenant', function (Builder $query) use ($customerId) {
                $query->where('customer_id', $customerId);
            });

            // Store customer context for views
            view()->share('portalCustomer', $user->customer);
            view()->share('portalUser', $user);
            view()->share('portalSettings', $user->customer->portalSettings);
        }

        return $next($request);
    }
}
