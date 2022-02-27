<?php
namespace App\Plugins\Other\MultiVendor\Admin\Controllers\Root;


use SCart\Core\Front\Models\ShopCountry;
use App\Plugins\Other\MultiVendor\Admin\Models\AdminVendorUser;
use Validator;
use App\Plugins\Other\MultiVendor\Admin\Controllers\RootAdminVendorController;

class AdminRootVendorUserController extends RootAdminVendorController
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        $data = [
            'title'         => sc_language_render('multi_vendor.vendor_user'),
            'subTitle'      => '',
            'icon'          => 'fa fa-indent',
            'urlDeleteItem' => sc_route_admin('admin_MultiVendorUser.delete'),
            'removeList'    => 1, // 1 - Enable function delete list item
            'buttonRefresh' => 1, // 1 - Enable button refresh
            'buttonSort'    => 0, // 1 - Enable button sort
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
            'name'       => sc_language_render('multi_vendor.name'),
            'email'       => sc_language_render('multi_vendor.email'),
            'store'       => sc_language_render('front.store_list'),
            'status' => sc_language_render('multi_vendor.status'),
            'created_at' => sc_language_render('admin.created_at'),
            'action'     => sc_language_render('action.title'),
        ];
        $keyword    = sc_clean(request('keyword') ?? '');

        $obj = new AdminVendorUser;

        if ($keyword) {
            $obj = $obj->whereRaw('(id = ' . (int) $keyword . '  OR first_name like "%' . $keyword . '%" OR last_name like "%' . $keyword . '%" OR email = "' . $keyword . '"  )');
        }

        $obj = $obj->orderBy('id', 'desc');

        $dataTmp = $obj->paginate(20);

        $dataTr = [];
        foreach ($dataTmp as $key => $row) {
            $codeStore = sc_get_list_code_store()[$row['store_id']] ?? '';
            $dataTr[$row['id']] = [
                'name' => $row['name'],
                'email' => $row['email'],
                'store' => '<i class="nav-icon fab fa-shopify"></i> <a href="'.sc_get_domain_from_code($codeStore).'">'.$codeStore.'</a>',
                'status' => $row['status'] ? '<span class="badge badge-success">ON</span>' : '<span class="badge badge-danger">OFF</span>',
                'created_at' => $row['created_at'],
                'action' => '
                    <a href="' . sc_route_admin('admin_MultiVendorUser.edit', ['id' => $row['id'] ? $row['id'] : 'not-found-id']) . '"><span title="' . sc_language_render('action.edit') . '" type="button" class="btn btn-flat btn-primary"><i class="fa fa-edit"></i></span></a>&nbsp;
                    <span onclick="deleteItem(\'' . $row['id'] . '\');"  title="' . sc_language_render('action.delete') . '" class="btn btn-flat btn-danger"><i class="fas fa-trash-alt"></i></span>'
                ,
            ];
        }

        $data['listTh'] = $listTh;
        $data['dataTr'] = $dataTr;
        $data['pagination'] = $dataTmp->appends(request()->except(['_token', '_pjax']))->links($this->templatePathAdmin.'component.pagination');
        $data['resultItems'] = sc_language_render('admin.result_item', ['item_from' => $dataTmp->firstItem(), 'item_to' => $dataTmp->lastItem(), 'total' =>  $dataTmp->total()]);

//menuRight
        $data['menuRight'][] = '<a href="' . sc_route_admin('admin_MultiVendorUser.create') . '" class="btn btn-success  btn-flat" title="New" id="button_create_new">
                           <i class="fa fa-user-plus" aria-hidden="true"></i>
                           </a>';
//=menuRight

//menuSort
        $optionSort = '';

        $data['optionSort'] = $optionSort;
//=menuSort

//menuSearch
        $data['topMenuRight'][] = '
                <form action="' . sc_route_admin('admin_MultiVendorUser.index') . '" id="button_search">
                <div class="input-group input-group" style="width: 350px;">
                    <input type="text" name="keyword" class="form-control rounded-0 float-right" placeholder="' . sc_language_render('multi_vendor.vendor_search_place') . '" value="' . $keyword . '">
                    <div class="input-group-append">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i></button>
                    </div>
                </div>
                </form>';
//=menuSearch

        return view($this->plugin->pathPlugin.'::Admin.screen.root.vendor_list')
            ->with($data);
    }

/**
 * Form create new item in admin
 * @return [type] [description]
 */
    public function create()
    {
        $data = [
            'title'             => sc_language_render('multi_vendor.vendor_add'),
            'subTitle'          => '',
            'title_description' => '',
            'icon'              => 'fa fa-plus',
            'vendor'          => [],
            'countries'         => (new ShopCountry)->getCodeAll(),
            'url_action'        => sc_route_admin('admin_MultiVendorUser.create'),
        ];

        return view($this->plugin->pathPlugin.'::Admin.screen.root.vendor_user_add')
            ->with($data);
    }

/**
 * Post create new item in admin
 * @return [type] [description]
 */
    public function postCreate()
    {
        $arraycountry = (new ShopCountry)->pluck('code')->toArray();

        $data = request()->all();
        $validate = [
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'password' => 'required|string|min:6',
            'email' => 'required|string|email|max:255|unique:"'.AdminVendorUser::class.'",email',
        ];
        $validate['address1'] = 'nullable|string|max:100';
        $validate['address2'] = 'nullable|string|max:100';
        $validate['postcode'] = 'nullable|min:5';
        $validate['store_id'] = 'required';
        $validate['country'] = 'nullable|string|min:2|in:'. implode(',', $arraycountry);
        $validate['phone'] = 'nullable';

        $messages = [
            'last_name.required'   => sc_language_render('validation.required', ['attribute'=> sc_language_render('multi_vendor.last_name')]),
            'first_name.required'  => sc_language_render('validation.required', ['attribute'=> sc_language_render('multi_vendor.first_name')]),
            'email.required'       => sc_language_render('validation.required', ['attribute'=> sc_language_render('multi_vendor.email')]),
            'password.required'    => sc_language_render('validation.required', ['attribute'=> sc_language_render('multi_vendor.password')]),
            'address1.required'    => sc_language_render('validation.required', ['attribute'=> sc_language_render('multi_vendor.address1')]),
            'address2.required'    => sc_language_render('validation.required', ['attribute'=> sc_language_render('multi_vendor.address2')]),
            'phone.required'       => sc_language_render('validation.required', ['attribute'=> sc_language_render('multi_vendor.phone')]),
            'country.required'     => sc_language_render('validation.required', ['attribute'=> sc_language_render('multi_vendor.country')]),
            'postcode.required'    => sc_language_render('validation.required', ['attribute'=> sc_language_render('multi_vendor.postcode')]),
            'email.email'          => sc_language_render('validation.email', ['attribute'=> sc_language_render('multi_vendor.email')]),
            'phone.regex'          => sc_language_render('multi_vendor.phone_regex'),
            'postcode.min'         => sc_language_render('validation.min', ['attribute'=> sc_language_render('multi_vendor.postcode')]),
            'password.min'         => sc_language_render('validation.min', ['attribute'=> sc_language_render('multi_vendor.password')]),
            'country.min'          => sc_language_render('validation.min', ['attribute'=> sc_language_render('multi_vendor.country')]),
            'first_name.max'       => sc_language_render('validation.max', ['attribute'=> sc_language_render('multi_vendor.first_name')]),
            'email.max'            => sc_language_render('validation.max', ['attribute'=> sc_language_render('multi_vendor.email')]),
            'address1.max'         => sc_language_render('validation.max', ['attribute'=> sc_language_render('multi_vendor.address1')]),
            'address2.max'         => sc_language_render('validation.max', ['attribute'=> sc_language_render('multi_vendor.address2')]),
            'last_name.max'        => sc_language_render('validation.max', ['attribute'=> sc_language_render('multi_vendor.last_name')]),
        ];

        $validator = Validator::make($data, $validate, $messages);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        $data['password'] = bcrypt($data['password']);
        $data['email'] = strtolower($data['email']);
        $data['status'] = empty($data['status']) ? 0 : 1;
        $dataClean = sc_clean($data, ['password'], true);
        $vendor = AdminVendorUser::create($dataClean);

        return redirect()->route('admin_MultiVendorUser.index')->with('success', sc_language_render('action.create_success'));

    }

/**
 * Form edit
 */
    public function edit($id)
    {
        $vendor = AdminVendorUser::find($id);
        if ($vendor === null) {
            return 'no data';
        }
        $data = [
            'title'             => sc_language_render('action.edit'),
            'subTitle'          => '',
            'title_description' => '',
            'icon'              => 'fa fa-edit',
            'vendor'              => $vendor,
            'countries'         => (new ShopCountry)->getCodeAll(),
            'url_action'        => sc_route_admin('admin_MultiVendorUser.edit', ['id' => $vendor['id']]),

        ];
        return view($this->plugin->pathPlugin.'::Admin.screen.root.vendor_user_add')
            ->with($data);
    }

/**
 * update status
 */
    public function postEdit($id)
    {
        $vendor = AdminVendorUser::find($id);
        $arraycountry = (new ShopCountry)->pluck('code')->toArray();

        $data = request()->all();
        $validate = [
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'password' => 'nullable|string|min:6',
            'email' => 'nullable|string|email|max:255|unique:"'.AdminVendorUser::class.'",email,'.$vendor['id'].',id',
        ];
        $validate['address1'] = 'nullable|string|max:100';
        $validate['address2'] = 'nullable|string|max:100';
        $validate['postcode'] = 'nullable|min:5';
        $validate['store_id'] = 'required';
        $validate['country'] = 'nullable|string|min:2|in:'. implode(',', $arraycountry);
        $validate['phone'] = 'nullable';

        $messages = [
            'last_name.required'   => sc_language_render('validation.required', ['attribute'=> sc_language_render('multi_vendor.last_name')]),
            'first_name.required'  => sc_language_render('validation.required', ['attribute'=> sc_language_render('multi_vendor.first_name')]),
            'email.required'       => sc_language_render('validation.required', ['attribute'=> sc_language_render('multi_vendor.email')]),
            'password.required'    => sc_language_render('validation.required', ['attribute'=> sc_language_render('multi_vendor.password')]),
            'address1.required'    => sc_language_render('validation.required', ['attribute'=> sc_language_render('multi_vendor.address1')]),
            'address2.required'    => sc_language_render('validation.required', ['attribute'=> sc_language_render('multi_vendor.address2')]),
            'phone.required'       => sc_language_render('validation.required', ['attribute'=> sc_language_render('multi_vendor.phone')]),
            'country.required'     => sc_language_render('validation.required', ['attribute'=> sc_language_render('multi_vendor.country')]),
            'postcode.required'    => sc_language_render('validation.required', ['attribute'=> sc_language_render('multi_vendor.postcode')]),
            'email.email'          => sc_language_render('validation.email', ['attribute'=> sc_language_render('multi_vendor.email')]),
            'phone.regex'          => sc_language_render('multi_vendor.phone_regex'),
            'postcode.min'         => sc_language_render('validation.min', ['attribute'=> sc_language_render('multi_vendor.postcode')]),
            'password.min'         => sc_language_render('validation.min', ['attribute'=> sc_language_render('multi_vendor.password')]),
            'country.min'          => sc_language_render('validation.min', ['attribute'=> sc_language_render('multi_vendor.country')]),
            'first_name.max'       => sc_language_render('validation.max', ['attribute'=> sc_language_render('multi_vendor.first_name')]),
            'email.max'            => sc_language_render('validation.max', ['attribute'=> sc_language_render('multi_vendor.email')]),
            'address1.max'         => sc_language_render('validation.max', ['attribute'=> sc_language_render('multi_vendor.address1')]),
            'address2.max'         => sc_language_render('validation.max', ['attribute'=> sc_language_render('multi_vendor.address2')]),
            'last_name.max'        => sc_language_render('validation.max', ['attribute'=> sc_language_render('multi_vendor.last_name')]),
        ];

        $validator = Validator::make($data, $validate, $messages);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        if (!empty($data['password'])) {
            $data['password'] = bcrypt($data['password']);
        } else {
            unset($data['password']);
        }
        $data['email'] = strtolower($data['email']);
        $data['status'] = empty($data['status']) ? 0 : 1;
        $dataClean = sc_clean($data, ['password'], true);
        $vendor->update($dataClean);

        return redirect()->route('admin_MultiVendorUser.index')->with('success', sc_language_render('action.edit_success'));

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
            AdminVendorUser::destroy($arrID);
            return response()->json(['error' => 0, 'msg' => '']);
        }
    }

}
