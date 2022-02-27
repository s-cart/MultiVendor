<?php
#App\Plugins\Other\MultiVendor\Models\PluginModel.php
namespace App\Plugins\Other\MultiVendor\Models;

use App\Plugins\Other\MultiVendor\Models\VendorCategory;
use App\Plugins\Other\MultiVendor\Models\VendorProductCategory;
use App\Plugins\Other\MultiVendor\Models\VendorUser;
use App\Plugins\Other\MultiVendor\Admin\Models\AdminMoneyProcess;
use SCart\Core\Front\Models\ShopStore;
use SCart\Core\Front\Models\ShopProduct;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class PluginModel
{

    public function uninstallExtension()
    {
        (new VendorCategory)->uninstall();
        (new VendorProductCategory)->uninstall();
        (new VendorUser)->uninstall();
        (new AdminMoneyProcess)->uninstall();

        // if (Schema::hasColumn(SC_DB_PREFIX.'admin_store', 'level'))
        // {
        //     Schema::table(SC_DB_PREFIX.'admin_store', function (Blueprint $table) {
        //         $table->dropColumn('level');
        //     });
        // }
        return ['error' => 0, 'msg' => 'uninstall success'];
    }

    public function installExtension()
    {
        (new VendorCategory)->install();
        (new VendorProductCategory)->install();
        (new VendorUser)->install();
        (new AdminMoneyProcess)->install();
        // if (!Schema::hasColumn(SC_DB_PREFIX.'admin_store', 'level'))
        // {
        //     Schema::table(SC_DB_PREFIX.'admin_store', function (Blueprint $table) {
        //         $table->integer('level')->default(0)->index()->comment('0 -normal');
        //     });
        // }
        return ['error' => 0, 'msg' => 'install success'];
    }
    
    /**
     * Get store by code
     *
     * @param   [type]  $code  [$code description]
     *
     * @return  [type]         [return description]
     */
    public static function getStoreByCode($code = null) {
        return ShopStore::with('descriptions')
            ->where('code', $code)
            ->where('status', 1) // open vendor
            ->first();
    }

    /**
     * Get list category of store
     *
     * @param   [type]  $storeId  [$storeId description]
     *
     * @return  [type]            [return description]
     */
    public static function getListVendorCategory($storeId = null) {
        $storeId = $storeId ? $storeId: config('app.storeId');
        return (new VendorCategory)->setStore($storeId)
            ->getData();
    }

    /**
     * Get products to sub category
     *
     * @param   [type]$cId      [$cId description]
     * @param   [type]$storeId  [$storeId description]
     * @param   null  $limit    [$limit description]
     * @param   null            [ description]
     *
     * @return  [type]          [return description]
     */
    public static function getListProductByVendorCategory($cId, $storeId = null, $limit = null) {
        $storeId = $storeId ? $storeId: config('app.storeId');
        $query =  (new ShopProduct)->setStore($storeId)->getProductToVendorCategory($cId);
        if($limit) {
            $data = $query->setLimit($limit)->getData();
        } else {
            $data = $query->setPaginate(1)->getData();
        }
        return $data;
    }
}
