<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureTenantIsActive
{
    /**
     * If the current tenant is suspended (inactive), show the suspended page.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $tenancy = app(\Stancl\Tenancy\Tenancy::class);
        if (! $tenancy->initialized || ! $tenancy->tenant) {
            return $next($request);
        }

        $tenant = $tenancy->tenant;
        $status = $tenant->getAttribute('status') ?? 'active';
        $isActive = $tenant->getAttribute('is_active');
        if ($status === 'suspended' || $isActive === false) {
            return response()->view('central.suspended');
        }

        return $next($request);
    }
}
