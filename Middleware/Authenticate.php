<?php

namespace App\Plugins\Other\MultiVendor\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class Authenticate
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $redirectTo = sc_route_admin('vendor.login');
        if (vendor()->guest() && !$this->shouldPassThrough($request)) {
            return redirect()->guest($redirectTo);
        }

        return $next($request);
    }

    /**
     * Determine if the request has a URI that should pass through verification.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return bool
     */
    protected function shouldPassThrough($request)
    {

        $routeName = $request->route()->getName();
        $excepts = [
            'vendor.login',
            'vendor.logout',
            'vendor.forgot',
            'vendor.register',
            'vendor.password_reset',
        ];
        return in_array($routeName, $excepts);

    }
}
