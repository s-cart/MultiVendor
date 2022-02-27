<?php
namespace App\Plugins\Other\MultiVendor\Admin\Controllers\Vendor;

use SCart\Core\Front\Models\ShopAttributeGroup;
use SCart\Core\Front\Models\ShopBrand;
use SCart\Core\Front\Models\ShopTax;
use SCart\Core\Front\Models\ShopLanguage;
use SCart\Core\Front\Models\ShopWeight;
use SCart\Core\Front\Models\ShopLength;
use SCart\Core\Front\Models\ShopProductAttribute;
use SCart\Core\Front\Models\ShopProductBuild;
use SCart\Core\Front\Models\ShopProductGroup;
use SCart\Core\Front\Models\ShopProductImage;
use SCart\Core\Front\Models\ShopSupplier;
use SCart\Core\Front\Models\ShopProductDownload;
use SCart\Core\Front\Models\ShopProductProperty;
use SCart\Core\Front\Models\ShopCustomField;
use SCart\Core\Front\Models\ShopCustomFieldDetail;
use SCart\Core\Admin\Models\AdminProduct;
use SCart\Core\Admin\Models\AdminCategory;
use App\Plugins\Other\MultiVendor\Models\VendorProductCategory;
use Illuminate\Support\Facades\Validator;
use App\Plugins\Other\MultiVendor\Admin\Controllers\RootAdminVendorController;

class AdminVendorProductController extends RootAdminVendorController
{
    public $languages;
    public $properties;
    public $attributeGroup;
    public $listWeight;
    public $listLength;
    public $categories;

    public function __construct()
    {
        parent::__construct();
        $this->languages       = ShopLanguage::getListActive();
        $this->listWeight      = ShopWeight::getListAll();
        $this->listLength      = ShopLength::getListAll();
        $this->attributeGroup  = ShopAttributeGroup::getListAll();
        $this->properties = (new ShopProductProperty)->pluck('name', 'code')->toArray();
    }

    public function kinds() {
        return [
            SC_PRODUCT_SINGLE => sc_language_render('product.kind_single'),
            SC_PRODUCT_BUILD  => sc_language_render('product.kind_bundle'),
            SC_PRODUCT_GROUP  => sc_language_render('product.kind_group'),
        ];
    }

    public function index()
    {
        $categoriesTitle = AdminCategory::getListTitleAdmin();
        $data = [
            'title'         => sc_language_render('product.admin.list'),
            'subTitle'      => '',
            'icon'          => 'fa fa-indent',
            'urlDeleteItem' => sc_route_admin('admin_mvendor_product.delete'),
            'removeList'    => 1, // Enable function delete list item
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
            'image'    => sc_language_render('product.image'),
            'sku'      => sc_language_render('product.sku'),
            'name'     => sc_language_render('product.name'),
            'category' => sc_language_render('product.category'),
        ];
        if (sc_config_admin('product_cost')) {
            $listTh['cost'] = sc_language_render('product.cost');
        }
        if (sc_config_admin('product_price')) {
            $listTh['price'] = sc_language_render('product.price');
        }
        if (sc_config_admin('product_kind')) {
            $listTh['kind'] = sc_language_render('product.kind');
        }
        $listTh['status'] = sc_language_render('product.status');
        $listTh['approve'] = sc_language_render('product.approve');
        $listTh['action'] = sc_language_render('action.title');

        $keyword     = sc_clean(request('keyword') ?? '');
        $category_id = sc_clean(request('category_id') ?? '');
        $sort_order  = sc_clean(request('sort_order') ?? 'id_desc');

        $arrSort = [
            'id__desc'   => sc_language_render('filter_sort.id_desc'),
            'id__asc'    => sc_language_render('filter_sort.id_asc'),
            'name__desc' => sc_language_render('filter_sort.name_desc'),
            'name__asc'  => sc_language_render('filter_sort.name_asc'),
        ];
        $dataSearch = [
            'keyword'     => $keyword,
            'category_id' => $category_id,
            'sort_order'  => $sort_order,
            'arrSort'     => $arrSort,
        ];

        $dataTmp = (new AdminProduct)->getProductListAdmin($dataSearch, session('adminStoreId'));

        $dataTr = [];
        foreach ($dataTmp as $key => $row) {
            $kind = $this->kinds()[$row['kind']] ?? $row['kind'];
            if ($row['kind'] == SC_PRODUCT_BUILD) {
                $kind = '<span class="badge badge-success">' . $kind . '</span>';
            } elseif ($row['kind'] == SC_PRODUCT_GROUP) {
                $kind = '<span class="badge badge-danger">' . $kind . '</span>';
            }
            $arrName = [];
            foreach ($row->categories as $category) {
                $arrName[] = $categoriesTitle[$category->id] ?? '';
            }
            $dataMap = [
                'image' => sc_image_render($row->getThumb(), '50px', '50px', $row['name']),
                'sku' => $row['sku'],
                'name' => $row['name'],
                'category' => implode(';<br>', $arrName),
                
            ];
            if (sc_config_admin('product_cost')) {
                $dataMap['cost'] = $row['cost'];
            }
            if (sc_config_admin('product_price')) {
                $dataMap['price'] = $row['price'];
            }
            if (sc_config_admin('product_kind')) {
                $dataMap['kind'] = $kind;
            }
            $dataMap['status'] = $row['status'] ? '<span class="badge badge-success">ON</span>' : '<span class="badge badge-danger">OFF</span>';
            $dataMap['approve'] = $row['approve'] ? '<span class="badge badge-success">ON</span>' : '<span class="badge badge-danger">OFF</span>';
            $dataMap['action'] = '
            <a href="' . sc_route_admin('admin_mvendor_product.edit', ['id' => $row['id'] ? $row['id'] : 'not-found-id']) . '">
            <span title="' . sc_language_render('product.admin.edit') . '" type="button" class="btn btn-flat btn-primary">
            <i class="fa fa-edit"></i>
            </span>
            </a>&nbsp;

            <span onclick="deleteItem(\'' . $row['id'] . '\');"  title="' . sc_language_render('admin.delete') . '" class="btn btn-flat btn-danger">
            <i class="fas fa-trash-alt"></i>
            </span>';
            $dataTr[$row['id']] = $dataMap;
        }

        $data['listTh'] = $listTh;
        $data['dataTr'] = $dataTr;
        $data['pagination'] = $dataTmp->appends(request()->except(['_token', '_pjax']))->links($this->templatePathAdmin.'component.pagination');
        $data['resultItems'] = sc_language_render('admin.result_item', ['item_from' => $dataTmp->firstItem(), 'item_to' => $dataTmp->lastItem(), 'total' =>  $dataTmp->total()]);

        //menuRight
        $data['menuRight'][] = '<a href="' . sc_route_admin('admin_mvendor_product.create') . '" class="btn btn-success btn-flat" title="'.sc_language_render('product.admin.add_new_title').'" id="button_create_new">
        <i class="fa fa-plus"></i>
        </a>';
        if (sc_config_admin('product_kind')) {
            $data['menuRight'][] = '<a href="' . sc_route_admin('admin_mvendor_product.build_create') . '" class="btn btn-success btn-flat" title="'.sc_language_render('product.admin.add_new_title_build').'" id="button_create_new">
            <i class="fas fa-puzzle-piece"></i>
            </a>';
            $data['menuRight'][] = '<a href="' . sc_route_admin('admin_mvendor_product.group_create') . '" class="btn btn-success btn-flat" title="'.sc_language_render('product.admin.add_new_title_group').'" id="button_create_new">
            <i class="fas fa-network-wired"></i>
            </a>';
        }
        //=menuRight


        //menuSort        
        $optionSort = '';
        foreach ($arrSort as $key => $status) {
            $optionSort .= '<option  ' . (($sort_order == $key) ? "selected" : "") . ' value="' . $key . '">' . $status . '</option>';
        }
        $data['optionSort'] = $optionSort;
        $data['urlSort'] = sc_route_admin('admin_mvendor_product.index', request()->except(['_token', '_pjax', 'sort_order']));
        //=menuSort

        //Search with category
        $optionCategory = '';
        $categories = (new AdminCategory)->getTreeCategoriesAdmin();
        if ($categories) {
            foreach ($categories as $k => $v) {
                $optionCategory .= "<option value='{$k}' ".(($category_id == $k) ? 'selected' : '').">{$v}</option>";
            }
        }

        //topMenuRight
        $data['topMenuRight'][] ='
                <form action="' . sc_route_admin('admin_mvendor_product.index') . '" id="button_search">
                <div class="input-group input-group float-left">
                    <select class="form-control rounded-0 select2" name="category_id" id="category_id">
                    <option value="">'.sc_language_render('product.admin.select_category').'</option>
                    '.$optionCategory.'
                    </select> &nbsp;
                    <input type="text" name="keyword" class="form-control rounded-0 float-right" placeholder="' . sc_language_render('product.admin.search_place') . '" value="' . $keyword . '">
                    <div class="input-group-append">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i></button>
                    </div>
                </div>
                </form>';
        //=topMenuRight

        return view($this->plugin->pathPlugin.'::Admin.screen.vendor.list')
            ->with($data);
    }

/**
 * Form create new order in admin
 * @return [type] [description]
 */
    public function create()
    {
        $categories = (new AdminCategory)->getTreeCategoriesAdmin();

        // html add more images
        $htmlMoreImage = '<div class="input-group"><input type="text" id="id_sub_image" name="sub_image[]" value="image_value" class="form-control rounded-0 input-sm sub_image" placeholder=""  /><span class="input-group-btn"><a data-input="id_sub_image" data-preview="preview_sub_image" data-type="product" class="btn btn-primary lfm"><i class="fa fa-picture-o"></i> Choose</a></span></div><div id="preview_sub_image" class="img_holder"></div>';
        //end add more images

        // html select attribute
        $htmlProductAtrribute = '<tr><td><br><input type="text" name="attribute[attribute_group][name][]" value="attribute_value" class="form-control rounded-0 input-sm" placeholder="' . sc_language_render('product.admin.add_attribute_place') . '" /></td><td><br><input type="number" name="attribute[attribute_group][add_price][]" value="add_price_value" class="form-control rounded-0 input-sm" placeholder="' . sc_language_render('product.admin.add_price_place') . '"></td><td><br><span title="Remove" class="btn btn-flat btn-danger removeAttribute"><i class="fa fa-times"></i></span></td></tr>';
        //end select attribute

        if (function_exists('sc_vendor_get_categories_admin')) {
            // Dont process in __construct because session 
            $categoriesStore = sc_vendor_get_categories_admin();
        } else {
            $categoriesStore = [];
        }

        $data = [
            'title'                => sc_language_render('product.admin.add_new_title'),
            'subTitle'             => '',
            'title_description'    => sc_language_render('product.admin.add_new_des'),
            'icon'                 => 'fa fa-plus',
            'languages'            => $this->languages,
            'categoriesStore'      => $categoriesStore,
            'categories'           => $categories,
            'brands'               => (new ShopBrand)->getListAll(),
            'suppliers'            => (new ShopSupplier)->getListAll(),
            'taxs'                 => (new ShopTax)->getListAll(),
            'properties'            => $this->properties,
            'kinds'                => $this->kinds(),
            'attributeGroup'       => $this->attributeGroup,
            'htmlMoreImage'        => $htmlMoreImage,
            'htmlProductAtrribute' => $htmlProductAtrribute,
            'listWeight'           => $this->listWeight,
            'listLength'           => $this->listLength,
            'customFields'         => (new ShopCustomField)->getCustomField($type = 'product'),
        ];

        return view($this->plugin->pathPlugin.'::Admin.screen.vendor.product_add')
            ->with($data);
    }

    /**
     * Form create new item in admin
     * @return [type] [description]
     */
    public function createProductBuild()
    {
        $listProductSingle = (new AdminProduct)->getProductSelectAdmin(['kind' => [0]], session('adminStoreId'));
        $categories = (new AdminCategory)->getTreeCategoriesAdmin();

        // html select product build
        $htmlSelectBuild = '<div class="select-product">';
        $htmlSelectBuild .= '<table width="100%"><tr><td width="70%"><select class="form-control rounded-0 productInGroup select2" data-placeholder="' . sc_language_render('product.admin.select_product_in_build') . '" style="width: 100%;" name="productBuild[]" >';
        $htmlSelectBuild .= '';
        foreach ($listProductSingle as $k => $v) {
            $htmlSelectBuild .= '<option value="' . $k . '">' . $v['name'] . '</option>';
        }
        $htmlSelectBuild .= '</select></td><td style="width:100px"><input class="form-control rounded-0"  type="number" name="productBuildQty[]" value="1" min=1></td><td><span title="Remove" class="btn btn-flat btn-danger removeproductBuild"><i class="fa fa-times"></i></span></td></tr></table>';
        $htmlSelectBuild .= '</div>';
        //end select product build

        // html select attribute
        $htmlProductAtrribute = '<tr><td><br><input type="text" name="attribute[attribute_group][name][]" value="attribute_value" class="form-control rounded-0 input-sm" placeholder="' . sc_language_render('product.admin.add_attribute_place') . '" /></td><td><br><input type="number" name="attribute[attribute_group][add_price][]" value="add_price_value" class="form-control rounded-0 input-sm" placeholder="' . sc_language_render('product.admin.add_price_place') . '"></td><td><br><span title="Remove" class="btn btn-flat btn-danger removeAttribute"><i class="fa fa-times"></i></span></td></tr>';
        //end select attribute

        // html add more images
        $htmlMoreImage = '<div class="input-group"><input type="text" id="id_sub_image" name="sub_image[]" value="image_value" class="form-control rounded-0 input-sm sub_image" placeholder=""  /><span class="input-group-btn"><a data-input="id_sub_image" data-preview="preview_sub_image" data-type="product" class="btn btn-primary lfm"><i class="fa fa-picture-o"></i> Choose</a></span></div><div id="preview_sub_image" class="img_holder"></div>';
        //end add more images

        if (function_exists('sc_vendor_get_categories_admin')) {
            // Dont process in __construct because session 
            $categoriesStore = sc_vendor_get_categories_admin();
        } else {
            $categoriesStore = [];
        }

        $data = [
            'title'                => sc_language_render('product.admin.add_new_title_build'),
            'subTitle'             => '',
            'title_description'    => sc_language_render('product.admin.add_new_des'),
            'icon'                 => 'fa fa-plus',
            'languages'            => $this->languages,
            'categoriesStore'      => $categoriesStore,
            'categories'           => $categories,
            'brands'               => (new ShopBrand)->getListAll(),
            'suppliers'            => (new ShopSupplier)->getListAll(),
            'taxs'                 => (new ShopTax)->getListAll(),
            'properties'            => $this->properties,
            'kinds'                => $this->kinds(),
            'attributeGroup'       => $this->attributeGroup,
            'htmlSelectBuild'      => $htmlSelectBuild,
            'listProductSingle'    => $listProductSingle,
            'htmlProductAtrribute' => $htmlProductAtrribute,
            'htmlMoreImage'        => $htmlMoreImage,
            'listWeight'           => $this->listWeight,
            'listLength'           => $this->listLength, 
        ];

        return view($this->plugin->pathPlugin.'::Admin.screen.vendor.product_add_build')
            ->with($data);
    }


    /**
     * Form create new item in admin
     * @return [type] [description]
     */
    public function createProductGroup()
    {
        $listProductSingle = (new AdminProduct)->getProductSelectAdmin(['kind' => [0]], session('adminStoreId'));
        $categories = (new AdminCategory)->getTreeCategoriesAdmin();

        // html select product group
        $htmlSelectGroup = '<div class="select-product">';
        $htmlSelectGroup .= '<table width="100%"><tr><td width="80%"><select class="form-control rounded-0 productInGroup select2" data-placeholder="' . sc_language_render('product.admin.select_product_in_group') . '" style="width: 100%;" name="productInGroup[]" >';
        $htmlSelectGroup .= '';
        foreach ($listProductSingle as $k => $v) {
            $htmlSelectGroup .= '<option value="' . $k . '">' . $v['name'] . '</option>';
        }
        $htmlSelectGroup .= '</select></td><td><span title="Remove" class="btn btn-flat btn-danger removeproductInGroup"><i class="fa fa-times"></i></span></td></tr></table>';
        $htmlSelectGroup .= '</div>';
        //End select product group

        if (function_exists('sc_vendor_get_categories_admin')) {
            // Dont process in __construct because session 
            $categoriesStore = sc_vendor_get_categories_admin();
        } else {
            $categoriesStore = [];
        }

        $data = [
            'title'                => sc_language_render('product.admin.add_new_title_group'),
            'subTitle'             => '',
            'title_description'    => sc_language_render('product.admin.add_new_des'),
            'icon'                 => 'fa fa-plus',
            'languages'            => $this->languages,
            'categoriesStore'      => $categoriesStore,
            'categories'           => $categories,
            'brands'               => (new ShopBrand)->getListAll(),
            'suppliers'            => (new ShopSupplier)->getListAll(),
            'taxs'                 => (new ShopTax)->getListAll(),
            'properties'            => $this->properties,
            'kinds'                => $this->kinds(),
            'attributeGroup'       => $this->attributeGroup,
            'listProductSingle'    => $listProductSingle,
            'htmlSelectGroup'      => $htmlSelectGroup,
            'listWeight'           => $this->listWeight,
            'listLength'           => $this->listLength, 
        ];

        return view($this->plugin->pathPlugin.'::Admin.screen.vendor.product_add_group')
            ->with($data);
    }

/**
 * Post create new order in admin
 * @return [type] [description]
 */

    public function postCreate()
    {
        
        $data = request()->all();
        $langFirst = array_key_first(sc_language_all()->toArray()); //get first code language active
        $data['alias'] = !empty($data['alias'])?$data['alias']:$data['descriptions'][$langFirst]['name'];
        $data['alias'] = sc_word_format_url($data['alias']);
        $data['alias'] = sc_word_limit($data['alias'], 100);

        switch ($data['kind']) {
            case SC_PRODUCT_SINGLE: // product single
                $arrValidation = [
                    'kind'                       => 'required',
                    'sort'                       => 'numeric|min:0',
                    'minimum'                    => 'numeric|min:0',
                    'descriptions.*.name'        => 'required|string|max:100',
                    'descriptions.*.keyword'     => 'nullable|string|max:100',
                    'descriptions.*.description' => 'nullable|string|max:100',
                    'descriptions.*.content'     => 'required|string',
                    'category'                   => 'required',
                    'sku'                        => 'required|regex:/(^([0-9A-Za-z\-_]+)$)/|product_sku_unique',
                    'alias'                      => 'required|regex:/(^([0-9A-Za-z\-_]+)$)/|string|max:120|product_alias_unique',
                ];

                $arrValidation = $this->validateAttribute($arrValidation);
                
                $arrMsg = [
                    'descriptions.*.name.required'    => sc_language_render('validation.required', ['attribute' => sc_language_render('product.name')]),
                    'descriptions.*.content.required' => sc_language_render('validation.required', ['attribute' => sc_language_render('product.content')]),
                    'category.required'               => sc_language_render('validation.required', ['attribute' => sc_language_render('product.category')]),
                    'sku.regex'                       => sc_language_render('product.sku_validate'),
                    'sku.product_sku_unique'          => sc_language_render('product.sku_unique'),
                    'alias.regex'                     => sc_language_render('product.alias_validate'),
                    'alias.product_alias_unique'      => sc_language_render('product.alias_unique'),
                ];
                break;

            case SC_PRODUCT_BUILD: //product build
                $arrValidation = [
                    'kind'                       => 'required',
                    'sort'                       => 'numeric|min:0',
                    'minimum'                    => 'numeric|min:0',
                    'descriptions.*.name'        => 'required|string|max:100',
                    'descriptions.*.keyword'     => 'nullable|string|max:100',
                    'descriptions.*.description' => 'nullable|string|max:100',
                    'category'                   => 'required',
                    'sku'                        => 'required|regex:/(^([0-9A-Za-z\-_]+)$)/|product_sku_unique',
                    'alias'                      => 'required|regex:/(^([0-9A-Za-z\-_]+)$)/|string|max:120|product_alias_unique',
                    'productBuild'               => 'required',
                    'productBuildQty'            => 'required',
                ];

                $arrValidation = $this->validateAttribute($arrValidation);

                $arrMsg = [
                    'descriptions.*.name.required' => sc_language_render('validation.required', ['attribute' => sc_language_render('product.name')]),
                    'category.required'            => sc_language_render('validation.required', ['attribute' => sc_language_render('product.category')]),
                    'sku.regex'                    => sc_language_render('product.sku_validate'),
                    'sku.product_sku_unique'       => sc_language_render('product.sku_unique'),
                    'alias.regex'                  => sc_language_render('product.alias_validate'),
                    'alias.product_alias_unique'   => sc_language_render('product.alias_unique'),
                ];
                break;

            case SC_PRODUCT_GROUP: //product group
                $arrValidation = [
                    'kind'                       => 'required',
                    'productInGroup'             => 'required',
                    'sku'                        => 'required|regex:/(^([0-9A-Za-z\-_]+)$)/|product_sku_unique',
                    'alias'                      => 'required|regex:/(^([0-9A-Za-z\-_]+)$)/|string|max:120|product_alias_unique',
                    'sort'                       => 'numeric|min:0',
                    'minimum'                    => 'numeric|min:0',
                    'descriptions.*.name'        => 'required|string|max:200',
                    'descriptions.*.keyword'     => 'nullable|string|max:200',
                    'descriptions.*.description' => 'nullable|string|max:300',
                ];
                $arrMsg = [
                    'descriptions.*.name.required' => sc_language_render('validation.required', ['attribute' => sc_language_render('product.name')]),
                    'sku.regex'                    => sc_language_render('product.sku_validate'),
                    'sku.product_sku_unique'       => sc_language_render('product.sku_unique'),
                    'alias.regex'                  => sc_language_render('product.alias_validate'),
                    'alias.product_alias_unique'   => sc_language_render('product.alias_unique'),
                ];
                break;

            default:
                $arrValidation = [
                    'kind' => 'required',
                ];
                break;
        }

        $validator = Validator::make($data, $arrValidation, $arrMsg ?? []);
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput($data);
        }

        $category        = $data['category'] ?? [];
        $attribute       = $data['attribute'] ?? [];
        $descriptions    = $data['descriptions'];
        $productInGroup  = $data['productInGroup'] ?? [];
        $productBuild    = $data['productBuild'] ?? [];
        $productBuildQty = $data['productBuildQty'] ?? [];
        $subImages       = $data['sub_image'] ?? [];
        $downloadPath    = $data['download_path'] ?? '';
        $dataInsert = [
            'brand_id'       => $data['brand_id'] ?? 0,
            'supplier_id'    => $data['supplier_id'] ?? 0,
            'price'          => $data['price'] ?? 0,
            'sku'            => $data['sku'],
            'cost'           => $data['cost'] ?? 0,
            'stock'          => $data['stock'] ?? 0,
            'weight_class'   => $data['weight_class'] ?? '',
            'length_class'   => $data['length_class'] ?? '',
            'weight'         => $data['weight'] ?? 0,
            'height'         => $data['height'] ?? 0,
            'length'         => $data['length'] ?? 0,
            'width'          => $data['width'] ?? 0,
            'kind'           => $data['kind'] ?? SC_PRODUCT_SINGLE,
            'alias'          => $data['alias'],
            'property'       => $data['property'] ?? SC_PROPERTY_PHYSICAL,
            'image'          => $data['image'] ?? '',
            'tax_id'         => $data['tax_id'] ?? 0,
            'status'         => (!empty($data['status']) ? 1 : 0),
            'sort'           => (int) $data['sort'],
            'minimum'        => (int) ($data['minimum'] ?? 0),
        ];
        if(!empty($data['date_available'])) {
            $dataInsert['date_available'] = $data['date_available'];
        }

        if (!sc_config_admin('MultiVendor_product_auto_approve')) {
            $dataInsert['approve'] = 0;
        }

        //insert product
        $product = AdminProduct::createProductAdmin($dataInsert);

        //Promoton price
        if (isset($data['price_promotion']) && in_array($data['kind'], [SC_PRODUCT_SINGLE, SC_PRODUCT_BUILD])) {
            $arrPromotion['price_promotion'] = $data['price_promotion'];
            $arrPromotion['date_start'] = $data['price_promotion_start'] ? $data['price_promotion_start'] : null;
            $arrPromotion['date_end'] = $data['price_promotion_end'] ? $data['price_promotion_end'] : null;
            $product->promotionPrice()->create($arrPromotion);
        }

        //Insert category
        if ($category && in_array($data['kind'], [SC_PRODUCT_SINGLE, SC_PRODUCT_BUILD])) {
            $product->categories()->attach($category);
        }

        //Insert group
        if ($productInGroup && $data['kind'] == SC_PRODUCT_GROUP) {
            $arrDataGroup = [];
            foreach ($productInGroup as $pID) {
                if ((int) $pID) {
                    $arrDataGroup[$pID] = new ShopProductGroup(['product_id' => $pID]);
                }
            }
            $product->groups()->saveMany($arrDataGroup);
        }

        //Insert Build
        if ($productBuild && $data['kind'] == SC_PRODUCT_BUILD) {
            $arrDataBuild = [];
            foreach ($productBuild as $key => $pID) {
                if ((int) $pID) {
                    $arrDataBuild[$pID] = new ShopProductBuild(['product_id' => $pID, 'quantity' => $productBuildQty[$key]]);
                }
            }
            $product->builds()->saveMany($arrDataBuild);
        }

        //Insert attribute
        if ($attribute && $data['kind'] == SC_PRODUCT_SINGLE) {
            $arrDataAtt = [];
            foreach ($attribute as $group => $rowGroup) {
                if (count($rowGroup)) {
                    foreach ($rowGroup['name'] as $key => $nameAtt) {
                        if ($nameAtt) {
                            $arrDataAtt[] = new ShopProductAttribute(['name' => $nameAtt, 'add_price' => $rowGroup['add_price'][$key],  'attribute_group_id' => $group]);
                        }
                    }
                }

            }
            $product->attributes()->saveMany($arrDataAtt);
        }


        //Insert path download
        if (!empty($data['property']) && $data['property'] == SC_PROPERTY_DOWNLOAD && $downloadPath) {
            (new ShopProductDownload)->insert(['product_id' => $product->id, 'path' => $downloadPath]);
        }

        //Insert custom fields
        if (!empty($data['fields'])) {
            $dataField = [];
            foreach ($data['fields'] as $key => $value) {
                $field = (new ShopCustomField)->where('code', $key)->where('type', 'product')->first();
                if ($field) {
                    $dataField[] = [
                        'custom_field_id' => $field->id,
                        'rel_id' => $product->id,
                        'text' => trim($value),
                    ];
                }
            }
            if ($dataField) {
                (new ShopCustomFieldDetail)->insert($dataField);
            }
        }


        //Insert description
        $dataDes = [];
        $languages = $this->languages;
        foreach ($languages as $code => $value) {
            $dataDes[] = [
                'product_id'  => $product->id,
                'lang'        => $code,
                'name'        => $descriptions[$code]['name'],
                'keyword'     => $descriptions[$code]['keyword'],
                'description' => $descriptions[$code]['description'],
                'content'     => $descriptions[$code]['content'] ?? '',
            ];
        }

        AdminProduct::insertDescriptionAdmin($dataDes);

        //Insert sub mages
        if ($subImages && in_array($data['kind'], [SC_PRODUCT_SINGLE, SC_PRODUCT_BUILD])) {
            $arrSubImages = [];
            foreach ($subImages as $key => $image) {
                if ($image) {
                    $arrSubImages[] = new ShopProductImage(['image' => $image]);
                }
            }
            $product->images()->saveMany($arrSubImages);
        }
        
        //Store
        $shopStore        = [session('adminStoreId')];
        $product->stores()->detach();
        if ($shopStore) {
            $product->stores()->attach($shopStore);
        }

        //Category vendor
        $modePTCVendor = (new VendorProductCategory);
        $modePTCVendor->where('product_id', $product->id)->delete();
        $modePTCVendor->insert(['product_id' => $product->id, 'vendor_category_id' => $data['vendor_category_id']]);

        sc_clear_cache('cache_product');

        return redirect()->route('admin_mvendor_product.index')->with('success', sc_language_render('action.create_success'));

    }

    /*
    * Form edit
    */
    public function edit($id)
    {
        $product = (new AdminProduct)->getProductAdmin($id, session('adminStoreId'));
        $categories = (new AdminCategory)->getTreeCategoriesAdmin();

        if ($product === null) {
            return redirect()->route('admin.data_not_found')->with(['url' => url()->full()]);
        }

        $listProductSingle = (new AdminProduct)->getProductSelectAdmin(['kind' => [0]], session('adminStoreId'));

        // html select product group
        $htmlSelectGroup = '<div class="select-product">';
        $htmlSelectGroup .= '<table width="100%"><tr><td width="80%"><select class="form-control rounded-0 productInGroup select2" data-placeholder="' . sc_language_render('product.admin.select_product_in_group') . '" style="width: 100%;" name="productInGroup[]" >';
        $htmlSelectGroup .= '';
        foreach ($listProductSingle as $k => $v) {
            $htmlSelectGroup .= '<option value="' . $k . '">' . $v['name'] . '</option>';
        }
        $htmlSelectGroup .= '</select></td><td><span title="Remove" class="btn btn-flat btn-danger removeproductInGroup"><i class="fa fa-times"></i></span></td></tr></table>';
        $htmlSelectGroup .= '</div>';
        //End select product group

        // html select product build
        $htmlSelectBuild = '<div class="select-product">';
        $htmlSelectBuild .= '<table width="100%"><tr><td width="70%"><select class="form-control rounded-0 productInGroup select2" data-placeholder="' . sc_language_render('product.admin.select_product_in_build') . '" style="width: 100%;" name="productBuild[]" >';
        $htmlSelectBuild .= '';
        foreach ($listProductSingle as $k => $v) {
            $htmlSelectBuild .= '<option value="' . $k . '">' . $v['name'] . '</option>';
        }
        $htmlSelectBuild .= '</select></td><td style="width:100px"><input class="form-control rounded-0"  type="number" name="productBuildQty[]" value="1" min=1></td><td><span title="Remove" class="btn btn-flat btn-danger removeproductBuild"><i class="fa fa-times"></i></span></td></tr></table>';
        $htmlSelectBuild .= '</div>';
        //end select product build

        // html select attribute
        $htmlProductAtrribute = '<tr><td><br><input type="text" name="attribute[attribute_group][name][]" value="attribute_value" class="form-control rounded-0 input-sm" placeholder="' . sc_language_render('product.admin.add_attribute_place') . '" /></td><td><br><input type="number" name="attribute[attribute_group][add_price][]" value="add_price_value" class="form-control rounded-0 input-sm" placeholder="' . sc_language_render('product.admin.add_price_place') . '"></td><td><br><span title="Remove" class="btn btn-flat btn-danger removeAttribute"><i class="fa fa-times"></i></span></td></tr>';
        //end select attribute

        if (function_exists('sc_vendor_get_categories_admin')) {
            $categoriesStore = sc_vendor_get_categories_admin();
        } else {
            $categoriesStore = [];
        }
        
        //Get category vendor
        $modePTCVendor = (new VendorProductCategory)->where('product_id', $id)->first();
        $vendor_category_id = 0;
        if ($modePTCVendor) {
            $vendor_category_id = $modePTCVendor->vendor_category_id;
        }       

        $data = [
            'title'                => sc_language_render('product.admin.edit'),
            'subTitle'             => '',
            'title_description'    => '',
            'icon'                 => 'fa fa-edit',
            'languages'            => $this->languages,
            'product'              => $product,
            'vendor_category_id'   => $vendor_category_id,
            'categoriesStore'      => $categoriesStore,
            'categories'           => $categories,
            'brands'               => (new ShopBrand)->getListAll(),
            'suppliers'            => (new ShopSupplier)->getListAll(),
            'taxs'                 => (new ShopTax)->getListAll(),
            'properties'            => $this->properties,
            'kinds'                => $this->kinds(),
            'attributeGroup'       => $this->attributeGroup,
            'htmlSelectGroup'      => $htmlSelectGroup,
            'htmlSelectBuild'      => $htmlSelectBuild,
            'listProductSingle'    => $listProductSingle,
            'htmlProductAtrribute' => $htmlProductAtrribute,
            'listWeight'           => $this->listWeight,
            'listLength'           => $this->listLength,  

        ];
        //Only prduct single have custom field
        if ($product->kind == SC_PRODUCT_SINGLE) {
            $data['customFields'] = (new ShopCustomField)->getCustomField($type = 'product');
        } else {
            $data['customFields'] = [];
        }

        return view($this->plugin->pathPlugin.'::Admin.screen.vendor.product_edit')
            ->with($data);
    }

    /*
    * update status
    */
    public function postEdit($id)
    {
        $product = (new AdminProduct)->getProductAdmin($id, session('adminStoreId'));
        if ($product === null) {
            return redirect()->route('admin.data_not_found')->with(['url' => url()->full()]);
        }
        $data = request()->all();
        $langFirst = array_key_first(sc_language_all()->toArray()); //get first code language active
        $data['alias'] = !empty($data['alias'])?$data['alias']:$data['descriptions'][$langFirst]['name'];
        $data['alias'] = sc_word_format_url($data['alias']);
        $data['alias'] = sc_word_limit($data['alias'], 100);

        switch ($product['kind']) {
            case SC_PRODUCT_SINGLE: // product single
                $arrValidation = [
                    'sort' => 'numeric|min:0',
                    'minimum' => 'numeric|min:0',
                    'descriptions.*.name' => 'required|string|max:200',
                    'descriptions.*.keyword' => 'nullable|string|max:200',
                    'descriptions.*.description' => 'nullable|string|max:300',
                    'descriptions.*.content' => 'required|string',
                    'category' => 'required',
                    'sku' => 'required|regex:/(^([0-9A-Za-z\-_]+)$)/|product_sku_unique:'.$id,
                    'alias' => 'required|regex:/(^([0-9A-Za-z\-_]+)$)/|string|max:120|product_alias_unique:'.$id,
                ];

                //Custom fields
                $customFields = (new ShopCustomField)->getCustomField($type = 'product');
                if ($customFields) {
                    foreach ($customFields as $field) {
                        if ($field->required) {
                            $arrValidation['fields.'.$field->code] = 'required';
                        }
                    }
                }
                $arrValidation = $this->validateAttribute($arrValidation);

                $arrMsg = [
                    'descriptions.*.name.required'    => sc_language_render('validation.required', ['attribute' => sc_language_render('product.name')]),
                    'descriptions.*.content.required' => sc_language_render('validation.required', ['attribute' => sc_language_render('product.content')]),
                    'category.required'               => sc_language_render('validation.required', ['attribute' => sc_language_render('product.category')]),
                    'sku.regex'                       => sc_language_render('product.sku_validate'),
                    'sku.product_sku_unique'          => sc_language_render('product.sku_unique'),
                    'alias.regex'                     => sc_language_render('product.alias_validate'),
                    'alias.product_alias_unique'      => sc_language_render('product.alias_unique'),
                ];
                break;
            case SC_PRODUCT_BUILD: //product build
                $arrValidation = [
                    'sort' => 'numeric|min:0',
                    'minimum' => 'numeric|min:0',
                    'descriptions.*.name' => 'required|string|max:200',
                    'descriptions.*.keyword' => 'nullable|string|max:200',
                    'descriptions.*.description' => 'nullable|string|max:300',
                    'category' => 'required',
                    'sku' => 'required|regex:/(^([0-9A-Za-z\-_]+)$)/|product_sku_unique:'.$id,
                    'alias' => 'required|regex:/(^([0-9A-Za-z\-_]+)$)/|string|max:120|product_alias_unique:'.$id,
                    'productBuild' => 'required',
                    'productBuildQty' => 'required',
                ];

                $arrValidation = $this->validateAttribute($arrValidation);
                
                $arrMsg = [
                    'descriptions.*.name.required' => sc_language_render('validation.required', ['attribute' => sc_language_render('product.name')]),
                    'category.required'            => sc_language_render('validation.required', ['attribute' => sc_language_render('product.category')]),
                    'sku.regex'                    => sc_language_render('product.sku_validate'),
                    'sku.product_sku_unique'       => sc_language_render('product.sku_unique'),
                    'alias.regex'                  => sc_language_render('product.alias_validate'),
                    'alias.product_alias_unique'   => sc_language_render('product.alias_unique'),
                ];
                break;

            case SC_PRODUCT_GROUP: //product group
                $arrValidation = [
                    'sku' => 'required|regex:/(^([0-9A-Za-z\-_]+)$)/|product_sku_unique:'.$id,
                    'alias' => 'required|regex:/(^([0-9A-Za-z\-_]+)$)/|string|max:120|product_alias_unique:'.$id,
                    'productInGroup' => 'required',
                    'sort' => 'numeric|min:0',
                    'minimum' => 'numeric|min:0',
                    'descriptions.*.name' => 'required|string|max:200',
                    'descriptions.*.keyword' => 'nullable|string|max:200',
                    'descriptions.*.description' => 'nullable|string|max:300',
                ];
                $arrMsg = [
                    'sku.regex'                    => sc_language_render('product.sku_validate'),
                    'sku.product_sku_unique'       => sc_language_render('product.sku_unique'),
                    'alias.regex'                  => sc_language_render('product.alias_validate'),
                    'alias.product_alias_unique'   => sc_language_render('product.alias_unique'),
                    'descriptions.*.name.required' => sc_language_render('validation.required', ['attribute' => sc_language_render('product.name')]),
                ];
                break;

            default:
                break;
        }

        $validator = Validator::make($data, $arrValidation, $arrMsg ?? []);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput($data);
        }
        //Edit

        $category        = $data['category'] ?? [];
        $attribute       = $data['attribute'] ?? [];
        $productInGroup  = $data['productInGroup'] ?? [];
        $productBuild    = $data['productBuild'] ?? [];
        $productBuildQty = $data['productBuildQty'] ?? [];
        $subImages       = $data['sub_image'] ?? [];
        $downloadPath    = $data['download_path'] ?? '';
        $dataUpdate = [
            'image'        => $data['image'] ?? '',
            'tax_id'       => $data['tax_id'] ?? 0,
            'brand_id'     => $data['brand_id'] ?? 0,
            'supplier_id'  => $data['supplier_id'] ?? 0,
            'price'        => $data['price'] ?? 0,
            'cost'         => $data['cost'] ?? 0,
            'stock'        => $data['stock'] ?? 0,
            'weight_class' => $data['weight_class'] ?? '',
            'length_class' => $data['length_class'] ?? '',
            'weight'       => $data['weight'] ?? 0,
            'height'       => $data['height'] ?? 0,
            'length'       => $data['length'] ?? 0,
            'width'        => $data['width'] ?? 0,
            'property'     => $data['property'] ?? SC_PROPERTY_PHYSICAL,
            'sku'          => $data['sku'],
            'alias'        => $data['alias'],
            'status'       => (!empty($data['status']) ? 1 : 0),
            'sort'         => (int) $data['sort'],
            'minimum'      => (int) $data['minimum'],
        ];
        if (!empty($data['date_available'])) {
            $dataUpdate['date_available'] = $data['date_available'];
        }
        $product->update($dataUpdate);


        //Update custom field
        if (!empty($data['fields'])) {
            (new ShopCustomFieldDetail)
                ->join(SC_DB_PREFIX.'shop_custom_field', SC_DB_PREFIX.'shop_custom_field.id', SC_DB_PREFIX.'shop_custom_field_detail.custom_field_id')
                ->select('code', 'name', 'text')
                ->where(SC_DB_PREFIX.'shop_custom_field_detail.rel_id', $product->id)
                ->where(SC_DB_PREFIX.'shop_custom_field.type', 'product')
                ->delete();

            $dataField = [];
            foreach ($data['fields'] as $key => $value) {
                $field = (new ShopCustomField)->where('code', $key)->where('type', 'product')->first();
                if ($field) {
                    $dataField[] = [
                        'custom_field_id' => $field->id,
                        'rel_id' => $product->id,
                        'text' => trim($value),
                    ];
                }
            }
            if ($dataField) {
                (new ShopCustomFieldDetail)->insert($dataField);
            }
        }

        //Update path download
        (new ShopProductDownload)->where('product_id', $product->id)->delete();
        if ($product['property'] == SC_PROPERTY_DOWNLOAD && $downloadPath) {
            (new ShopProductDownload)->insert(['product_id' => $product->id, 'path' => $downloadPath]);
        }

        //Promoton price
        $product->promotionPrice()->delete();
        if (isset($data['price_promotion']) && in_array($product['kind'], [SC_PRODUCT_SINGLE, SC_PRODUCT_BUILD])) {
            $arrPromotion['price_promotion'] = $data['price_promotion'];
            $arrPromotion['date_start'] = $data['price_promotion_start'] ? $data['price_promotion_start'] : null;
            $arrPromotion['date_end'] = $data['price_promotion_end'] ? $data['price_promotion_end'] : null;
            $product->promotionPrice()->create($arrPromotion);
        }

        $product->descriptions()->delete();
        $dataDes = [];
        foreach ($data['descriptions'] as $code => $row) {
            $dataDes[] = [
                'product_id' => $id,
                'lang' => $code,
                'name' => $row['name'],
                'keyword' => $row['keyword'],
                'description' => $row['description'],
                'content' => $row['content'] ?? '',
            ];
        }
        AdminProduct::insertDescriptionAdmin($dataDes);

        //Update category
        if (in_array($product['kind'], [SC_PRODUCT_SINGLE, SC_PRODUCT_BUILD])) {
            $product->categories()->detach();
            if (count($category)) {
                $product->categories()->attach($category);
            }

        }

        //Update group
        if ($product['kind'] == SC_PRODUCT_GROUP) {
            $product->groups()->delete();
            if (count($productInGroup)) {
                $arrDataGroup = [];
                foreach ($productInGroup as $pID) {
                    if ((int) $pID) {
                        $arrDataGroup[$pID] = new ShopProductGroup(['product_id' => $pID]);
                    }
                }
                $product->groups()->saveMany($arrDataGroup);
            }

        }

        //Update Build
        if ($product['kind'] == SC_PRODUCT_BUILD) {
            $product->builds()->delete();
            if (count($productBuild)) {
                $arrDataBuild = [];
                foreach ($productBuild as $key => $pID) {
                    if ((int) $pID) {
                        $arrDataBuild[$pID] = new ShopProductBuild(['product_id' => $pID, 'quantity' => $productBuildQty[$key]]);
                    }
                }
                $product->builds()->saveMany($arrDataBuild);
            }

        }

        //Update attribute
        if ($product['kind'] == SC_PRODUCT_SINGLE) {
            $product->attributes()->delete();
            if (count($attribute)) {
                $arrDataAtt = [];
                foreach ($attribute as $group => $rowGroup) {
                    if (count($rowGroup)) {
                        foreach ($rowGroup['name'] as $key => $nameAtt) {
                            if ($nameAtt) {
                                $arrDataAtt[] = new ShopProductAttribute(['name' => $nameAtt, 'add_price' => $rowGroup['add_price'][$key], 'attribute_group_id' => $group]);
                            }
                        }
                    }

                }
                $product->attributes()->saveMany($arrDataAtt);
            }

        }

        //Update sub mages
        if ($subImages && in_array($product['kind'], [SC_PRODUCT_SINGLE, SC_PRODUCT_BUILD])) {
            $product->images()->delete();
            $arrSubImages = [];
            foreach ($subImages as $key => $image) {
                if ($image) {
                    $arrSubImages[] = new ShopProductImage(['image' => $image]);
                }
            }
            $product->images()->saveMany($arrSubImages);
        }

        //Store
        $shopStore        = [session('adminStoreId')];
        $product->stores()->detach();
        if ($shopStore) {
            $product->stores()->attach($shopStore);
        }

        //Category vendor
        $modePTCVendor = (new VendorProductCategory);
        $modePTCVendor->where('product_id', $product->id)->delete();
        $modePTCVendor->insert(['product_id' => $product->id, 'vendor_category_id' => $data['vendor_category_id']]);
        

        sc_clear_cache('cache_product');

        return redirect()->route('admin_mvendor_product.index')->with('success', sc_language_render('action.edit_success'));

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
            $arrCantDelete = [];
            $arrDontPermission = [];
            foreach ($arrID as $key => $id) {
                if(!$this->checkPermisisonItem($id)) {
                    $arrDontPermission[] = $id;
                }
                if (ShopProductBuild::where('product_id', $id)->first() || ShopProductGroup::where('product_id', $id)->first()) {
                    $arrCantDelete[] = $id;
                }
            }
            if (count($arrDontPermission)) {
                return response()->json(['error' => 1, 'msg' => sc_language_render('admin.remove_dont_permisison') . ': ' . json_encode($arrDontPermission)]);
            } elseif (count($arrCantDelete)) {
                return response()->json(['error' => 1, 'msg' => sc_language_render('product.admin.cant_remove_child') . ': ' . json_encode($arrCantDelete)]);
            }else {
                AdminProduct::destroy($arrID);

                sc_clear_cache('cache_product');

                return response()->json(['error' => 0, 'msg' => '']);
            }

        }
    }

    /**
     * Validate attribute product
     */
    public function validateAttribute(array $arrValidation) {
        if (sc_config_admin('product_brand')) {
            if (sc_config_admin('product_brand_required')) {
                $arrValidation['brand_id'] = 'required';
            } else {
                $arrValidation['brand_id'] = 'nullable';
            }
        }

        if (sc_config_admin('product_supplier')) {
            if (sc_config_admin('product_supplier_required')) {
                $arrValidation['supplier_id'] = 'required';
            } else {
                $arrValidation['supplier_id'] = 'nullable';
            }
        }

        if (sc_config_global('MultiVendor')) {
            $arrValidation['vendor_category_id'] = 'required';
        }

        if (sc_config_admin('product_price')) {
            if (sc_config_admin('product_price_required')) {
                $arrValidation['price'] = 'required|numeric|min:0';
            } else {
                $arrValidation['price'] = 'nullable|numeric|min:0';
            }
        }

        if (sc_config_admin('product_cost')) {
            if (sc_config_admin('product_cost_required')) {
                $arrValidation['cost'] = 'required|numeric|min:0';
            } else {
                $arrValidation['cost'] = 'nullable|numeric|min:0';
            }
        }

        if (sc_config_admin('product_promotion')) {
            if (sc_config_admin('product_promotion_required')) {
                $arrValidation['price_promotion'] = 'required|numeric|min:0';
            } else {
                $arrValidation['price_promotion'] = 'nullable|numeric|min:0';
            }
        }

        if (sc_config_admin('product_stock')) {
            if (sc_config_admin('product_stock_required')) {
                $arrValidation['stock'] = 'required|numeric';
            } else {
                $arrValidation['stock'] = 'nullable|numeric';
            }
        }

        if (sc_config_admin('product_property')) {
            if (sc_config_admin('product_property_required')) {
                $arrValidation['property'] = 'required|string';
            } else {
                $arrValidation['property'] = 'nullable|string';
            }
        }

        if (sc_config_admin('product_available')) {
            if (sc_config_admin('product_available_required')) {
                $arrValidation['date_available'] = 'required|date';
            } else {
                $arrValidation['date_available'] = 'nullable|date';
            }
        }

        if (sc_config_admin('product_weight')) {
            if (sc_config_admin('product_weight_required')) {
                $arrValidation['weight'] = 'required|numeric';
                $arrValidation['weight_class'] = 'required|string';
            } else {
                $arrValidation['weight'] = 'nullable|numeric';
                $arrValidation['weight_class'] = 'nullable|string';
            }
        }

        if (sc_config_admin('product_length')) {
            if (sc_config_admin('product_length_required')) {
                $arrValidation['length_class'] = 'required|string';
                $arrValidation['length'] = 'required|numeric|min:0';
                $arrValidation['width'] = 'required|numeric|min:0';
                $arrValidation['height'] = 'required|numeric|min:0';
            } else {
                $arrValidation['length_class'] = 'nullable|string';
                $arrValidation['length'] = 'nullable|numeric|min:0';
                $arrValidation['width'] = 'nullable|numeric|min:0';
                $arrValidation['height'] = 'nullable|numeric|min:0';
            }
        }
        return $arrValidation;
    }

    /**
     * Check permisison item
     */
    public function checkPermisisonItem($id) {
        return (new AdminProduct)->getProductAdmin($id, session('adminStoreId'));
    }
}
