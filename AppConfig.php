<?php
namespace App\Plugins\Other\MultiVendor;

use App\Plugins\Other\MultiVendor\Models\PluginModel;
use SCart\Core\Admin\Models\AdminConfig;
use SCart\Core\Admin\Models\AdminMenu;
use SCart\Core\Front\Models\Languages;
use Illuminate\Support\Facades\File;
use App\Plugins\ConfigDefault;

class AppConfig extends ConfigDefault
{
    public function __construct()
    {
        //Read config from config.json
        $config = file_get_contents(__DIR__.'/config.json');
        $config = json_decode($config, true);
    	$this->configGroup = $config['configGroup'];
    	$this->configCode = $config['configCode'];
        $this->configKey = $config['configKey'];
        $this->scartVersion = $config['scartVersion'] ?? [];
        //Path
        $this->pathPlugin = $this->configGroup . '/' . $this->configCode . '/' . $this->configKey;
        //Language
        $this->title = trans($this->pathPlugin.'::lang.title');
        //Image logo or thumb
        $this->image = $this->pathPlugin.'/'.$config['image'];
        //
        $this->version = $config['version'];
        $this->auth = $config['auth'];
        $this->link = $config['link'];
    }

    public function install()
    {
        $return = ['error' => 0, 'msg' => ''];

        $checkMultiStore = AdminConfig::whereIn('key', ['MultiStorePro', 'MultiStore', 'MultiVendorPro'])->first();
        if ($checkMultiStore) {
            //Check plugin multi-store exist
            return ['error' => 1, 'msg' =>  sc_language_render('plugin.plugin_action.plugin_exist')];
        }
        
        $check = AdminConfig::where('key', $this->configKey)->first();
        if ($check) {
            //Check multi-vendor  exist
            $return = ['error' => 1, 'msg' =>  sc_language_render('plugin.plugin_action.plugin_exist')];
        } else {
            //Insert plugin to config
            $dataInsert = [
                [
                    'group'  => $this->configGroup,
                    'code'   => $this->configCode,
                    'key'    => $this->configKey,
                    'sort'   => 0,
                    'store_id' => SC_ID_GLOBAL,
                    'value'  => self::ON, //Enable extension
                    'detail' => $this->pathPlugin.'::lang.title',
                ],
                [
                    'group'  => '',
                    'code'   => $this->configKey.'_config',
                    'key'    => 'domain_strict',
                    'sort'   => 0,
                    'store_id' => SC_ID_GLOBAL,
                    'value'  => 0,
                    'detail' => 'multi_vendor.MultiVendor_domain_strict',
                ],
                [
                    'group'  => '',
                    'code'   => $this->configKey.'_config',
                    'key'    => 'MultiVendor_allow_register',
                    'sort'   => 0,
                    'store_id' => SC_ID_GLOBAL,
                    'value'  => 1,
                    'detail' => 'multi_vendor.MultiVendor_allow_register',
                ],
                [
                    'group'  => '',
                    'code'   => $this->configKey.'_config',
                    'key'    => 'MultiVendor_product_auto_approve',
                    'sort'   => 0,
                    'store_id' => SC_ID_GLOBAL,
                    'value'  => 0,
                    'detail' => 'multi_vendor.MultiVendor_product_auto_approve',
                ],
                [
                    'group'  => '',
                    'code'   => $this->configKey.'_config',
                    'key'    => 'MultiVendor_vendor_auto_approve',
                    'sort'   => 0,
                    'store_id' => SC_ID_GLOBAL,
                    'value'  => 1,
                    'detail' => 'multi_vendor.MultiVendor_vendor_auto_approve',
                ],
                [
                    'group'  => '',
                    'code'   => $this->configKey.'_config',
                    'key'    => 'MultiVendor_commission',
                    'sort'   => 0,
                    'store_id' => SC_ID_GLOBAL,
                    'value'  => 10,
                    'detail' => 'multi_vendor.MultiVendor_commission',
                ],
                [
                    'group'  => '',
                    'code'   => $this->configKey.'_config',
                    'key'    => 'MultiVendor_quick_order',
                    'sort'   => 0,
                    'store_id' => SC_ID_GLOBAL,
                    'value'  => 1,
                    'detail' => 'multi_vendor.MultiVendor_quick_order',
                ],
            ];
            
            try {
                $process = AdminConfig::insertOrIgnore(
                    $dataInsert
                );

                $idBlock = AdminMenu::insertGetId(
                    [
                        'parent_id' => 0,
                        'sort'      => 250,
                        'title'     => sc_language_render('multi_vendor.plugin_block'),
                        'icon'      => 'nav-icon fab fa-shopify',
                        'key'       => 'ADMIN_MVENDOR_SETTING',
                    ]
                );

                $dataInsert = [
                    [
                        'parent_id' => $idBlock,
                        'sort'      => 1,
                        'title'     => sc_language_render('multi_vendor.vendor_store'),
                        'icon'      => 'fas fa-h-square',
                        'uri'       => 'admin::MultiVendor/store'
                    ],
                    [
                        'parent_id' => $idBlock,
                        'sort'      => 2,
                        'title'     => sc_language_render('multi_vendor.vendor_user'),
                        'icon'      => 'fa fa-user-circle',
                        'uri'       => 'admin::MultiVendor/vendor'
                    ],
                    [
                        'parent_id' => $idBlock,
                        'sort'      => 3,
                        'title'     => sc_language_render('multi_vendor.vendor_config'),
                        'icon'      => 'fas fa-cogs ',
                        'uri'       => 'admin::MultiVendor/config'
                    ],
                    [
                        'parent_id' => $idBlock,
                        'sort'      => 5,
                        'title'     => sc_language_render('multi_vendor.vendor_report'),
                        'icon'      => 'fa fa-bars',
                        'uri'       => 'admin::MultiVendor/report'
                    ]
                ];
                AdminMenu::insert(
                    $dataInsert
                );

                $dataLang = [
                    ['code' => 'multi_vendor.plugin_block', 'text' => 'MARKETPLACE', 'position' => 'multi_vendor', 'location' => 'en'],
                    ['code' => 'multi_vendor.plugin_block', 'text' => 'CH??? B??N H??NG', 'position' => 'multi_vendor', 'location' => 'vi'],
                    ['code' => 'multi_vendor.account_inactive_title', 'text' => 'Access denied!', 'position' => 'multi_vendor', 'location' => 'en'],
                    ['code' => 'multi_vendor.account_inactive_title', 'text' => 'Truy c???p b??? t??? ch???i', 'position' => 'multi_vendor', 'location' => 'vi'],
                    ['code' => 'multi_vendor.account_inactive_msg', 'text' => 'The account has not been activated or has been locked!', 'position' => 'multi_vendor', 'location' => 'en'],
                    ['code' => 'multi_vendor.account_inactive_msg', 'text' => 'T??i kho???n ch??a ???????c k??ch ho???t ho???c ???? b??? kh??a!', 'position' => 'multi_vendor', 'location' => 'vi'],
                    ['code' => 'multi_vendor.update_info_store_msg', 'text' => 'Please update your store information!', 'position' => 'multi_vendor', 'location' => 'en'],
                    ['code' => 'multi_vendor.update_info_store_msg', 'text' => 'Vui l??ng c???p nh???t th??ng tin c???a h??ng c???a b???n!', 'position' => 'multi_vendor', 'location' => 'vi'],
                    ['code' => 'multi_vendor.update_info_store_title', 'text' => 'Update store information', 'position' => 'multi_vendor', 'location' => 'en'],
                    ['code' => 'multi_vendor.update_info_store_title', 'text' => 'C???p nh???t th??ng tin c???a h??ng', 'position' => 'multi_vendor', 'location' => 'vi'],
                    ['code' => 'multi_vendor.vendor_store', 'text' => 'Vendor store', 'position' => 'multi_vendor', 'location' => 'en'],
                    ['code' => 'multi_vendor.vendor_store', 'text' => 'Gian h??ng ng?????i b??n', 'position' => 'multi_vendor', 'location' => 'vi'],
                    ['code' => 'multi_vendor.vendor_user', 'text' => 'Vendor user', 'position' => 'multi_vendor', 'location' => 'en'],
                    ['code' => 'multi_vendor.vendor_user', 'text' => 'T??i kho???n ng?????i b??n', 'position' => 'multi_vendor', 'location' => 'vi'],
                    ['code' => 'multi_vendor.vendor_config', 'text' => 'Config', 'position' => 'multi_vendor', 'location' => 'en'],
                    ['code' => 'multi_vendor.vendor_config', 'text' => 'C???u h??nh', 'position' => 'multi_vendor', 'location' => 'vi'],
                    ['code' => 'multi_vendor.vendor_report', 'text' => 'Report', 'position' => 'multi_vendor', 'location' => 'en'],
                    ['code' => 'multi_vendor.vendor_report', 'text' => 'B??o c??o', 'position' => 'multi_vendor', 'location' => 'vi'],
                    ['code' => 'multi_vendor.vendor_add', 'text' => 'Add new vendor', 'position' => 'multi_vendor', 'location' => 'en'],
                    ['code' => 'multi_vendor.vendor_add', 'text' => 'Th??m ng?????i b??n', 'position' => 'multi_vendor', 'location' => 'vi'],
                    ['code' => 'multi_vendor.config', 'text' => 'Config information', 'position' => 'multi_vendor', 'location' => 'en'],
                    ['code' => 'multi_vendor.config', 'text' => 'Th??ng tin c???u h??nh', 'position' => 'multi_vendor', 'location' => 'vi'],
                    ['code' => 'multi_vendor.vendor_search_place', 'text' => 'Search name, email vendor', 'position' => 'multi_vendor', 'location' => 'en'],
                    ['code' => 'multi_vendor.vendor_search_place', 'text' => 'T??m ki???m t??n, email', 'position' => 'multi_vendor', 'location' => 'vi'],
                    ['code' => 'multi_vendor.password', 'text' => 'Password', 'position' => 'multi_vendor', 'location' => 'en'],
                    ['code' => 'multi_vendor.password', 'text' => 'M???t kh???u', 'position' => 'multi_vendor', 'location' => 'vi'],
                    ['code' => 'multi_vendor.password_forgot', 'text' => 'Forgot password', 'position' => 'multi_vendor', 'location' => 'en'],
                    ['code' => 'multi_vendor.password_forgot', 'text' => 'Qu??n m???t kh???u', 'position' => 'multi_vendor', 'location' => 'vi'],
                    ['code' => 'multi_vendor.login_title', 'text' => 'Login page', 'position' => 'multi_vendor', 'location' => 'en'],
                    ['code' => 'multi_vendor.login_title', 'text' => 'Trang ????ng nh???p', 'position' => 'multi_vendor', 'location' => 'vi'],
                    ['code' => 'multi_vendor.register_success', 'text' => 'Successful register', 'position' => 'multi_vendor', 'location' => 'en'],
                    ['code' => 'multi_vendor.register_success', 'text' => '????ng k?? th??nh c??ng', 'position' => 'multi_vendor', 'location' => 'vi'],
                    ['code' => 'multi_vendor.signup', 'text' => 'Signup', 'position' => 'multi_vendor', 'location' => 'en'],
                    ['code' => 'multi_vendor.signup', 'text' => '????ng k??', 'position' => 'multi_vendor', 'location' => 'vi'],
                    ['code' => 'multi_vendor.title_register', 'text' => 'Account register', 'position' => 'multi_vendor', 'location' => 'en'],
                    ['code' => 'multi_vendor.title_register', 'text' => '????ng k?? t??i kho???n', 'position' => 'multi_vendor', 'location' => 'vi'],
                    ['code' => 'multi_vendor.password_reset', 'text' => 'Password reset', 'position' => 'multi_vendor', 'location' => 'en'],
                    ['code' => 'multi_vendor.password_reset', 'text' => 'Reset m???t kh???u', 'position' => 'multi_vendor', 'location' => 'vi'],
                    ['code' => 'multi_vendor.password_confirm', 'text' => 'Password confirm', 'position' => 'multi_vendor', 'location' => 'en'],
                    ['code' => 'multi_vendor.password_confirm', 'text' => 'X??c nh???n m???t kh???u', 'position' => 'multi_vendor', 'location' => 'vi'],
                    ['code' => 'multi_vendor.phone_regex', 'text' => 'The phone format is not correct. Length 8-14, use only 0-9 and the "-" SIGN.', 'position' => 'multi_vendor', 'location' => 'en'],
                    ['code' => 'multi_vendor.phone_regex', 'text' => 'S??? ??i???n tho???i ?????nh d???ng kh??ng ????ng. Chi???u d??i 8-14, ch??? s??? d???ng s??? 0-9 v?? "-"', 'position' => 'multi_vendor', 'location' => 'vi'],
                    ['code' => 'multi_vendor.country', 'text' => 'Country', 'position' => 'multi_vendor', 'location' => 'en'],
                    ['code' => 'multi_vendor.country', 'text' => 'Qu???c gia', 'position' => 'multi_vendor', 'location' => 'vi'],
                    ['code' => 'multi_vendor.address2', 'text' => 'Qu???n/Huy???n', 'position' => 'multi_vendor', 'location' => 'vi'],
                    ['code' => 'multi_vendor.address2', 'text' => 'Address 2', 'position' => 'multi_vendor', 'location' => 'en'],
                    ['code' => 'multi_vendor.address1', 'text' => 'T???nh/Th??nh', 'position' => 'multi_vendor', 'location' => 'vi'],
                    ['code' => 'multi_vendor.address1', 'text' => 'Address 1', 'position' => 'multi_vendor', 'location' => 'en'],
                    ['code' => 'multi_vendor.postcode', 'text' => 'M?? b??u ??i???n', 'position' => 'multi_vendor', 'location' => 'vi'],
                    ['code' => 'multi_vendor.postcode', 'text' => 'Post code', 'position' => 'multi_vendor', 'location' => 'en'],
                    ['code' => 'multi_vendor.phone', 'text' => 'Phone', 'position' => 'multi_vendor', 'location' => 'en'],
                    ['code' => 'multi_vendor.phone', 'text' => '??i???n tho???i', 'position' => 'multi_vendor', 'location' => 'vi'],
                    ['code' => 'multi_vendor.name', 'text' => 'Name', 'position' => 'multi_vendor', 'location' => 'en'],
                    ['code' => 'multi_vendor.name', 'text' => 'T??n', 'position' => 'multi_vendor', 'location' => 'vi'],
                    ['code' => 'multi_vendor.last_name', 'text' => 'H???', 'position' => 'multi_vendor', 'location' => 'vi'],
                    ['code' => 'multi_vendor.last_name', 'text' => 'Last name', 'position' => 'multi_vendor', 'location' => 'en'],
                    ['code' => 'multi_vendor.first_name', 'text' => 'T??n', 'position' => 'multi_vendor', 'location' => 'vi'],
                    ['code' => 'multi_vendor.first_name', 'text' => 'First name', 'position' => 'multi_vendor', 'location' => 'en'],
                    ['code' => 'multi_vendor.email', 'text' => 'Email', 'position' => 'multi_vendor', 'location' => 'vi'],
                    ['code' => 'multi_vendor.email', 'text' => 'Email', 'position' => 'multi_vendor', 'location' => 'en'],
                    ['code' => 'multi_vendor.title_login', 'text' => 'Login account', 'position' => 'multi_vendor', 'location' => 'en'],
                    ['code' => 'multi_vendor.title_login', 'text' => '????ng nh???p', 'position' => 'multi_vendor', 'location' => 'vi'],
                    ['code' => 'multi_vendor.status', 'text' => 'Tr???ng th??i', 'position' => 'multi_vendor', 'location' => 'vi'],
                    ['code' => 'multi_vendor.status', 'text' => 'Status', 'position' => 'multi_vendor', 'location' => 'en'],
                    ['code' => 'multi_vendor.group', 'text' => 'Nh??m', 'position' => 'multi_vendor', 'location' => 'en'],
                    ['code' => 'multi_vendor.group', 'text' => 'Group', 'position' => 'multi_vendor', 'location' => 'vi'],
                    ['code' => 'multi_vendor.admin_login', 'text' => 'Vendor login', 'position' => 'multi_vendor', 'location' => 'en'],
                    ['code' => 'multi_vendor.admin_login', 'text' => 'D??nh cho ng?????i b??n h??ng', 'position' => 'multi_vendor', 'location' => 'vi'],
                    ['code' => 'multi_vendor.admin.keep_password', 'text' => 'Leave it blank if you don\'t change the password', 'position' => 'multi_vendor', 'location' => 'en'],
                    ['code' => 'multi_vendor.admin.keep_password', 'text' => '????? tr???ng n???u kh??ng thay ?????i m???t kh???u', 'position' => 'multi_vendor', 'location' => 'vi'],
                    ['code' => 'multi_vendor.remember_me', 'text' => 'Remember me', 'position' => 'multi_vendor', 'location' => 'en'],
                    ['code' => 'multi_vendor.remember_me', 'text' => 'Ghi nh??? t??i kho???n', 'position' => 'multi_vendor', 'location' => 'vi'],
                    ['code' => 'multi_vendor.login', 'text' => 'Login', 'position' => 'multi_vendor', 'location' => 'en'],
                    ['code' => 'multi_vendor.login', 'text' => '????ng nh???p', 'position' => 'multi_vendor', 'location' => 'vi'],
                    ['code' => 'multi_vendor.top_count_order_vendor', 'text' => 'Top stores with the highest number of orders', 'position' => 'multi_vendor', 'location' => 'en'],
                    ['code' => 'multi_vendor.top_count_order_vendor', 'text' => 'Top c???a h??ng c?? s??? ????n h??ng cao nh???t', 'position' => 'multi_vendor', 'location' => 'vi'],
                    ['code' => 'multi_vendor.export_order_list', 'text' => 'Export order list', 'position' => 'multi_vendor', 'location' => 'en'],
                    ['code' => 'multi_vendor.export_order_list', 'text' => 'Export ????n h??ng ', 'position' => 'multi_vendor', 'location' => 'vi'],
                    ['code' => 'multi_vendor.store_empty', 'text' => 'Select store', 'position' => 'multi_vendor', 'location' => 'en'],
                    ['code' => 'multi_vendor.store_empty', 'text' => 'Ch???n c???a h??ng ', 'position' => 'multi_vendor', 'location' => 'vi'],
                    ['code' => 'multi_vendor.quick_order', 'text' => 'Quick order', 'position' => 'multi_vendor', 'location' => 'en'],
                    ['code' => 'multi_vendor.quick_order', 'text' => '?????t h??ng nhanh', 'position' => 'multi_vendor', 'location' => 'vi'],
                    ['code' => 'multi_vendor.MultiVendor_commission', 'text' => 'Commission rate', 'position' => 'multi_vendor', 'location' => 'en'],
                    ['code' => 'multi_vendor.MultiVendor_commission', 'text' => 'T??? l??? hoa h???ng', 'position' => 'multi_vendor', 'location' => 'vi'],
                    ['code' => 'multi_vendor.MultiVendor_commission_help', 'text' => 'Is the payment rate (%) that the trading floor will keep before paying the vendor', 'position' => 'multi_vendor', 'location' => 'en'],
                    ['code' => 'multi_vendor.MultiVendor_commission_help', 'text' => 'L?? t??? l??? thanh to??n (%) m?? s??n th????ng m???i s??? gi??? l???i tr?????c khi chi tr??? cho nh?? cung c???p', 'position' => 'multi_vendor', 'location' => 'vi'],
                    ['code' => 'multi_vendor.MultiVendor_quick_order', 'text' => 'Quick order', 'position' => 'multi_vendor', 'location' => 'en'],
                    ['code' => 'multi_vendor.MultiVendor_quick_order', 'text' => '?????t h??ng nhanh', 'position' => 'multi_vendor', 'location' => 'vi'],
                    ['code' => 'multi_vendor.MultiVendor_quick_order_help', 'text' => 'The function allows customers to place bulk orders on each vendor\'s booth.', 'position' => 'multi_vendor', 'location' => 'en'],
                    ['code' => 'multi_vendor.MultiVendor_quick_order_help', 'text' => 'Ch???c n??ng cho ph??p kh??ch h??ng ?????t h??ng s??? l?????ng l???n tr??n m???i gian h??ng c???a nh?? cung c???p.', 'position' => 'multi_vendor', 'location' => 'vi'],
                    ['code' => 'multi_vendor.MultiVendor_allow_register', 'text' => 'Allow register vendor', 'position' => 'multi_vendor', 'location' => 'en'],
                    ['code' => 'multi_vendor.MultiVendor_allow_register', 'text' => 'Cho ph??p ????ng k?? vendor', 'position' => 'multi_vendor', 'location' => 'vi'],
                    ['code' => 'multi_vendor.MultiVendor_allow_register_help', 'text' => 'Users are allowed to self-register for a vendor account. If disabled, the vendor account can only be registered through admin.', 'position' => 'multi_vendor', 'location' => 'en'],
                    ['code' => 'multi_vendor.MultiVendor_allow_register_help', 'text' => 'Ng?????i d??ng ???????c ph??p t??? ????ng k?? t??i kho???n vendor. N???u v?? hi???u h??a, t??i kho???n vendor ch??? c?? th??? ????ng k?? th??ng qua admin.', 'position' => 'multi_vendor', 'location' => 'vi'],
                    ['code' => 'multi_vendor.MultiVendor_product_auto_approve', 'text' => 'Auto approve product', 'position' => 'multi_vendor', 'location' => 'en'],
                    ['code' => 'multi_vendor.MultiVendor_product_auto_approve', 'text' => 'T??? ?????ng duy???t s???n ph???m', 'position' => 'multi_vendor', 'location' => 'vi'],
                    ['code' => 'multi_vendor.MultiVendor_product_auto_approve_help', 'text' => 'If this is disabled, the admin must approve all products posted by the seller.', 'position' => 'multi_vendor', 'location' => 'en'],
                    ['code' => 'multi_vendor.MultiVendor_product_auto_approve_help', 'text' => 'N???u t???t ch???c n???ng n??y, admin ph???i ph?? duy???t t???t c??? s???n ph???m ???????c ????ng b???i ng?????i b??n.', 'position' => 'multi_vendor', 'location' => 'vi'],
                    ['code' => 'multi_vendor.MultiVendor_vendor_auto_approve', 'text' => 'Auto approve vendor', 'position' => 'multi_vendor', 'location' => 'en'],
                    ['code' => 'multi_vendor.MultiVendor_vendor_auto_approve', 'text' => 'T??? ?????ng duy???t vendor', 'position' => 'multi_vendor', 'location' => 'vi'],
                    ['code' => 'multi_vendor.MultiVendor_vendor_auto_approve_help', 'text' => 'If this is disabled, the admin must approve all new vendors.', 'position' => 'multi_vendor', 'location' => 'en'],
                    ['code' => 'multi_vendor.MultiVendor_vendor_auto_approve_help', 'text' => 'N???u t???t ch???c n???ng n??y, admin ph???i ph?? duy???t t???t c??? nh?? cung c???p.', 'position' => 'multi_vendor', 'location' => 'vi'],
                    ['code' => 'multi_vendor.MultiVendor_config', 'text' => 'Config markeplace', 'position' => 'multi_vendor', 'location' => 'en'],
                    ['code' => 'multi_vendor.MultiVendor_config', 'text' => 'C???u h??nh ch??? th????ng m???i', 'position' => 'multi_vendor', 'location' => 'vi'],
                    ['code' => 'multi_vendor.MultiVendor_domain_strict', 'text' => 'Strict domain management', 'position' => 'multi_vendor', 'location' => 'en'],
                    ['code' => 'multi_vendor.MultiVendor_domain_strict', 'text' => 'Qu???n l?? nghi??m ng???t t??n mi???n', 'position' => 'multi_vendor', 'location' => 'vi'],
                    ['code' => 'multi_vendor.MultiVendor_domain_strict_help', 'text' => 'Website only works on approved domain names (the domain names of the vendors)', 'position' => 'multi_vendor', 'location' => 'en'],
                    ['code' => 'multi_vendor.MultiVendor_domain_strict_help', 'text' => 'Website ch??? ho???t ?????ng tr??n c??c t??n mi???n ???? ???????c ph?? duy???t (l?? t??n mi???n c???a c??c nh?? cung c???p)', 'position' => 'multi_vendor', 'location' => 'vi'],
                    ['code' => 'multi_vendor.vendor_payment', 'text' => 'Payment', 'position' => 'multi_vendor', 'location' => 'en'],
                    ['code' => 'multi_vendor.vendor_payment', 'text' => 'Thanh to??n', 'position' => 'multi_vendor', 'location' => 'vi'],
                    ['code' => 'multi_vendor.vendor_payment_date', 'text' => 'Payment processing date', 'position' => 'multi_vendor', 'location' => 'en'],
                    ['code' => 'multi_vendor.vendor_payment_date', 'text' => 'Ng??y x??? l?? thanh to??n', 'position' => 'multi_vendor', 'location' => 'vi'],
                    ['code' => 'multi_vendor.vendor_payment_date_help', 'text' => 'The processing date must be less than the current date.<br>Processing only completed orders.', 'position' => 'multi_vendor', 'location' => 'en'],
                    ['code' => 'multi_vendor.vendor_payment_date_help', 'text' => 'Ng??y x??? l?? ph???i nh??? h??n ng??y hi???n t???i.<br>Ch??? x??? l?? ????n h??ng ???? finish.', 'position' => 'multi_vendor', 'location' => 'vi'],
                    ['code' => 'multi_vendor.vendor_payment_button', 'text' => 'Process', 'position' => 'multi_vendor', 'location' => 'en'],
                    ['code' => 'multi_vendor.vendor_payment_button', 'text' => 'X??? l??', 'position' => 'multi_vendor', 'location' => 'vi'],
                    ['code' => 'multi_vendor.vendor_payment_date_validate', 'text' => 'Time must be less than current date', 'position' => 'multi_vendor', 'location' => 'en'],
                    ['code' => 'multi_vendor.vendor_payment_date_validate', 'text' => 'Th???i gian ph???i nh??? h??n ng??y hi???n t???i', 'position' => 'multi_vendor', 'location' => 'vi'],
                    ['code' => 'multi_vendor.vendor_payment_date_exist', 'text' => 'The date you selected has already been processed', 'position' => 'multi_vendor', 'location' => 'en'],
                    ['code' => 'multi_vendor.vendor_payment_date_exist', 'text' => 'Ng??y b???n ch???n ???? x??? l?? r???i', 'position' => 'multi_vendor', 'location' => 'vi'],
                    ['code' => 'multi_vendor.payment.total_sum', 'text' => 'Total sum', 'position' => 'multi_vendor', 'location' => 'en'],
                    ['code' => 'multi_vendor.payment.total_sum', 'text' => 'T???ng gi?? tr???', 'position' => 'multi_vendor', 'location' => 'vi'],
                    ['code' => 'multi_vendor.payment.amount', 'text' => 'Amount', 'position' => 'multi_vendor', 'location' => 'en'],
                    ['code' => 'multi_vendor.payment.amount', 'text' => 'Thanh to??n', 'position' => 'multi_vendor', 'location' => 'vi'],
                    ['code' => 'multi_vendor.payment.order_count', 'text' => 'Orders', 'position' => 'multi_vendor', 'location' => 'en'],
                    ['code' => 'multi_vendor.payment.order_count', 'text' => '????n h??ng', 'position' => 'multi_vendor', 'location' => 'vi'],
                    ['code' => 'multi_vendor.payment.currency', 'text' => 'Currency', 'position' => 'multi_vendor', 'location' => 'en'],
                    ['code' => 'multi_vendor.payment.currency', 'text' => 'Ti???n t???', 'position' => 'multi_vendor', 'location' => 'vi'],
                    ['code' => 'multi_vendor.payment.content', 'text' => 'Content', 'position' => 'multi_vendor', 'location' => 'en'],
                    ['code' => 'multi_vendor.payment.content', 'text' => 'N???i dung', 'position' => 'multi_vendor', 'location' => 'vi'],
                    ['code' => 'multi_vendor.payment.comment', 'text' => 'Comment', 'position' => 'multi_vendor', 'location' => 'en'],
                    ['code' => 'multi_vendor.payment.comment', 'text' => 'B??nh lu???n', 'position' => 'multi_vendor', 'location' => 'vi'],
                    ['code' => 'multi_vendor.payment.date_process', 'text' => 'Date process', 'position' => 'multi_vendor', 'location' => 'en'],
                    ['code' => 'multi_vendor.payment.date_process', 'text' => 'Ng??y x??? l??', 'position' => 'multi_vendor', 'location' => 'vi'],
                    ['code' => 'multi_vendor.payment.date_pay', 'text' => 'Date pay', 'position' => 'multi_vendor', 'location' => 'en'],
                    ['code' => 'multi_vendor.payment.date_pay', 'text' => 'Ng??y thanh to??n', 'position' => 'multi_vendor', 'location' => 'vi'],
                    ['code' => 'multi_vendor.payment.status', 'text' => 'Status', 'position' => 'multi_vendor', 'location' => 'en'],
                    ['code' => 'multi_vendor.payment.status', 'text' => 'Tr???ng th??i', 'position' => 'multi_vendor', 'location' => 'vi'],
                    ['code' => 'multi_vendor.payment.created_at', 'text' => 'Created at', 'position' => 'multi_vendor', 'location' => 'en'],
                    ['code' => 'multi_vendor.payment.created_at', 'text' => 'T???o l??c', 'position' => 'multi_vendor', 'location' => 'vi'],
                    ['code' => 'multi_vendor.payment.life_time', 'text' => 'Lifetime sales', 'position' => 'multi_vendor', 'location' => 'en'],
                    ['code' => 'multi_vendor.payment.life_time', 'text' => '???? b??n', 'position' => 'multi_vendor', 'location' => 'vi'],
                    ['code' => 'multi_vendor.payment.payout', 'text' => 'Payout', 'position' => 'multi_vendor', 'location' => 'en'],
                    ['code' => 'multi_vendor.payment.payout', 'text' => '???? nh???n', 'position' => 'multi_vendor', 'location' => 'vi'],
                    ['code' => 'multi_vendor.payment.remaining', 'text' => 'Remaining', 'position' => 'multi_vendor', 'location' => 'en'],
                    ['code' => 'multi_vendor.payment.remaining', 'text' => 'C??n l???i', 'position' => 'multi_vendor', 'location' => 'vi'],
                ];

                Languages::insertOrIgnore(
                    $dataLang
                );

                if (!$process) {
                    $return = ['error' => 1, 'msg' => sc_language_render('plugin.plugin_action.install_faild')];
                } else {
                    $return = (new PluginModel)->installExtension();
                    if (is_writable(base_path('resources/views/templates/'.sc_store('template')))) {
                        File::copyDirectory(app_path($this->configGroup.'/'.$this->configCode.'/'.$this->configKey.'/template/block'), base_path('resources/views/templates/'.sc_store('template').'/block'));
                        File::copyDirectory(app_path($this->configGroup.'/'.$this->configCode.'/'.$this->configKey.'/template/vendor'), base_path('resources/views/templates/'.sc_store('template').'/vendor'));
                    }
                }

            } catch(\Throwable $e) {
                $this->uninstall();
                $return = ['error' => 1, 'msg' => $e->getMessage()];
            }

        }

        return $return;
    }

    public function uninstall()
    {
        $return = ['error' => 0, 'msg' => ''];
        
        //Please delete all values inserted in the installation step
        AdminConfig::where('key', $this->configKey)->delete();
        AdminConfig::where('code', $this->configKey.'_config')->delete();

        //Delete menu
        $blockMenu = AdminMenu::where('key', 'ADMIN_MVENDOR_SETTING')->first();
        AdminMenu::where('parent_id', $blockMenu->id)->delete();
        AdminMenu::where('id', $blockMenu->id)->delete();

        //Language
        Languages::where('position', 'multi_vendor')->delete();

        //Default
        (new PluginModel)->uninstallExtension();

        return $return;
    }
    
    public function enable()
    {
        $return = ['error' => 0, 'msg' => ''];
        $process = (new AdminConfig)->where('key', $this->configKey)->update(['value' => self::ON]);
        if (!$process) {
            $return = ['error' => 1, 'msg' => 'Error enable'];
        }
        return $return;
    }

    public function disable()
    {
        $return = ['error' => 0, 'msg' => ''];
        $process = (new AdminConfig)
            ->where('key', $this->configKey)
            ->update(['value' => self::OFF]);
        if (!$process) {
            $return = ['error' => 1, 'msg' => 'Error disable'];
        }
        return $return;
    }

    public function config()
    {
        return redirect(sc_route_admin('admin_MultiVendor.index'));
    }

    public function getData()
    {
        $arrData = [
            'title'      => $this->title,
            'code'       => $this->configCode,
            'key'        => $this->configKey,
            'image'      => $this->image,
            'permission' => self::ALLOW,
            'version'    => $this->version,
            'auth'       => $this->auth,
            'link'       => $this->link,
            'value'      => 0, // this return need for plugin shipping
            'pathPlugin' => $this->pathPlugin
        ];

        return $arrData;
    }

    /**
     * Process after order success
     *
     * @param   [array]  $data  
     *
     */
    public function endApp($data = []) {
        //action after end app
    }
}
