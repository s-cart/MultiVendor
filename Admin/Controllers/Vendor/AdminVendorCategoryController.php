<?php
namespace App\Plugins\Other\MultiVendor\Admin\Controllers\Vendor;

use SCart\Core\Front\Models\ShopLanguage;
use Validator;
use App\Plugins\Other\MultiVendor\Admin\Models\AdminVendorCategory;
use App\Plugins\Other\MultiVendor\Admin\Controllers\RootAdminVendorController;

class AdminVendorCategoryController extends RootAdminVendorController
{
    public $languages;

    public function __construct()
    {
        parent::__construct();
        $this->languages = ShopLanguage::getListActive();

    }

    public function index()
    {
        $data = [
            'title'         => sc_language_render($this->plugin->pathPlugin.'::category_store.admin.list'),
            'subTitle'      => '',
            'icon'          => 'fa fa-indent',
            'urlDeleteItem' => sc_route_admin('admin_mvendor_category.delete'),
            'removeList'    => 1, // 1 - Enable function delete list item
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
            'image'  => sc_language_render($this->plugin->pathPlugin.'::category_store.image'),
            'title'  => sc_language_render($this->plugin->pathPlugin.'::category_store.title'),
            'status' => sc_language_render($this->plugin->pathPlugin.'::category_store.status'),
            'sort'   => sc_language_render($this->plugin->pathPlugin.'::category_store.sort'),
            'action' => sc_language_render($this->plugin->pathPlugin.'::category_store.admin.action'),
        ];
        $sort_order = sc_clean(request('sort_order') ?? 'id_desc');
        $keyword    = sc_clean(request('keyword') ?? '');
        $arrSort = [
            'id__desc' => sc_language_render($this->plugin->pathPlugin.'::category_store.admin.sort_order.id_desc'),
            'id__asc' => sc_language_render($this->plugin->pathPlugin.'::category_store.admin.sort_order.id_asc'),
            'title__desc' => sc_language_render($this->plugin->pathPlugin.'::category_store.admin.sort_order.title_desc'),
            'title__asc' => sc_language_render($this->plugin->pathPlugin.'::category_store.admin.sort_order.title_asc'),
        ];
        
        $dataSearch = [
            'keyword'    => $keyword,
            'sort_order' => $sort_order,
            'arrSort'    => $arrSort,
        ];
        $dataTmp = (new AdminVendorCategory)->getVendorCategoryListAdmin($dataSearch);

        $dataTr = [];
        foreach ($dataTmp as $key => $row) {
            $dataTr[$row['id']] = [
                'image' => sc_image_render($row->getThumb(), '50px', '50px', $row['title']),
                'title' => $row['title'],
                'status' => $row['status'] ? '<span class="badge badge-success">ON</span>' : '<span class="badge badge-danger">OFF</span>',
                'sort' => $row['sort'],
                'action' => '
                    <a href="' . sc_route_admin('admin_mvendor_category.edit', ['id' => $row['id'] ? $row['id'] : 'not-found-id']) . '"><span title="' . sc_language_render($this->plugin->pathPlugin.'::category_store.admin.edit') . '" type="button" class="btn btn-flat btn-primary"><i class="fa fa-edit"></i></span></a>&nbsp;

                    <span onclick="deleteItem(\'' . $row['id'] . '\');"  title="' . sc_language_render('admin.delete') . '" class="btn btn-flat btn-danger"><i class="fas fa-trash-alt"></i></span>'
                ,
            ];
        }

        $data['listTh'] = $listTh;
        $data['dataTr'] = $dataTr;
        $data['pagination'] = $dataTmp->appends(request()->except(['_token', '_pjax']))->links($this->templatePathAdmin.'component.pagination');
        $data['resultItems'] = sc_language_render('admin.result_item', ['item_from' => $dataTmp->firstItem(), 'item_to' => $dataTmp->lastItem(), 'total' =>  $dataTmp->total()]);


        //menuRight
        $data['menuRight'][] = '<a href="' . sc_route_admin('admin_mvendor_category.create') . '" class="btn  btn-success  btn-flat" title="New" id="button_create_new">
        <i class="fa fa-plus" title="'.sc_language_render('admin.add_new').'"></i>
        </a>';
        //=menuRight

        //menuSort        
        $optionSort = '';
        foreach ($arrSort as $key => $sort) {
            $optionSort .= '<option  ' . (($sort_order == $key) ? "selected" : "") . ' value="' . $key . '">' . $sort . '</option>';
        }
        $data['urlSort'] = sc_route_admin('admin_mvendor_category.index', request()->except(['_token', '_pjax', 'sort_order']));
        $data['optionSort'] = $optionSort;
        //=menuSort

        //menuSearch        
        $data['topMenuRight'][] = '
                <form action="' . sc_route_admin('admin_mvendor_category.index') . '" id="button_search">
                <div class="input-group input-group" style="width: 250px;">
                    <input type="text" name="keyword" class="form-control float-right" placeholder="' . sc_language_render($this->plugin->pathPlugin.'::category_store.admin.search_place') . '" value="' . $keyword . '">
                    <div class="input-group-append">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i></button>
                    </div>
                </div>
                </form>';
        //=menuSearch

        return view($this->plugin->pathPlugin.'::Admin.screen.vendor.list')
            ->with($data);
    }

    /*
     * Form create new order in admin
     * @return [type] [description]
     */
    public function create()
    {
        $data = [
            'title'             => sc_language_render($this->plugin->pathPlugin.'::category_store.admin.add_new_title'),
            'subTitle'          => '',
            'title_description' => sc_language_render($this->plugin->pathPlugin.'::category_store.admin.add_new_des'),
            'icon'              => 'fa fa-plus',
            'languages'         => $this->languages,
            'category_store'    => [],
            'pathPlugin'        => $this->plugin->pathPlugin,
            'url_action'        => sc_route_admin('admin_mvendor_category.create'),
        ];

        return view($this->plugin->pathPlugin.'::Admin.screen.vendor.category_store')
            ->with($data);
    }

    /*
     * Post create new order in admin
     * @return [type] [description]
     */
    public function postCreate()
    {
        $data = request()->all();

        $langFirst = array_key_first(sc_language_all()->toArray()); //get first code language active
        $data['alias'] = !empty($data['alias'])?$data['alias']:$data['descriptions'][$langFirst]['title'];
        $data['alias'] = sc_word_format_url($data['alias']);
        $data['alias'] = sc_word_limit($data['alias'], 100);

        $validator = Validator::make($data, [
                'sort'                   => 'numeric|min:0',
                'alias'                  => 'required|regex:/(^([0-9A-Za-z\-_]+)$)/|string|max:100',
                'descriptions.*.title'   => 'required|string|max:200',
                'descriptions.*.keyword' => 'nullable|string|max:200',
                'descriptions.*.description' => 'nullable|string|max:300',
            ], [
                'descriptions.*.title.required' => sc_language_render('validation.required', ['attribute' => sc_language_render($this->plugin->pathPlugin.'::category_store.title')]),
                'alias.regex' => sc_language_render($this->plugin->pathPlugin.'::category_store.alias_validate'),
            ]
        );

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput($data);
        }
        $dataInsert = [
            'image'    => $data['image'],
            'alias'    => $data['alias'],
            'status'   => !empty($data['status']) ? 1 : 0,
            'sort'     => (int) $data['sort'],
            'store_id' => session('adminStoreId'),
        ];
        $category_store = AdminVendorCategory::createVendorCategoryAdmin($dataInsert);
        $dataDes = [];
        $languages = $this->languages;
        foreach ($languages as $code => $value) {
            $dataDes[] = [
                'vendor_category_id' => $category_store->id,
                'lang'        => $code,
                'title'       => $data['descriptions'][$code]['title'],
                'keyword'     => $data['descriptions'][$code]['keyword'],
                'description' => $data['descriptions'][$code]['description'],
            ];
        }
        AdminVendorCategory::insertDescriptionAdmin($dataDes);

        sc_clear_cache('cache_category_store');

        return redirect()->route('admin_mvendor_category.index')->with('success', sc_language_render($this->plugin->pathPlugin.'::category_store.admin.create_success'));

    }

    /*
     * Form edit
     */
    public function edit($id)
    {
        $category_store = AdminVendorCategory::getVendorCategoryAdmin($id);

        if (!$category_store) {
            return redirect()->route('admin.data_not_found')->with(['url' => url()->full()]);
        }

        $data                   =  [
            'title'             => sc_language_render($this->plugin->pathPlugin.'::category_store.admin.edit'),
            'subTitle'          => '',
            'title_description' => '',
            'icon'              => 'fa fa-edit',
            'languages'         => $this->languages,
            'category_store'    => $category_store,
            'pathPlugin'        => $this->plugin->pathPlugin,
            'url_action'        => sc_route_admin('admin_mvendor_category.edit', ['id' => $category_store['id']]),
        ];
        return view($this->plugin->pathPlugin.'::Admin.screen.vendor.category_store')
            ->with($data);
    }

    /*
     * update status
     */
    public function postEdit($id)
    {
        $category_store = AdminVendorCategory::getVendorCategoryAdmin($id);
        if (!$category_store) {
            return redirect()->route('admin.data_not_found')->with(['url' => url()->full()]);
        }

        $data = request()->all();

        $langFirst = array_key_first(sc_language_all()->toArray()); //get first code language active
        $data['alias'] = !empty($data['alias'])?$data['alias']:$data['descriptions'][$langFirst]['title'];
        $data['alias'] = sc_word_format_url($data['alias']);
        $data['alias'] = sc_word_limit($data['alias'], 100);

        $validator = Validator::make($data, [
            'sort'                   => 'numeric|min:0',
            'alias'                  => 'required|regex:/(^([0-9A-Za-z\-_]+)$)/|string|max:100',
            'descriptions.*.title'   => 'required|string|max:200',
            'descriptions.*.keyword' => 'nullable|string|max:200',
            'descriptions.*.description' => 'nullable|string|max:300',
            ], [
                'descriptions.*.title.required' => sc_language_render('validation.required', ['attribute' => sc_language_render($this->plugin->pathPlugin.'::category_store.title')]),
                'alias.regex'                   => sc_language_render($this->plugin->pathPlugin.'::category_store.alias_validate'),
            ]
        );

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput($data);
        }
        //Edit
        $dataUpdate = [
            'image'    => $data['image'],
            'alias'    => $data['alias'],
            'sort'     => $data['sort'],
            'status'   => empty($data['status']) ? 0 : 1,
            'store_id' => session('adminStoreId'),
        ];

        $category_store->update($dataUpdate);
        $category_store->descriptions()->delete();
        $dataDes = [];
        foreach ($data['descriptions'] as $code => $row) {
            $dataDes[] = [
                'vendor_category_id' => $id,
                'lang'        => $code,
                'title'       => $row['title'],
                'keyword'     => $row['keyword'],
                'description' => $row['description'],
            ];
        }
        AdminVendorCategory::insertDescriptionAdmin($dataDes);

        sc_clear_cache('cache_category_store');

    //
        return redirect()->route('admin_mvendor_category.index')->with('success', sc_language_render($this->plugin->pathPlugin.'::category_store.admin.edit_success'));

    }

    /*
    Delete list Item
    Need mothod destroy to boot deleting in model
    */
    public function deleteList()
    {
        if (!request()->ajax()) {
            return response()->json(['error' => 1, 'msg' => sc_language_render('admin.method_not_allow')]);
        } else {
            $ids = request('ids');
            $arrID = explode(',', $ids);
            $arrDontPermission = [];
            foreach ($arrID as $key => $id) {
                if(!$this->checkPermisisonItem($id)) {
                    $arrDontPermission[] = $id;
                }
            }
            if (count($arrDontPermission)) {
                return response()->json(['error' => 1, 'msg' => sc_language_render('admin.remove_dont_permisison') . ': ' . json_encode($arrDontPermission)]);
            }
            AdminVendorCategory::destroy($arrID);
            sc_clear_cache('cache_category_store');
            return response()->json(['error' => 0, 'msg' => '']);
        }
    }

    /**
     * Check permisison item
     */
    public function checkPermisisonItem($id) {
        return AdminVendorCategory::getVendorCategoryAdmin($id);
    }

}
