<?php

namespace App\Plugins\Other\MultiVendor\Admin\Controllers\Vendor;

use SCart\Core\Admin\Models\AdminCustomer;
use SCart\Core\Admin\Models\AdminOrder;
use SCart\Core\Front\Models\ShopProductStore;
use SCart\Core\Front\Models\ShopNewsStore;
use Illuminate\Http\Request;
use App\Plugins\Other\MultiVendor\Admin\Controllers\RootAdminVendorController;

class DashboardVendorController extends RootAdminVendorController
{
    public function __construct()
    {
        parent::__construct();
    }
    
    public function index(Request $request)
    {
        $data                   = [];
        $data['title']          = sc_language_render('admin.dashboard');
        $data['totalOrder']     = sc_vendor_count_order(session('adminStoreId'));
        $data['totalProduct']   = ShopProductStore::where('store_id', session('adminStoreId'))->count();
        $data['topCustomer']    = AdminCustomer::where('store_id', session('adminStoreId'))
            ->orderBy('id', 'desc')
            ->limit(10)
            ->get();
        $data['totalCustomer']  = AdminCustomer::where('store_id', session('adminStoreId'))->count();
        $data['totalNews']      = ShopNewsStore::where('store_id', session('adminStoreId'))->count();
        $data['mapStyleStatus'] = AdminOrder::$mapStyleStatus;

        //Device statistics
        $dataCountries = sc_vendor_order_device_in_year(session('adminStoreId'));
        $arrDevice   = [];
        foreach ($dataCountries as $key => $row) {
            $arrDevice[] =  [
                'name' => ucfirst($row->device_type),
                'y' => $row->count,
                'sliced' => true,
                'selected' => ($key == 0) ? true : false,
            ];
        }
        $data['dataPie'] = json_encode($arrDevice);
        //End Device statistics


        //Order in 30 days
        $totalsInMonth = sc_vendor_total_order_in_month(session('adminStoreId'))->keyBy('md')->toArray();
        $rangDays = new \DatePeriod(
            new \DateTime('-1 month'),
            new \DateInterval('P1D'),
            new \DateTime('+1 day')
        );
        $orderInMonth  = [];
        $amountInMonth  = [];
        foreach ($rangDays as $i => $day) {
            $date = $day->format('m-d');
            $orderInMonth[$date] = $totalsInMonth[$date]['total_order'] ?? '';
            $amountInMonth[$date] = ($totalsInMonth[$date]['total_amount'] ?? 0);
        }
        $data['orderInMonth'] = $orderInMonth;
        $data['amountInMonth'] = $amountInMonth;

        //End order in 30 days
        
        //Order in 12 months
        $totalsMonth = sc_vendor_total_order_in_year(session('adminStoreId'))
            ->pluck('total_amount', 'ym')->toArray();
        $dataInYear = [];
        for ($i = 12; $i >= 0; $i--) {
            $date = date("Y-m", strtotime(date('Y-m-01') . " -$i months"));
            $dataInYear[$date] = $totalsMonth[$date] ?? 0;
        }
        $data['dataInYear'] = $dataInYear;
        //End order in 12 months

        return view($this->plugin->pathPlugin.'::Admin.dashboard', $data);
    }


    /**
     * Page not found
     *
     * @return  [type]  [return description]
     */
    public function dataNotFound()
    {
        $data = [
            'title' => sc_language_render('admin.data_not_found'),
            'icon' => '',
            'url' => session('url'),
        ];
        return view($this->plugin->pathPlugin.'::Admin.data_not_found', $data);
    }


    /**
     * Page deny
     *
     * @return  [type]  [return description]
     */
    public function deny()
    {
        $data = [
            'title' => sc_language_render('admin.deny'),
            'icon' => '',
            'method' => session('method'),
            'url' => session('url'),
        ];
        return view($this->plugin->pathPlugin.'::Admin.deny', $data);
    }

    /**
     * Page deny
     *
     * @return  [type]  [return description]
     */
    public function accountInactive()
    {
        $data = [
            'title' => sc_language_render('multi_vendor.account_inactive_title'),
            'icon' => '',
            'url' => session('url'),
        ];
        return view($this->plugin->pathPlugin.'::Admin.account_inactive', $data);
    }

    /**
     * [denySingle description]
     *
     * @return  [type]  [return description]
     */
    public function denySingle()
    {
        $data = [
            'method' => session('method'),
            'url' => session('url'),
        ];
        return view($this->plugin->pathPlugin.'::Admin.deny_single', $data);
    }


}
