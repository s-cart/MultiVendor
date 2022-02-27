<?php

namespace App\Plugins\Other\MultiVendor\Admin\Models;

use App\Plugins\Other\MultiVendor\Models\VendorCategory;
use App\Plugins\Other\MultiVendor\Models\VendorCategoryDescription;
use Cache;

class AdminVendorCategory extends VendorCategory
{
    protected static $getListTitleAdmin = null;
    protected static $getListVendorCategoryGroupByParentAdmin = null;
    /**
     * Get category detail in admin
     *
     * @param   [type]  $id  [$id description]
     *
     * @return  [type]       [return description]
     */
    public static function getVendorCategoryAdmin($id) {
        return self::where('id', $id)
        ->first();
    }

    /**
     * Get list category in admin
     *
     * @param   [array]  $dataSearch  [$dataSearch description]
     *
     * @return  [type]               [return description]
     */
    public static function getVendorCategoryListAdmin(array $dataSearch) {
        $keyword          = $dataSearch['keyword'] ?? '';
        $sort_order       = $dataSearch['sort_order'] ?? '';
        $arrSort          = $dataSearch['arrSort'] ?? '';
        $tableDescription = (new VendorCategoryDescription)->getTable();
        $tableVendorCategory     = (new VendorCategory)->getTable();

        $categoryList = (new VendorCategory)
            ->leftJoin($tableDescription, $tableDescription . '.vendor_category_id', $tableVendorCategory . '.id')
            ->where($tableVendorCategory.'.store_id', session('adminStoreId'))
            ->where($tableDescription . '.lang', sc_get_locale());

        if ($keyword) {
            $categoryList = $categoryList->where(function ($sql) use($tableDescription, $keyword){
                $sql->where($tableDescription . '.title', 'like', '%' . $keyword . '%')
                    ->orWhere($tableDescription . '.keyword', 'like', '%' . $keyword . '%')
                    ->orWhere($tableDescription . '.description', 'like', '%' . $keyword . '%');
            });
        }

        if ($sort_order && array_key_exists($sort_order, $arrSort)) {
            $field = explode('__', $sort_order)[0];
            $sort_field = explode('__', $sort_order)[1];
            $categoryList = $categoryList->sort($field, $sort_field);
        } else {
            $categoryList = $categoryList->sort('id', 'desc');
        }
        $categoryList = $categoryList->paginate(20);

        return $categoryList;
    }


    /**
     * Get array title category
     * user for admin 
     *
     * @return  [type]  [return description]
     */
    public static function getCategoriesAdmin()
    {
        $tableDescription = (new VendorCategoryDescription)->getTable();
        $table = (new AdminVendorCategory)->getTable();
        if (sc_config_global('cache_status') && sc_config_global('cache_category_vendor')) {
            if (!Cache::has(session('adminStoreId').'_cache_category_vendor_'.sc_get_locale())) {
                if (self::$getListTitleAdmin === null) {
                    self::$getListTitleAdmin = self::join($tableDescription, $tableDescription.'.vendor_category_id', $table.'.id')
                    ->where($table.'.store_id', session('adminStoreId'))
                    ->where('lang', sc_get_locale())
                    ->pluck('title', 'id')
                    ->toArray();
                }
                sc_set_cache(session('adminStoreId').'_cache_category_vendor_'.sc_get_locale(), self::$getListTitleAdmin);
            }
            return Cache::get(session('adminStoreId').'_cache_category_vendor_'.sc_get_locale());
        } else {
            if (self::$getListTitleAdmin === null) {
                self::$getListTitleAdmin = self::join($tableDescription, $tableDescription.'.vendor_category_id', $table.'.id')
                ->where('lang', sc_get_locale())
                ->where($table.'.store_id', session('adminStoreId'))
                ->pluck('title', 'id')
                ->toArray();
            }
            return self::$getListTitleAdmin;
        }
    }


    /**
     * Create a new category
     *
     * @param   array  $dataInsert  [$dataInsert description]
     *
     * @return  [type]              [return description]
     */
    public static function createVendorCategoryAdmin(array $dataInsert) {
        $dataInsert = sc_clean($dataInsert);
        return self::create($dataInsert);
    }


    /**
     * Insert data description
     *
     * @param   array  $dataInsert  [$dataInsert description]
     *
     * @return  [type]              [return description]
     */
    public static function insertDescriptionAdmin(array $dataInsert) {
        $dataInsert = sc_clean($dataInsert);
        return VendorCategoryDescription::create($dataInsert);
    }

}
