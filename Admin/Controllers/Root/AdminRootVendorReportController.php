<?php
namespace App\Plugins\Other\MultiVendor\Admin\Controllers\Root;

use SCart\Core\Admin\Admin;
use SCart\Core\Front\Models\ShopAttributeGroup;
use SCart\Core\Front\Models\ShopCountry;
use SCart\Core\Front\Models\ShopCurrency;
use SCart\Core\Front\Models\ShopOrderDetail;
use SCart\Core\Front\Models\ShopOrderStatus;
use SCart\Core\Front\Models\ShopPaymentStatus;
use SCart\Core\Front\Models\ShopShippingStatus;
use SCart\Core\Admin\Models\AdminCustomer;
use SCart\Core\Admin\Models\AdminOrder;
use SCart\Core\Admin\Models\AdminStore;
use SCart\Core\Admin\Models\AdminProduct;
use Validator;
use Illuminate\Http\Request;
use App\Plugins\Other\MultiVendor\Admin\Controllers\RootAdminVendorController;

class AdminRootVendorReportController extends RootAdminVendorController
{
    public $statusPayment;
    public $statusOrder;
    public $statusShipping;
    public $statusOrderMap;
    public $statusShippingMap;
    public $statusPaymentMap;
    public $currency;
    public $country;
    public $countryMap;

    public function __construct()
    {
        parent::__construct();
        $this->statusOrder    = ShopOrderStatus::getIdAll();
        $this->currency       = ShopCurrency::getListActive();
        $this->country        = ShopCountry::getCodeAll();
        $this->statusPayment  = ShopPaymentStatus::getIdAll();
        $this->statusShipping = ShopShippingStatus::getIdAll();
    }

    public function index(Request $request)
    {
        $data                   = [];
        $data['title']          = sc_language_render('multi_vendor.vendor_report');

        $storeTable = (new AdminStore)->getTable();
        $orderTable = (new AdminOrder)->getTable();
        $startDate = request('startDate') ?? date("Y-m-01");
        $endDate = request('endDate') ?? date('Y-m-d');
        $storeId = request('store_id') ?? '';
        $status = request('status_id') ?? '';
        $endDateProcess = request('endDate') ?? date('Y-m-d H:i:s');
        $urlProcess = sc_route_admin('admin_MultiVendorReport.index');

        if ($request->isMethod('post')) {
            $urlProcess = $urlProcess.'?startDate='.$startDate.'&endDate='.$endDate.'&store_id='.$storeId.'&status_id='.$status;
            if (!$storeId) {
                return redirect($urlProcess)->with('store_empty', 1);
            } else {
                $dataOrder = [];
                $dataOrderStore = (new AdminOrder)->where('store_id', $storeId)
                    ->whereRaw($orderTable.'.created_at >= ?  AND '.$orderTable.'.created_at <= ?', [$startDate, $endDateProcess]);
                if ($status) {
                    $dataOrderStore = $dataOrderStore->where($orderTable.'.status', $status);
                }
                $dataOrderStore = $dataOrderStore->get();
                if (count($dataOrderStore)) {
                    $dataOrder[] = [
                        'id' => sc_language_render('order.id'),
                        'domain' => sc_language_render('order.domain'),
                        'email' => sc_language_render('order.email'),
                        'created_at' => sc_language_render('order.date'),
                        'subtotal' => sc_language_render('order.subtotal'),
                        'shipping' => sc_language_render('order.shipping'),
                        'discount' => sc_language_render('order.discount'),
                        'tax' => sc_language_render('order.tax'),
                        'total' => sc_language_render('order.total'),
                        'received' => sc_language_render('order.received'),
                        'balance' => sc_language_render('order.totals.balance'),
                        'currency' => sc_language_render('order.currency'),
                        'exchange_rate' => sc_language_render('order.exchange_rate'),
                        'status' => sc_language_render('order.order_status'),
                    ];
                    foreach ($dataOrderStore as $key => $row) {
                        $dataOrder[] = [
                            'id' => '#'.$row['id'],
                            'domain' => $row['domain'],
                            'email' => $row['email'],
                            'created_at' => $row['created_at'],
                            'subtotal' => $row['subtotal'],
                            'shipping' => $row['shipping'],
                            'discount' => $row['discount'],
                            'tax' => $row['tax'],
                            'total' => $row['total'],
                            'received' => $row['received'],
                            'balance' => $row['balance'],
                            'currency' => $row['currency'],
                            'exchange_rate' => $row['exchange_rate'],
                            'status' => $this->statusOrder[$row['status']] ?? $row['status'],
                        ];
                    }
                }
                    $options['title'] = sc_language_render('order.admin.list').' '.$startDate.' - '.$endDate;
                    $options['filename'] = 'Store-'.$storeId.'-'.$startDate.' - '.$endDate;
                    $options['sheetname'] = 'Order list';
                    return \Export::export('xls', $dataOrder, $options);
            }
        }

        $countOrderVendor =  AdminOrder::selectRaw('count(*) AS count, '.$storeTable.'.code')
            ->join($storeTable, $storeTable.'.id', 'store_id')
            ->whereRaw($orderTable.'.created_at >= ?  AND '.$orderTable.'.created_at <= ?', [$startDate, $endDateProcess]);
        if ($storeId) {
            $countOrderVendor = $countOrderVendor->where($orderTable.'.store_id', $storeId);
        }
        if ($status) {
            $countOrderVendor = $countOrderVendor->where($orderTable.'.status', $status);
        }
        $countOrderVendor = $countOrderVendor->groupBy($storeTable.'.code')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->pluck('count', $storeTable.'.code')
            ->toArray();
        $data['countOrderVendor'] = $countOrderVendor;

        
        $data['urlProcess'] = $urlProcess;
        $data['startDate'] = $startDate;
        $data['endDate'] = $endDate;
        $data['storeId'] = $storeId;
        $data['status'] = $status;
        $data['statusOrder'] = $this->statusOrder;
        return view($this->plugin->pathPlugin.'::Admin.screen.root.report', $data);
    }

}
