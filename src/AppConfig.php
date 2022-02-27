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
                    ['code' => 'multi_vendor.plugin_block', 'text' => 'CHỢ BÁN HÀNG', 'position' => 'multi_vendor', 'location' => 'vi'],
                    ['code' => 'multi_vendor.account_inactive_title', 'text' => 'Access denied!', 'position' => 'multi_vendor', 'location' => 'en'],
                    ['code' => 'multi_vendor.account_inactive_title', 'text' => 'Truy cập bị từ chối', 'position' => 'multi_vendor', 'location' => 'vi'],
                    ['code' => 'multi_vendor.account_inactive_msg', 'text' => 'The account has not been activated or has been locked!', 'position' => 'multi_vendor', 'location' => 'en'],
                    ['code' => 'multi_vendor.account_inactive_msg', 'text' => 'Tài khoản chưa được kích hoạt hoặc đã bị khóa!', 'position' => 'multi_vendor', 'location' => 'vi'],
                    ['code' => 'multi_vendor.update_info_store_msg', 'text' => 'Please update your store information!', 'position' => 'multi_vendor', 'location' => 'en'],
                    ['code' => 'multi_vendor.update_info_store_msg', 'text' => 'Vui lòng cập nhật thông tin cửa hàng của bạn!', 'position' => 'multi_vendor', 'location' => 'vi'],
                    ['code' => 'multi_vendor.update_info_store_title', 'text' => 'Update store information', 'position' => 'multi_vendor', 'location' => 'en'],
                    ['code' => 'multi_vendor.update_info_store_title', 'text' => 'Cập nhật thông tin cửa hàng', 'position' => 'multi_vendor', 'location' => 'vi'],
                    ['code' => 'multi_vendor.vendor_store', 'text' => 'Vendor store', 'position' => 'multi_vendor', 'location' => 'en'],
                    ['code' => 'multi_vendor.vendor_store', 'text' => 'Gian hàng người bán', 'position' => 'multi_vendor', 'location' => 'vi'],
                    ['code' => 'multi_vendor.vendor_user', 'text' => 'Vendor user', 'position' => 'multi_vendor', 'location' => 'en'],
                    ['code' => 'multi_vendor.vendor_user', 'text' => 'Tài khoản người bán', 'position' => 'multi_vendor', 'location' => 'vi'],
                    ['code' => 'multi_vendor.vendor_config', 'text' => 'Config', 'position' => 'multi_vendor', 'location' => 'en'],
                    ['code' => 'multi_vendor.vendor_config', 'text' => 'Cấu hình', 'position' => 'multi_vendor', 'location' => 'vi'],
                    ['code' => 'multi_vendor.vendor_report', 'text' => 'Report', 'position' => 'multi_vendor', 'location' => 'en'],
                    ['code' => 'multi_vendor.vendor_report', 'text' => 'Báo cáo', 'position' => 'multi_vendor', 'location' => 'vi'],
                    ['code' => 'multi_vendor.vendor_add', 'text' => 'Add new vendor', 'position' => 'multi_vendor', 'location' => 'en'],
                    ['code' => 'multi_vendor.vendor_add', 'text' => 'Thêm người bán', 'position' => 'multi_vendor', 'location' => 'vi'],
                    ['code' => 'multi_vendor.config', 'text' => 'Config information', 'position' => 'multi_vendor', 'location' => 'en'],
                    ['code' => 'multi_vendor.config', 'text' => 'Thông tin cấu hình', 'position' => 'multi_vendor', 'location' => 'vi'],
                    ['code' => 'multi_vendor.vendor_search_place', 'text' => 'Search name, email vendor', 'position' => 'multi_vendor', 'location' => 'en'],
                    ['code' => 'multi_vendor.vendor_search_place', 'text' => 'Tìm kiếm tên, email', 'position' => 'multi_vendor', 'location' => 'vi'],
                    ['code' => 'multi_vendor.password', 'text' => 'Password', 'position' => 'multi_vendor', 'location' => 'en'],
                    ['code' => 'multi_vendor.password', 'text' => 'Mật khẩu', 'position' => 'multi_vendor', 'location' => 'vi'],
                    ['code' => 'multi_vendor.password_forgot', 'text' => 'Forgot password', 'position' => 'multi_vendor', 'location' => 'en'],
                    ['code' => 'multi_vendor.password_forgot', 'text' => 'Quên mật khẩu', 'position' => 'multi_vendor', 'location' => 'vi'],
                    ['code' => 'multi_vendor.login_title', 'text' => 'Login page', 'position' => 'multi_vendor', 'location' => 'en'],
                    ['code' => 'multi_vendor.login_title', 'text' => 'Trang đăng nhập', 'position' => 'multi_vendor', 'location' => 'vi'],
                    ['code' => 'multi_vendor.register_success', 'text' => 'Successful register', 'position' => 'multi_vendor', 'location' => 'en'],
                    ['code' => 'multi_vendor.register_success', 'text' => 'Đăng ký thành công', 'position' => 'multi_vendor', 'location' => 'vi'],
                    ['code' => 'multi_vendor.signup', 'text' => 'Signup', 'position' => 'multi_vendor', 'location' => 'en'],
                    ['code' => 'multi_vendor.signup', 'text' => 'Đăng ký', 'position' => 'multi_vendor', 'location' => 'vi'],
                    ['code' => 'multi_vendor.title_register', 'text' => 'Account register', 'position' => 'multi_vendor', 'location' => 'en'],
                    ['code' => 'multi_vendor.title_register', 'text' => 'Đăng ký tài khoản', 'position' => 'multi_vendor', 'location' => 'vi'],
                    ['code' => 'multi_vendor.password_reset', 'text' => 'Password reset', 'position' => 'multi_vendor', 'location' => 'en'],
                    ['code' => 'multi_vendor.password_reset', 'text' => 'Reset mật khẩu', 'position' => 'multi_vendor', 'location' => 'vi'],
                    ['code' => 'multi_vendor.password_confirm', 'text' => 'Password confirm', 'position' => 'multi_vendor', 'location' => 'en'],
                    ['code' => 'multi_vendor.password_confirm', 'text' => 'Xác nhận mật khẩu', 'position' => 'multi_vendor', 'location' => 'vi'],
                    ['code' => 'multi_vendor.phone_regex', 'text' => 'The phone format is not correct. Length 8-14, use only 0-9 and the "-" SIGN.', 'position' => 'multi_vendor', 'location' => 'en'],
                    ['code' => 'multi_vendor.phone_regex', 'text' => 'Số điện thoại định dạng không đúng. Chiều dài 8-14, chỉ sử dụng số 0-9 và "-"', 'position' => 'multi_vendor', 'location' => 'vi'],
                    ['code' => 'multi_vendor.country', 'text' => 'Country', 'position' => 'multi_vendor', 'location' => 'en'],
                    ['code' => 'multi_vendor.country', 'text' => 'Quốc gia', 'position' => 'multi_vendor', 'location' => 'vi'],
                    ['code' => 'multi_vendor.address2', 'text' => 'Quận/Huyện', 'position' => 'multi_vendor', 'location' => 'vi'],
                    ['code' => 'multi_vendor.address2', 'text' => 'Address 2', 'position' => 'multi_vendor', 'location' => 'en'],
                    ['code' => 'multi_vendor.address1', 'text' => 'Tỉnh/Thành', 'position' => 'multi_vendor', 'location' => 'vi'],
                    ['code' => 'multi_vendor.address1', 'text' => 'Address 1', 'position' => 'multi_vendor', 'location' => 'en'],
                    ['code' => 'multi_vendor.postcode', 'text' => 'Mã bưu điện', 'position' => 'multi_vendor', 'location' => 'vi'],
                    ['code' => 'multi_vendor.postcode', 'text' => 'Post code', 'position' => 'multi_vendor', 'location' => 'en'],
                    ['code' => 'multi_vendor.phone', 'text' => 'Phone', 'position' => 'multi_vendor', 'location' => 'en'],
                    ['code' => 'multi_vendor.phone', 'text' => 'Điện thoại', 'position' => 'multi_vendor', 'location' => 'vi'],
                    ['code' => 'multi_vendor.name', 'text' => 'Name', 'position' => 'multi_vendor', 'location' => 'en'],
                    ['code' => 'multi_vendor.name', 'text' => 'Tên', 'position' => 'multi_vendor', 'location' => 'vi'],
                    ['code' => 'multi_vendor.last_name', 'text' => 'Họ', 'position' => 'multi_vendor', 'location' => 'vi'],
                    ['code' => 'multi_vendor.last_name', 'text' => 'Last name', 'position' => 'multi_vendor', 'location' => 'en'],
                    ['code' => 'multi_vendor.first_name', 'text' => 'Tên', 'position' => 'multi_vendor', 'location' => 'vi'],
                    ['code' => 'multi_vendor.first_name', 'text' => 'First name', 'position' => 'multi_vendor', 'location' => 'en'],
                    ['code' => 'multi_vendor.email', 'text' => 'Email', 'position' => 'multi_vendor', 'location' => 'vi'],
                    ['code' => 'multi_vendor.email', 'text' => 'Email', 'position' => 'multi_vendor', 'location' => 'en'],
                    ['code' => 'multi_vendor.title_login', 'text' => 'Login account', 'position' => 'multi_vendor', 'location' => 'en'],
                    ['code' => 'multi_vendor.title_login', 'text' => 'Đăng nhập', 'position' => 'multi_vendor', 'location' => 'vi'],
                    ['code' => 'multi_vendor.status', 'text' => 'Trạng thái', 'position' => 'multi_vendor', 'location' => 'vi'],
                    ['code' => 'multi_vendor.status', 'text' => 'Status', 'position' => 'multi_vendor', 'location' => 'en'],
                    ['code' => 'multi_vendor.group', 'text' => 'Nhóm', 'position' => 'multi_vendor', 'location' => 'en'],
                    ['code' => 'multi_vendor.group', 'text' => 'Group', 'position' => 'multi_vendor', 'location' => 'vi'],
                    ['code' => 'multi_vendor.admin_login', 'text' => 'Vendor login', 'position' => 'multi_vendor', 'location' => 'en'],
                    ['code' => 'multi_vendor.admin_login', 'text' => 'Dành cho người bán hàng', 'position' => 'multi_vendor', 'location' => 'vi'],
                    ['code' => 'multi_vendor.admin.keep_password', 'text' => 'Leave it blank if you don\'t change the password', 'position' => 'multi_vendor', 'location' => 'en'],
                    ['code' => 'multi_vendor.admin.keep_password', 'text' => 'Để trống nếu không thay đổi mật khẩu', 'position' => 'multi_vendor', 'location' => 'vi'],
                    ['code' => 'multi_vendor.remember_me', 'text' => 'Remember me', 'position' => 'multi_vendor', 'location' => 'en'],
                    ['code' => 'multi_vendor.remember_me', 'text' => 'Ghi nhớ tài khoản', 'position' => 'multi_vendor', 'location' => 'vi'],
                    ['code' => 'multi_vendor.login', 'text' => 'Login', 'position' => 'multi_vendor', 'location' => 'en'],
                    ['code' => 'multi_vendor.login', 'text' => 'Đăng nhập', 'position' => 'multi_vendor', 'location' => 'vi'],
                    ['code' => 'multi_vendor.top_count_order_vendor', 'text' => 'Top stores with the highest number of orders', 'position' => 'multi_vendor', 'location' => 'en'],
                    ['code' => 'multi_vendor.top_count_order_vendor', 'text' => 'Top cửa hàng có số đơn hàng cao nhất', 'position' => 'multi_vendor', 'location' => 'vi'],
                    ['code' => 'multi_vendor.export_order_list', 'text' => 'Export order list', 'position' => 'multi_vendor', 'location' => 'en'],
                    ['code' => 'multi_vendor.export_order_list', 'text' => 'Export đơn hàng ', 'position' => 'multi_vendor', 'location' => 'vi'],
                    ['code' => 'multi_vendor.store_empty', 'text' => 'Select store', 'position' => 'multi_vendor', 'location' => 'en'],
                    ['code' => 'multi_vendor.store_empty', 'text' => 'Chọn cửa hàng ', 'position' => 'multi_vendor', 'location' => 'vi'],
                    ['code' => 'multi_vendor.quick_order', 'text' => 'Quick order', 'position' => 'multi_vendor', 'location' => 'en'],
                    ['code' => 'multi_vendor.quick_order', 'text' => 'Đặt hàng nhanh', 'position' => 'multi_vendor', 'location' => 'vi'],
                    ['code' => 'multi_vendor.MultiVendor_commission', 'text' => 'Commission rate', 'position' => 'multi_vendor', 'location' => 'en'],
                    ['code' => 'multi_vendor.MultiVendor_commission', 'text' => 'Tỷ lệ hoa hồng', 'position' => 'multi_vendor', 'location' => 'vi'],
                    ['code' => 'multi_vendor.MultiVendor_commission_help', 'text' => 'Is the payment rate (%) that the trading floor will keep before paying the vendor', 'position' => 'multi_vendor', 'location' => 'en'],
                    ['code' => 'multi_vendor.MultiVendor_commission_help', 'text' => 'Là tỉ lệ thanh toán (%) mà sàn thương mại sẽ giữ lại trước khi chi trả cho nhà cung cấp', 'position' => 'multi_vendor', 'location' => 'vi'],
                    ['code' => 'multi_vendor.MultiVendor_quick_order', 'text' => 'Quick order', 'position' => 'multi_vendor', 'location' => 'en'],
                    ['code' => 'multi_vendor.MultiVendor_quick_order', 'text' => 'Đặt hàng nhanh', 'position' => 'multi_vendor', 'location' => 'vi'],
                    ['code' => 'multi_vendor.MultiVendor_quick_order_help', 'text' => 'The function allows customers to place bulk orders on each vendor\'s booth.', 'position' => 'multi_vendor', 'location' => 'en'],
                    ['code' => 'multi_vendor.MultiVendor_quick_order_help', 'text' => 'Chức năng cho phép khách hàng đặt hàng số lượng lớn trên mỗi gian hàng của nhà cung cấp.', 'position' => 'multi_vendor', 'location' => 'vi'],
                    ['code' => 'multi_vendor.MultiVendor_allow_register', 'text' => 'Allow register vendor', 'position' => 'multi_vendor', 'location' => 'en'],
                    ['code' => 'multi_vendor.MultiVendor_allow_register', 'text' => 'Cho phép đăng ký vendor', 'position' => 'multi_vendor', 'location' => 'vi'],
                    ['code' => 'multi_vendor.MultiVendor_allow_register_help', 'text' => 'Users are allowed to self-register for a vendor account. If disabled, the vendor account can only be registered through admin.', 'position' => 'multi_vendor', 'location' => 'en'],
                    ['code' => 'multi_vendor.MultiVendor_allow_register_help', 'text' => 'Người dùng được phép tự đăng ký tài khoản vendor. Nếu vô hiệu hóa, tài khoản vendor chỉ có thể đăng ký thông qua admin.', 'position' => 'multi_vendor', 'location' => 'vi'],
                    ['code' => 'multi_vendor.MultiVendor_product_auto_approve', 'text' => 'Auto approve product', 'position' => 'multi_vendor', 'location' => 'en'],
                    ['code' => 'multi_vendor.MultiVendor_product_auto_approve', 'text' => 'Tự động duyệt sản phẩm', 'position' => 'multi_vendor', 'location' => 'vi'],
                    ['code' => 'multi_vendor.MultiVendor_product_auto_approve_help', 'text' => 'If this is disabled, the admin must approve all products posted by the seller.', 'position' => 'multi_vendor', 'location' => 'en'],
                    ['code' => 'multi_vendor.MultiVendor_product_auto_approve_help', 'text' => 'Nếu tắt chức nằng này, admin phải phê duyệt tất cả sản phẩm được đăng bởi người bán.', 'position' => 'multi_vendor', 'location' => 'vi'],
                    ['code' => 'multi_vendor.MultiVendor_vendor_auto_approve', 'text' => 'Auto approve vendor', 'position' => 'multi_vendor', 'location' => 'en'],
                    ['code' => 'multi_vendor.MultiVendor_vendor_auto_approve', 'text' => 'Tự động duyệt vendor', 'position' => 'multi_vendor', 'location' => 'vi'],
                    ['code' => 'multi_vendor.MultiVendor_vendor_auto_approve_help', 'text' => 'If this is disabled, the admin must approve all new vendors.', 'position' => 'multi_vendor', 'location' => 'en'],
                    ['code' => 'multi_vendor.MultiVendor_vendor_auto_approve_help', 'text' => 'Nếu tắt chức nằng này, admin phải phê duyệt tất cả nhà cung cấp.', 'position' => 'multi_vendor', 'location' => 'vi'],
                    ['code' => 'multi_vendor.MultiVendor_config', 'text' => 'Config markeplace', 'position' => 'multi_vendor', 'location' => 'en'],
                    ['code' => 'multi_vendor.MultiVendor_config', 'text' => 'Cấu hình chợ thương mại', 'position' => 'multi_vendor', 'location' => 'vi'],
                    ['code' => 'multi_vendor.MultiVendor_domain_strict', 'text' => 'Strict domain management', 'position' => 'multi_vendor', 'location' => 'en'],
                    ['code' => 'multi_vendor.MultiVendor_domain_strict', 'text' => 'Quản lý nghiêm ngặt tên miền', 'position' => 'multi_vendor', 'location' => 'vi'],
                    ['code' => 'multi_vendor.MultiVendor_domain_strict_help', 'text' => 'Website only works on approved domain names (the domain names of the vendors)', 'position' => 'multi_vendor', 'location' => 'en'],
                    ['code' => 'multi_vendor.MultiVendor_domain_strict_help', 'text' => 'Website chỉ hoạt động trên các tên miền đã được phê duyệt (là tên miền của các nhà cung cấp)', 'position' => 'multi_vendor', 'location' => 'vi'],
                    ['code' => 'multi_vendor.vendor_payment', 'text' => 'Payment', 'position' => 'multi_vendor', 'location' => 'en'],
                    ['code' => 'multi_vendor.vendor_payment', 'text' => 'Thanh toán', 'position' => 'multi_vendor', 'location' => 'vi'],
                    ['code' => 'multi_vendor.vendor_payment_date', 'text' => 'Payment processing date', 'position' => 'multi_vendor', 'location' => 'en'],
                    ['code' => 'multi_vendor.vendor_payment_date', 'text' => 'Ngày xử lý thanh toán', 'position' => 'multi_vendor', 'location' => 'vi'],
                    ['code' => 'multi_vendor.vendor_payment_date_help', 'text' => 'The processing date must be less than the current date.<br>Processing only completed orders.', 'position' => 'multi_vendor', 'location' => 'en'],
                    ['code' => 'multi_vendor.vendor_payment_date_help', 'text' => 'Ngày xử lý phải nhỏ hơn ngày hiện tại.<br>Chỉ xử lý đơn hàng đã finish.', 'position' => 'multi_vendor', 'location' => 'vi'],
                    ['code' => 'multi_vendor.vendor_payment_button', 'text' => 'Process', 'position' => 'multi_vendor', 'location' => 'en'],
                    ['code' => 'multi_vendor.vendor_payment_button', 'text' => 'Xử lý', 'position' => 'multi_vendor', 'location' => 'vi'],
                    ['code' => 'multi_vendor.vendor_payment_date_validate', 'text' => 'Time must be less than current date', 'position' => 'multi_vendor', 'location' => 'en'],
                    ['code' => 'multi_vendor.vendor_payment_date_validate', 'text' => 'Thời gian phải nhỏ hơn ngày hiện tại', 'position' => 'multi_vendor', 'location' => 'vi'],
                    ['code' => 'multi_vendor.vendor_payment_date_exist', 'text' => 'The date you selected has already been processed', 'position' => 'multi_vendor', 'location' => 'en'],
                    ['code' => 'multi_vendor.vendor_payment_date_exist', 'text' => 'Ngày bạn chọn đã xử lý rồi', 'position' => 'multi_vendor', 'location' => 'vi'],
                    ['code' => 'multi_vendor.payment.total_sum', 'text' => 'Total sum', 'position' => 'multi_vendor', 'location' => 'en'],
                    ['code' => 'multi_vendor.payment.total_sum', 'text' => 'Tổng giá trị', 'position' => 'multi_vendor', 'location' => 'vi'],
                    ['code' => 'multi_vendor.payment.amount', 'text' => 'Amount', 'position' => 'multi_vendor', 'location' => 'en'],
                    ['code' => 'multi_vendor.payment.amount', 'text' => 'Thanh toán', 'position' => 'multi_vendor', 'location' => 'vi'],
                    ['code' => 'multi_vendor.payment.order_count', 'text' => 'Orders', 'position' => 'multi_vendor', 'location' => 'en'],
                    ['code' => 'multi_vendor.payment.order_count', 'text' => 'Đơn hàng', 'position' => 'multi_vendor', 'location' => 'vi'],
                    ['code' => 'multi_vendor.payment.currency', 'text' => 'Currency', 'position' => 'multi_vendor', 'location' => 'en'],
                    ['code' => 'multi_vendor.payment.currency', 'text' => 'Tiền tệ', 'position' => 'multi_vendor', 'location' => 'vi'],
                    ['code' => 'multi_vendor.payment.content', 'text' => 'Content', 'position' => 'multi_vendor', 'location' => 'en'],
                    ['code' => 'multi_vendor.payment.content', 'text' => 'Nội dung', 'position' => 'multi_vendor', 'location' => 'vi'],
                    ['code' => 'multi_vendor.payment.comment', 'text' => 'Comment', 'position' => 'multi_vendor', 'location' => 'en'],
                    ['code' => 'multi_vendor.payment.comment', 'text' => 'Bình luận', 'position' => 'multi_vendor', 'location' => 'vi'],
                    ['code' => 'multi_vendor.payment.date_process', 'text' => 'Date process', 'position' => 'multi_vendor', 'location' => 'en'],
                    ['code' => 'multi_vendor.payment.date_process', 'text' => 'Ngày xử lý', 'position' => 'multi_vendor', 'location' => 'vi'],
                    ['code' => 'multi_vendor.payment.date_pay', 'text' => 'Date pay', 'position' => 'multi_vendor', 'location' => 'en'],
                    ['code' => 'multi_vendor.payment.date_pay', 'text' => 'Ngày thanh toán', 'position' => 'multi_vendor', 'location' => 'vi'],
                    ['code' => 'multi_vendor.payment.status', 'text' => 'Status', 'position' => 'multi_vendor', 'location' => 'en'],
                    ['code' => 'multi_vendor.payment.status', 'text' => 'Trạng thái', 'position' => 'multi_vendor', 'location' => 'vi'],
                    ['code' => 'multi_vendor.payment.created_at', 'text' => 'Created at', 'position' => 'multi_vendor', 'location' => 'en'],
                    ['code' => 'multi_vendor.payment.created_at', 'text' => 'Tạo lúc', 'position' => 'multi_vendor', 'location' => 'vi'],
                    ['code' => 'multi_vendor.payment.life_time', 'text' => 'Lifetime sales', 'position' => 'multi_vendor', 'location' => 'en'],
                    ['code' => 'multi_vendor.payment.life_time', 'text' => 'Đã bán', 'position' => 'multi_vendor', 'location' => 'vi'],
                    ['code' => 'multi_vendor.payment.payout', 'text' => 'Payout', 'position' => 'multi_vendor', 'location' => 'en'],
                    ['code' => 'multi_vendor.payment.payout', 'text' => 'Đã nhận', 'position' => 'multi_vendor', 'location' => 'vi'],
                    ['code' => 'multi_vendor.payment.remaining', 'text' => 'Remaining', 'position' => 'multi_vendor', 'location' => 'en'],
                    ['code' => 'multi_vendor.payment.remaining', 'text' => 'Còn lại', 'position' => 'multi_vendor', 'location' => 'vi'],
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
