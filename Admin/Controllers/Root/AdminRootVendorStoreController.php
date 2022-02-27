<?php
namespace App\Plugins\Other\MultiVendor\Admin\Controllers\Root;

use SCart\Core\Admin\Models\AdminStore;
use SCart\Core\Front\Models\ShopLanguage;
use SCart\Core\Front\Models\ShopCurrency;
use SCart\Core\Front\Models\ShopTax;
use SCart\Core\Admin\Models\AdminConfig;
use SCart\Core\Admin\Models\AdminTemplate;
use SCart\Core\Front\Models\ShopLink;
use SCart\Core\Front\Models\ShopLinkStore;
use Validator;
use Illuminate\Support\Facades\Artisan;
use DB;
use App\Plugins\Other\MultiVendor\Admin\Controllers\RootAdminVendorController;

class AdminRootVendorStoreController extends RootAdminVendorController
{
    public $templates, $currencies, $languages;

    public function __construct()
    {
        parent::__construct();
        $this->templates = (new AdminTemplate)->getListTemplateActive();
        $this->currencies = ShopCurrency::getCodeActive();
        $this->languages = ShopLanguage::getListActive();
    }
    
    public function index()
    {
        $data = [
            'title' => sc_language_render('store.admin.list'),
            'subTitle' => sc_language_render($this->plugin->pathPlugin.'::lang.admin.help'),
            'icon' => 'fa fa-indent',        
        ];
        $stories = AdminStore::with('descriptions')
            ->get()
            ->keyBy('id');
        $data['stories'] = $stories;
        $data['templates'] = $this->templates;
        $data['languages'] = $this->languages;
        $data['currencies'] =$this->currencies;
        $data['pathPlugin'] =$this->plugin->pathPlugin;

        $data['urlDeleteItem'] = sc_route('admin_MultiVendor.delete');
        return view($this->plugin->pathPlugin.'::Admin.screen.root.store_list')
            ->with($data);
    }


    /**
     * Form create new store in admin
     * @return [type] [description]
     */
    public function create()
    {
        $data = [
            'title' => sc_language_render('store.admin.add_new_title'),
            'subTitle' => '',
            'title_description' => sc_language_render('store.admin.add_new_des'),
            'icon' => 'fa fa-plus',
            'store' => [],
            'languages' => $this->languages,
            'url_action' => sc_route('admin_MultiVendor.create'),
            'templates' => $this->templates
        ];

        $data['currencies'] =$this->currencies;

        return view($this->plugin->pathPlugin.'::Admin.screen.root.store_add')
            ->with($data);
    }

    /*
    * Post create new order in admin
    * @return [type] [description]
    */
    public function postCreate()
    {
        $data = request()->all();
        $data = sc_clean($data, [], true);
        $data['domain'] = sc_process_domain_store($data['domain'] ?? '');
        $data['code'] = sc_word_limit(sc_word_format_url($data['code']), 20);
        $validator = Validator::make($data, [
            'descriptions.*.title' => 'required|string|max:200',
            'descriptions.*.keyword' => 'nullable|string|max:200',
            'descriptions.*.description' => 'nullable|string|max:300',
            'code'     => 'required|string|max:20|unique:"'.AdminStore::class.'",code',
            'template' => 'required',
            ], [
                'descriptions.*.title.required' => sc_language_render('validation.required', ['attribute' => sc_language_render('store.title')]),
                'descriptions.*.keyword.required' => sc_language_render('validation.required', ['attribute' => sc_language_render('store.keyword')]),
                'descriptions.*.description.required' => sc_language_render('validation.required', ['attribute' => sc_language_render('store.description')]),
            ]
        );
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput($data);
        }

        $dataInsert = [
            'logo'        => $data['logo'],
            'phone'       => $data['phone'],
            'long_phone'  => $data['long_phone'],
            'email'       => $data['email'],
            'time_active' => $data['time_active'],
            'address'     => $data['address'],
            'office'      => $data['office'],
            'language'    => $data['language'] ?? '',
            'currency'    => $data['currency'] ?? '',
            'template'    => $data['template'],
            'domain'      => $data['domain'] ?? '',
            'code'        => $data['code'],
            'status'      => empty($data['status']) ? 0 : 1,
        ];
        $dataInsert = sc_clean($dataInsert, [], true);
        try {
        //Create new store
        DB::connection(SC_CONNECTION)
            ->transaction(function () use($dataInsert, $data) {
                $store = AdminStore::create($dataInsert);
                $storeId = $store->id;
                $dataDes = [];
                $languages = ShopLanguage::getListActive();
                foreach ($languages as $code => $value) {
                    $dataDes[] = [
                        'store_id'    => $storeId,
                        'lang'        => $code,
                        'title'       => $data['descriptions'][$code]['title'],
                        'keyword'     => $data['descriptions'][$code]['keyword'],
                        'description' => $data['descriptions'][$code]['description'],
                        'maintain_content' => '<center><img src="/images/maintenance.png" />
                        <h3><span style="color:#e74c3c;"><strong>Sorry! We are currently doing site maintenance!</strong></span></h3>
                        </center>'
                    ];
                }
                $dataDes = sc_clean($dataDes, [], true);
                AdminStore::insertDescription($dataDes);

                //Add config default for new store
                session(['lastStoreId' => $storeId]);
                Artisan::call('db:seed', [
                    '--class' => 'DataStoreSeeder',
                    '--force' => true
                ]);
                session()->forget('lastStoreId');


                //Install template for store
                if (file_exists($fileProcess = resource_path() . '/views/templates/'.$store->template.'/Provider.php')) {
                    include_once $fileProcess;
                    if (function_exists('sc_template_install_store')) {
                        //Insert only specify store
                        sc_template_install_store($storeId);
                    }
                }


                
            }, 2);
        }catch(\Throwable $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
        return redirect()->route('admin_MultiVendor.index')->with('success', sc_language_render('action.create_success'));

    }

    public function config($id) {
        if ($id == SC_ID_ROOT) {
            return redirect()->route('admin_store.index');
        }
        $store = AdminStore::find($id);
        if (!$store) {
            $data = [
                'title' => sc_language_render('store.admin.config_store', ['id' => $id]),
                'subTitle' => '',
                'icon' => 'fas fa-cogs',
                'dataNotFound' => 1       
            ];
            return view($this->templatePathAdmin.'screen.store_config')
            ->with($data);
        }

        $breadcrumb['url'] = sc_route('admin_MultiVendor.index');
        $breadcrumb['name'] = sc_language_render('store.admin.list');
        
        $data = [
            'title' => sc_language_render('store.admin.config_store', ['id' => $id]),
            'subTitle' => '',
            'icon' => 'fas fa-cogs',        
        ];
        $stories = AdminStore::getListAll();
        $data['store'] = $stories[$id] ?? [];

        // Customer config
        $dataCustomerConfig = [
            'code' => 'customer_config_attribute',
            'storeId' => $id,
            'keyBy' => 'key',
        ];
        $customerConfigs = AdminConfig::getListConfigByCode($dataCustomerConfig);
        
        $dataCustomerConfigRequired = [
            'code' => 'customer_config_attribute_required',
            'storeId' => $id,
            'keyBy' => 'key',
        ];
        $customerConfigsRequired = AdminConfig::getListConfigByCode($dataCustomerConfigRequired);
        //End customer

        //Product config
        $taxs = ShopTax::pluck('name', 'id')->toArray();
        $taxs[0] = sc_language_render('tax.admin.non_tax');

        $productConfigQuery = [
            'code' => 'product_config',
            'storeId' => $id,
            'keyBy' => 'key',
        ];
        $productConfig = AdminConfig::getListConfigByCode($productConfigQuery);

        $productConfigAttributeQuery = [
            'code' => 'product_config_attribute',
            'storeId' => $id,
            'keyBy' => 'key',
        ];
        $productConfigAttribute = AdminConfig::getListConfigByCode($productConfigAttributeQuery);

        $productConfigAttributeRequiredQuery = [
            'code' => 'product_config_attribute_required',
            'storeId' => $id,
            'keyBy' => 'key',
        ];
        $productConfigAttributeRequired = AdminConfig::getListConfigByCode($productConfigAttributeRequiredQuery);

        $orderConfigQuery = [
            'code' => 'order_config',
            'storeId' => $id,
            'keyBy' => 'key',
        ];
        $orderConfig = AdminConfig::getListConfigByCode($orderConfigQuery);

        $configDisplayQuery = [
            'code' => 'display_config',
            'storeId' => $id,
            'keyBy' => 'key',
        ];
        $configDisplay = AdminConfig::getListConfigByCode($configDisplayQuery);

        $configCaptchaQuery = [
            'code' => 'captcha_config',
            'storeId' => $id,
            'keyBy' => 'key',
        ];
        $configCaptcha = AdminConfig::getListConfigByCode($configCaptchaQuery);

        $emailConfigQuery = [
            'code'    => ['smtp_config', 'email_action'],
            'storeId' => $id,
            'groupBy' => 'code',
            'sort'    => 'asc',
        ];
        $emailConfig = AdminConfig::getListConfigByCode($emailConfigQuery);
        $data['smtp_method'] = ['' => 'None Secirity', 'TLS' => 'TLS', 'SSL' => 'SSL'];
        $data['captcha_page'] = [
            'register' => sc_language_render('captcha.captcha_page_register'), 
            'forgot'   => sc_language_render('captcha.captcha_page_forgot_password'), 
            'checkout' => sc_language_render('captcha.captcha_page_checkout'), 
            'contact'  => sc_language_render('captcha.captcha_page_contact'), 
        ];
        //End email
        $data['customerConfigs']                = $customerConfigs;
        $data['customerConfigsRequired']        = $customerConfigsRequired;
        $data['productConfig']                  = $productConfig;
        $data['productConfigAttribute']         = $productConfigAttribute;
        $data['productConfigAttributeRequired'] = $productConfigAttributeRequired;
        $data['pluginCaptchaInstalled']         = sc_get_plugin_captcha_installed();
        $data['taxs']                           = $taxs;
        $data['configDisplay']                  = $configDisplay;
        $data['orderConfig']                    = $orderConfig;
        $data['configCaptcha']                  = $configCaptcha;
        $data['emailConfig']                    = $emailConfig;
        $data['templates']                      = $this->templates;
        $data['languages']                      = $this->languages;
        $data['currencies']                     = $this->currencies;
        $data['storeId']                        = $id;
        $data['pathPlugin']                     = $this->plugin->pathPlugin;
        $data['breadcrumb']                     = $breadcrumb;
        $data['urlUpdateConfig']                = sc_route('admin_config.update');
        $data['urlUpdateStore']                 = sc_route('admin_store.update');
        return view($this->plugin->pathPlugin.'::Admin.screen.root.store_config')
        ->with($data);
    }

    /*
    Delete list item
    Need mothod destroy to boot deleting in model
    */
    public function delete()
    {
        if (!request()->ajax()) {
            return response()->json(['error' => 1, 'msg' => 'Method not allow!']);
        } else {
            $id = request('id');
            if (config('app.storeId') == $id) {
                return response()->json(['error' => 1, 'msg' => sc_language_render('store.cannot_delete')]);
            }
            if ($id != SC_ID_ROOT) {
                //Destroy store
                AdminStore::destroy($id);
                //Delete config
                Adminconfig::where('store_id', $id)->delete();
                //Delete link
                ShopLink::where('module', 'store_'.$id)->delete();
            }
            return response()->json(['error' => 0, 'msg' => 'Remove store success!']);
        }
    }

}
