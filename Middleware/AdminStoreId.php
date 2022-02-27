<?php

namespace App\Plugins\Other\MultiVendor\Middleware;

use Closure;

class AdminStoreId
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if(vendor()->user()) {
            session(['adminStoreId' => vendor()->user()->store_id]);
        } else {
            session()->forget('adminStoreId');
        }
        return $next($request);
    }
}
