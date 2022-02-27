<?php
namespace App\Plugins\Other\MultiVendor\Middleware;
use Closure;
class CheckStoreExist
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
        if (!\SCart\Core\Admin\Models\AdminStore::find(vendor()->user()->store_id) && !$this->shouldPassThrough($request)) {
            return redirect()->route('admin_mvendor_info.update')->with(['error' => sc_language_render('multi_vendor.update_info_store_msg')]);
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

        $routeName = $request->path();
        $excepts = [
            'vendor_admin/vendor_update',
        ];
        return in_array($routeName, $excepts);

    }
}
