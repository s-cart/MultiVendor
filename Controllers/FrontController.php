<?php
#App\Plugins\Other\MultiVendor\Admin\AdminController.php

namespace App\Plugins\Other\MultiVendor\Controllers;

use SCart\Core\Front\Controllers\RootFrontController;
use App\Plugins\Other\MultiVendor\Models\VendorCategory;
use SCart\Core\Front\Models\ShopProduct;
use App\Plugins\Other\MultiVendor\Models\PluginModel;
use Cart;

class FrontController extends RootFrontController
{
    public function vendorDetail($code = null) {
        $store = PluginModel::getStoreByCode($code);
        if(!$store) {
            return abort(404);
        }
        sc_check_view($this->templatePath . '.vendor.vendor_home');

        return view($this->templatePath . '.vendor.vendor_home',
            array(
                'title'           => $store->getTitle(),
                'keyword'         => $store->getKeyword(),
                'description'     => $store->getDescription(),
                'storeId'         => $store->id,
                'storeCode'       => $store->code,
                'layout_page'     => 'vendor_home',
                'breadcrumbs' => [
                    ['url'    => '', 'title' => $store->getTitle()],
                ],
            )
        );
        
    }

    /**
     * Category detail: list category child + product list
     * @param  [string] $alias
     * @return [view]
     */
    public function categoryVendorDetail($alias, $storeId)
    {
        $sortBy = 'sort';
        $sortOrder = 'asc';
        $filter_sort = request('filter_sort') ?? '';
        $filterArr = [
            'price_desc' => ['price', 'desc'],
            'price_asc' => ['price', 'asc'],
            'sort_desc' => ['sort', 'desc'],
            'sort_asc' => ['sort', 'asc'],
            'id_desc' => ['id', 'desc'],
            'id_asc' => ['id', 'asc'],
        ];
        if (array_key_exists($filter_sort, $filterArr)) {
            $sortBy    = $filterArr[$filter_sort][0];
            $sortOrder = $filterArr[$filter_sort][1];
        }

        $category = (new VendorCategory)->getDetail($alias, $type = 'alias', $storeId);
        if ($category) {
            $products = (new ShopProduct)
                ->getProductToCategoryStore($category->id)
                ->setLimit(sc_config('product_list', $storeId))
                ->setStore($storeId)
                ->setSort([$sortBy, $sortOrder])
                ->setPaginate()
                ->getData();

            sc_check_view($this->templatePath . '.vendor.vendor_product_list');
            return view($this->templatePath . '.vendor.vendor_product_list',
                array(
                    'title'             => $category->title,
                    'description'       => $category->description,
                    'keyword'           => $category->keyword,
                    'products'          => $products,
                    'storeId'           => $storeId,
                    'category'          => $category,
                    'layout_page'       => 'vendor_product_list',
                    'og_image'          => sc_file($category->getImage()),
                    'filter_sort'       => $filter_sort,
                    'breadcrumbs' => [
                        ['url'    => sc_route('MultiVendor.detail', ['code' => $category->store->code]), 'title' => $category->store->getTitle()],
                        ['url'    => '', 'title' => $category->title],
                    ],
                )
            );
        } else {
            return $this->itemNotFound();
        }

    }
}
