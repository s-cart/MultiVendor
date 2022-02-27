<?php

namespace App\Plugins\Other\MultiVendor\Admin\Models;

use SCart\Core\Admin\Models\AdminOrder;
use SCart\Core\Front\Models\ShopOrderTotal;

class AdminVendorOrder extends AdminOrder
{
    /**
     * Check order id store exits in store
     *
     * @param   [type]  $id  [$id description]
     *
     * @return  [type]       [return description]
     */
    public function checkOrderAdmin($id) {
        return $this
            ->where('store_id', session('adminStoreId'))
            ->where('id', $id)
            ->count();
    }


    /**
     * Get row order total
     *
     * @param   [type]  $rowId  [$rowId description]
     *
     * @return  [type]          [return description]
     */
    public static function getRowOrderTotal($rowId) {
        return ShopOrderTotal::find($rowId);
    }

    /**
     * Get order vendor in month
     */
    public function getSumOrderTotalStoreInMonth(string $storeId) {
        return (new AdminOrder)
        ->selectRaw('DATE_FORMAT(created_at, "%m-%d") AS md,
        SUM(total/exchange_rate) AS total_amount, count(id) AS total_order')
        ->where('store_id', $storeId)
        ->whereRaw('created_at >=  DATE_FORMAT(DATE_SUB(CURRENT_DATE(), INTERVAL 1 MONTH), "%Y-%m-%d")')
        ->groupBy('md')->get();
    }

    /**
     * Get order vendor in year
     */
    public function getSumOrderTotalStoreInYear(string $storeId) {
        return (new AdminOrder)
        ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") AS ym,
        SUM(total/exchange_rate) AS total_amount')
        ->where('store_id', $storeId)
        ->whereRaw('DATE_FORMAT(created_at, "%Y-%m") >=  DATE_FORMAT(DATE_SUB(CURRENT_DATE(), INTERVAL 12 MONTH), "%Y-%m")')
        ->groupBy('ym')->get();
    }

    /**
     * Get order vendor in year
     */
    public function getSumVendorOrderCountryInYear(string $storeId) {
        return (new AdminOrder)
        ->selectRaw('country, count(id) as count')
        ->where('store_id', $storeId)
        ->whereRaw('DATE(created_at) >=  DATE_SUB(DATE(NOW()), INTERVAL 12 MONTH)')
        ->groupBy('country')
        ->orderBy('count', 'desc')
        ->get();
    }

    /**
     * Get device order in year
     *
     * @return  [type]  [return description]
    */
    public function getDeviceStoreInYear(string $storeId) {
        return (new AdminOrder)
        ->selectRaw('device_type, count(id) as count')
        ->where('store_id', $storeId)
        ->whereRaw('DATE(created_at) >=  DATE_SUB(DATE(NOW()), INTERVAL 12 MONTH)')
        ->groupBy('device_type')
        ->orderBy('count', 'desc')
        ->get();
    }
    
    /**
     * Count order new of vendor
     */
    public function countOrderNewStore(string $storeId) {
        $tableOrder = (new AdminOrder)->getTable();
        $tableVendorOrder = $this->getTable();
        return (new AdminOrder)
        ->join($tableVendorOrder, $tableVendorOrder.'.order_id', $tableOrder.'.id')
        ->where($tableVendorOrder.'.store_id', $storeId)
        ->where($tableOrder.'.status', 1)
        ->count();
    }
}
