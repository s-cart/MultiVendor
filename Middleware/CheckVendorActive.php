<?php
namespace App\Plugins\Other\MultiVendor\Middleware;
use Closure;
class CheckVendorActive
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
        if (!vendor()->user()->status) {
            return redirect()->route('vendor_admin.account_inactive')->with(['url' => $request->url()]);
        }
        return $next($request);
    }
}
