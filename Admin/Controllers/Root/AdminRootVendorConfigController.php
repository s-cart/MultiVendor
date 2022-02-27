<?php
namespace App\Plugins\Other\MultiVendor\Admin\Controllers\Root;

use SCart\Core\Admin\Models\AdminConfig;
use App\Plugins\Other\MultiVendor\Admin\Controllers\RootAdminVendorController;

class AdminRootVendorConfigController extends RootAdminVendorController
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
    }
    public function index()
    {
        $data = [
            'title' => sc_language_render('multi_vendor.MultiVendor_config'),
            'subTitle' => '',
            'icon' => 'fa fa-indent',        ];
        $configs = AdminConfig::getListConfigByCode(['code' => 'cache']);
        $data['configs'] = $configs;
        $data['urlUpdateConfigGlobal'] = sc_route_admin('admin_config_global.update');
        return view($this->plugin->pathPlugin.'::Admin.screen.root.config')
            ->with($data);
    }
}
