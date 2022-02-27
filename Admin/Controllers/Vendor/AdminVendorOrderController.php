<?php
namespace App\Plugins\Other\MultiVendor\Admin\Controllers\Vendor;

use SCart\Core\Front\Models\ShopAttributeGroup;
use SCart\Core\Front\Models\ShopCountry;
use SCart\Core\Front\Models\ShopCurrency;
use SCart\Core\Front\Models\ShopOrderStatus;
use SCart\Core\Front\Models\ShopPaymentStatus;
use SCart\Core\Front\Models\ShopShippingStatus;
use SCart\Core\Admin\Models\AdminProduct;
use App\Plugins\Other\MultiVendor\Admin\Models\AdminVendorOrder;
use Validator;
use App\Plugins\Other\MultiVendor\Admin\Controllers\RootAdminVendorController;

class AdminVendorOrderController extends RootAdminVendorController
{
    public $statusPayment, 
    $statusOrder, 
    $statusShipping, 
    $statusOrderMap, 
    $statusShippingMap, 
    $statusPaymentMap, 
    $currency, 
    $country, 
    $countryMap;

    public function __construct()
    {
        parent::__construct();
        $this->statusOrder    = ShopOrderStatus::getIdAll();
        $this->currency       = ShopCurrency::getListActive();
        $this->country        = ShopCountry::getCodeAll();
        $this->statusPayment  = ShopPaymentStatus::getIdAll();
        $this->statusShipping = ShopShippingStatus::getIdAll();

    }

    /**
     * Index interface.
     *
     * @return Content
     */
    public function index()
    {

        $data = [
            'title'         => sc_language_render('order.admin.list'),
            'subTitle'      => '',
            'icon'          => 'fa fa-indent',
            'removeList'    => 0, // 1 - Enable function delete list item
            'buttonRefresh' => 1, // 1 - Enable button refresh
            'buttonSort'    => 1, // 1 - Enable button sort
            'css'           => '', 
            'js'            => '',
        ];
        //Process add content
        $data['menuRight']    = sc_config_group('menuRight', \Request::route()->getName());
        $data['menuLeft']     = sc_config_group('menuLeft', \Request::route()->getName());
        $data['topMenuRight'] = sc_config_group('topMenuRight', \Request::route()->getName());
        $data['topMenuLeft']  = sc_config_group('topMenuLeft', \Request::route()->getName());
        $data['blockBottom']  = sc_config_group('blockBottom', \Request::route()->getName());

        $listTh = [
            'email'          => sc_language_render('order.email'),
            'subtotal'       => '<i class="fa fa-shopping-cart" aria-hidden="true" title="'.sc_language_render('order.subtotal').'"></i>',
            'shipping'       => '<i class="fa fa-truck" aria-hidden="true" title="'.sc_language_render('order.shipping').'"></i>',
            'discount'       => '<i class="fa fa-tags" aria-hidden="true" title="'.sc_language_render('order.discount').'"></i>',
            'tax'            => sc_language_render('order.tax'),
            'total'          => sc_language_render('order.total'),
            'payment_method' => '<i class="fa fa-credit-card" aria-hidden="true" title="'.sc_language_render('order.admin.payment_method_short').'"></i>',
            'status'         => sc_language_render('order.status'),
            'created_at'     => sc_language_render('admin.created_at'),
            'action'         => sc_language_render('action.title'),
        ];
        $sort_order   = sc_clean(request('sort_order') ?? 'id_desc');
        $keyword      = sc_clean(request('keyword') ?? '');
        $email        = sc_clean(request('email') ?? '');
        $from_to      = sc_clean(request('from_to') ?? '');
        $end_to       = sc_clean(request('end_to') ?? '');
        $order_status = sc_clean(request('order_status') ?? '');
        $arrSort = [
            'id__desc'         => sc_language_render('filter_sort.id_desc'),
            'id__asc'          => sc_language_render('filter_sort.id_asc'),
            'email__desc'      => sc_language_render('filter_sort.alpha_desc', ['alpha' => 'Email']),
            'email__asc'       => sc_language_render('filter_sort.alpha_asc', ['alpha' => 'Email']),
            'created_at__desc' => sc_language_render('filter_sort.value_desc', ['value' => 'Date']),
            'created_at__asc'  => sc_language_render('filter_sort.value_asc', ['value' => 'Date']),
        ];
        $dataSearch = [
            'keyword'      => $keyword,
            'email'        => $email,
            'from_to'      => $from_to,
            'end_to'       => $end_to,
            'sort_order'   => $sort_order,
            'arrSort'      => $arrSort,
            'order_status' => $order_status,
        ];

        $dataSearch['storeId'] = session('adminStoreId');
        $dataTmp = (new AdminVendorOrder)->getOrderListAdmin($dataSearch);

        $styleStatus = $this->statusOrder;
        array_walk($styleStatus, function (&$v, $k) {
            $v = '<span class="badge badge-' . (AdminVendorOrder::$mapStyleStatus[$k] ?? 'light') . '">' . $v . '</span>';
        });
        $dataTr = [];
        foreach ($dataTmp as $key => $row) {
            $dataTr[$row['id']] = [
                'email'          => $row['email'] ?? 'N/A',
                'subtotal'       => sc_currency_render_symbol($row['subtotal'] ?? 0, $row['currency']),
                'shipping'       => sc_currency_render_symbol($row['shipping'] ?? 0, $row['currency']),
                'discount'       => sc_currency_render_symbol($row['discount'] ?? 0, $row['currency']),
                'tax'            => sc_currency_render_symbol($row['tax'] ?? 0, $row['currency']),
                'total'          => sc_currency_render_symbol($row['total'] ?? 0, $row['currency']),
                'payment_method' => $row['payment_method'],
                'status'         => $styleStatus[$row['status']],
                'created_at'     => $row['created_at'],
                'action'         => '
                                <a href="' . sc_route_admin('admin_mvendor_order.detail', ['id' => $row['id'] ? $row['id'] : 'not-found-id']) . '">
                                 <span title="' . sc_language_render('order.admin.edit') . '" type="button" class="btn btn-flat btn-primary"><i class="fa fa-edit"></i></span>
                                </a>
                                '
                ,
            ];
        }

        $data['listTh'] = $listTh;
        $data['dataTr'] = $dataTr;
        $data['pagination'] = $dataTmp->appends(request()->except(['_token', '_pjax']))->links($this->templatePathAdmin.'component.pagination');
        $data['resultItems'] = sc_language_render('admin.result_item', ['item_from' => $dataTmp->firstItem(), 'item_to' => $dataTmp->lastItem(), 'total' =>  $dataTmp->total()]);

        //menuSort        
        $optionSort = '';
        foreach ($arrSort as $key => $sort) {
            $optionSort .= '<option  ' . (($sort_order == $key) ? "selected" : "") . ' value="' . $key . '">' . $sort . '</option>';
        }
        $data['optionSort'] = $optionSort;
        $data['urlSort'] = sc_route_admin('admin_mvendor_order.index', request()->except(['_token', '_pjax', 'sort_order']));
        //=menuSort

        //menuSearch        
        $optionStatus = '';
        foreach ($this->statusOrder as $key => $status) {
            $optionStatus .= '<option  ' . (($order_status == $key) ? "selected" : "") . ' value="' . $key . '">' . $status . '</option>';
        }
        //menuSearch        
        $optionStatus = '';
        foreach ($this->statusOrder as $key => $status) {
            $optionStatus .= '<option  ' . (($order_status == $key) ? "selected" : "") . ' value="' . $key . '">' . $status . '</option>';
        }
        $data['topMenuRight'][] = '
                <form action="' . sc_route_admin('admin_mvendor_order.index') . '" id="button_search">
                    <div class="input-group float-left">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>From:</label>
                                <div class="input-group">
                                <input type="text" name="from_to" id="from_to" class="form-control input-sm date_time rounded-0" placeholder="yyyy-mm-dd" /> 
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>To:</label>
                                <div class="input-group">
                                <input type="text" name="end_to" id="end_to" class="form-control input-sm date_time rounded-0" placeholder="yyyy-mm-dd" /> 
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>'.sc_language_render('order.admin.status').':</label>
                                <div class="input-group">
                                <select class="form-control rounded-0" name="order_status">
                                <option value="">'.sc_language_render('order.admin.search_order_status').'</option>
                                ' . $optionStatus . '
                                </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>'.sc_language_render('order.admin.search_email').':</label>
                                <div class="input-group">
                                    <input type="text" name="email" class="form-control rounded-0 float-right" placeholder="' . sc_language_render('order.admin.search_email') . '" value="' . $email . '">
                                    <div class="input-group-append">
                                        <button type="submit" class="btn btn-primary  btn-flat"><i class="fas fa-search"></i></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>';
    //=menuSearch

        return view($this->plugin->pathPlugin.'::Admin.screen.vendor.list')
            ->with($data);
    }

    /**
     * Order detail
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function edit($id)
    {
        $checkOrder = $this->checkPermisisonItem($id);
        if (!$checkOrder) {
            return redirect()->route('admin.data_not_found')->with(['url' => url()->full()]);
        }
        $order = AdminVendorOrder::getOrderAdmin($id, session('adminStoreId'));

        $paymentMethodTmp = sc_get_plugin_installed('payment', $onlyActive = false);
        foreach ($paymentMethodTmp as $key => $value) {
            $paymentMethod[$key] = sc_language_render($value->detail);
        }
        $shippingMethodTmp = sc_get_plugin_installed('shipping', $onlyActive = false);
        foreach ($shippingMethodTmp as $key => $value) {
            $shippingMethod[$key] = sc_language_render($value->detail);
        }
        return view($this->plugin->pathPlugin.'::Admin.screen.vendor.order_vendor_edit')->with(
            [
                "title"           => sc_language_render('order.order_detail'),
                "subTitle"        => '',
                'icon'            => 'fa fa-file-text-o',
                "order"           => $order,
                "statusOrder"     => $this->statusOrder,
                "statusPayment"   => $this->statusPayment,
                "statusShipping"  => $this->statusShipping,
                'dataTotal'       => AdminVendorOrder::getOrderTotal($id),
                'attributesGroup' => ShopAttributeGroup::pluck('name', 'id')->all(),
                'paymentMethod'   => $paymentMethod,
                'shippingMethod'  => $shippingMethod,
                'country'         => $this->country,
            ]
        );
    }

    /**
     * process update order
     * @return [json]           [description]
     */
    public function postOrderUpdate()
    {
        $id = request('pk');
        $code = request('name');
        $value = request('value');
        $order = AdminVendorOrder::where('id', $id)->where('store_id', session('adminStoreId'))->first();
        if (!$order) {
            return response()->json(['error' => 1, 'msg' => sc_language_render('admin.data_not_found_detail', ['msg' => 'order#'.$id]), 'detail' => '']);
        }
        $order->update([$code => $value]);
        return response()->json(
            ['error' => 0,'msg' => sc_language_render('order.admin.update_success')]
        );
    }

    /**
     * Check permisison item
     */
    public function checkPermisisonItem($id) {
        return (new AdminVendorOrder)->checkOrderAdmin($id);
    }
}
