<?php
namespace App\Plugins\Other\MultiVendor\Admin\Controllers\Vendor;

use SCart\Core\Admin\Models\AdminStore;
use SCart\Core\Front\Models\ShopLanguage;
use SCart\Core\Front\Models\ShopCurrency;
use Illuminate\Support\Facades\Artisan;
use Validator;
use DB;
use App\Plugins\Other\MultiVendor\Admin\Controllers\RootAdminVendorController;

class AdminVendorInfoController extends RootAdminVendorController
{
    public $templates, $currencies, $languages;

    public function __construct()
    {
        parent::__construct();
        $allTemplate = sc_get_all_template();
        $templates = [];
        foreach ($allTemplate as $key => $template) {
            $templates[$key] = empty($template['config']['name']) ? $key : $template['config']['name'];
        }
        $this->templates = $templates;
        $this->currencies = ShopCurrency::getCodeActive();
        $this->languages = ShopLanguage::getListActive();
    }
    
    /**
     * Form create new store in admin
     * @return [type] [description]
     */
    public function vendorUpdate()
    {
        $data = [
            'title' => sc_language_render('multi_vendor.update_info_store_title'),
            'subTitle' => '',
            'icon' => 'fa fa-plus',
            'store' => [],
            'languages' => $this->languages,
            'url_action' => sc_route('admin_mvendor_info.update'),
            'templates' => $this->templates
        ];
        
        $store = AdminStore::find(session('adminStoreId'));
        $data['currencies'] =$this->currencies;
        $data['store'] = $store;


        return view($this->plugin->pathPlugin.'::Admin.screen.vendor.store_update')
            ->with($data);
    }


    /*
    * Post create new store in admin
    * @return [type] [description]
    */
    public function vendorPostUpdate()
    {
        $data = request()->all();
        $data['code'] = sc_word_limit(sc_word_format_url($data['code'] ?? ''), 20);
        $id = session('adminStoreId');
        
        $dataValidate = [
            'descriptions.*.title' => 'required|string|max:200',
            'descriptions.*.keyword' => 'nullable|string|max:200',
            'descriptions.*.description' => 'nullable|string|max:300',
            'template' => 'required',
        ];
        $store = AdminStore::find($id);
        if ($store) {
                //
        } else {
            //Code only process when create new vendor
            $dataValidate['code'] = 'required|string|max:20|unique:"'.AdminStore::class.'",code';
        }

        $validator = Validator::make($data, $dataValidate, [
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

        $dataProcess = [
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
            'status'      => empty($data['status']) ? 0 : 1,
        ];
        try {
        //Update new store
        DB::connection(SC_CONNECTION)
            ->transaction(function () use ($dataProcess, $data, $store) {
                if ($store) {
                    $dataProcess['domain'] = $data['domain'] ?? '';
                    $store->update($dataProcess);
                    $store->save();

                    $store->descriptions()->delete();

                    $dataDes = [];
                    $languages = ShopLanguage::getListActive();
                    foreach ($languages as $code => $value) {
                        $dataDes[] = [
                            'store_id'    => $store->id,
                            'lang'        => $code,
                            'title'       => $data['descriptions'][$code]['title'],
                            'keyword'     => $data['descriptions'][$code]['keyword'],
                            'description' => $data['descriptions'][$code]['description'],
                            'maintain_content' => '<center><img src="/images/maintenance.png" />
                            <h3><span style="color:#e74c3c;"><strong>Sorry! We are currently doing site maintenance!</strong></span></h3>
                            </center>'
                        ];
                    }
                    AdminStore::insertDescription($dataDes);

                } else {
                    $dataProcess['code'] = $data['code'];
                    if (sc_config_global('MultiVendor_product_auto_approve')) {
                        $dataProcess['active'] = 1;
                    } else {
                        $dataProcess['active'] = 0;
                    }
                    $store = AdminStore::create($dataProcess);
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
                    AdminStore::insertDescription($dataDes);

                    vendor()->user()->update(['store_id' => $storeId]);

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

                }
            }, 2);
        }catch(\Throwable $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
        return redirect()->route('vendor_admin.home')->with('success', sc_language_render('action.create_success'));

    }



}
