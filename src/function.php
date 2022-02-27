<?php
use App\Plugins\Other\MultiVendor\Admin\Models\AdminVendorCategory;
use App\Plugins\Other\MultiVendor\Admin\Models\AdminVendorOrder;
use App\Plugins\Other\MultiVendor\Admin\Models\AdminMoneyProcess;
use App\Plugins\Other\MultiVendor\Models\PluginModel;

/**
 * Get id seller
 *
 * @return  [type]  [return description]
 */
if (!function_exists('vendor') && sc_config_global('MultiVendor')) {
    function vendor() {
        return auth()->guard('vendor');
    }
}


/**
 * Get list category of vendor
 *
 * @return  [type]  [return description]
 */
if (!function_exists('sc_vendor_get_categories_admin') && sc_config_global('MultiVendor')) {
    function sc_vendor_get_categories_admin() {
        return AdminVendorCategory::getCategoriesAdmin();
    }
}

/**
 * Get list category of vendor front
 *
 * @return  [type]  [return description]
 */
if (!function_exists('sc_vendor_get_categories_front') && sc_config_global('MultiVendor')) {
    function sc_vendor_get_categories_front(string $storeId) {
        return PluginModel::getListVendorCategory($storeId);
    }
}


/**
 * Count order of vendor
 */
if (!function_exists('sc_vendor_count_order') && sc_config_global('MultiVendor')) {
    function sc_vendor_count_order(string $storeId) {
        return AdminVendorOrder::where('store_id', $storeId)->count();
    }
}


/**
 * Get total order store in month
 */
if (!function_exists('sc_vendor_total_order_in_month') && sc_config_global('MultiVendor')) {
    function sc_vendor_total_order_in_month(string $storeId) {
        return (new AdminVendorOrder)->getSumOrderTotalStoreInMonth($storeId);
    }
}

/**
 * Get total order store in year
 */
if (!function_exists('sc_vendor_total_order_in_year') && sc_config_global('MultiVendor')) {
    function sc_vendor_total_order_in_year(string $storeId) {
        return (new AdminVendorOrder)->getSumOrderTotalStoreInYear($storeId);
    }
}

/**
 * Get total order vendor country in year
 */
if (!function_exists('sc_vendor_order_country_in_year') && sc_config_global('MultiVendor')) {
    function sc_vendor_order_country_in_year(string $storeId) {
        return (new AdminVendorOrder)->getSumVendorOrderCountryInYear($storeId);
    }
}

/**
 * Get total order vendor device in year
 */
if (!function_exists('sc_vendor_order_device_in_year') && sc_config_global('MultiVendor')) {
    function sc_vendor_order_device_in_year(string $storeId) {
        return (new AdminVendorOrder)->getDeviceStoreInYear($storeId);
    }
}


/**
 * Get url store
 */
if (!function_exists('sc_vendor_get_url') && sc_config_global('MultiVendor')) {
    function sc_vendor_get_url(string $storeId) {
        $store = \SCart\Core\Admin\Models\AdminStore::find($storeId);
        if (!$store) {
            return null;
        }
        if (!empty($store->domain)) {
            return 'http://'.$store->domain;
        } else {
            return sc_route_admin('MultiVendor.detail', ['code' => $store->code]);
        }
    }
}


/**
 * Get top new vendor
 */
if (!function_exists('sc_vendor_top_new') && sc_config_global('MultiVendor')) {
    function sc_vendor_top_new() {
        return \SCart\Core\Admin\Models\AdminStore::where('status', 1)->where('id', '<>', 1)->orderBy('id', 'desc')->limit(8)->get();
    }
}


