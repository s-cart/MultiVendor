<?php

/**
 * Admin mamanager multi-vendor
 */
Route::group(
    [
        'prefix' => SC_ADMIN_PREFIX.'/MultiVendor',
        'middleware' => SC_ADMIN_MIDDLEWARE,
        'namespace' => 'App\Plugins\Other\MultiVendor\Admin\Controllers\Root',
    ], 
    function () {
        //Store
        Route::group(['prefix' => 'store'], function () {
            Route::get('/', 'AdminRootVendorStoreController@index')
            ->name('admin_MultiVendor.index');
            Route::get('/create', 'AdminRootVendorStoreController@create')
            ->name('admin_MultiVendor.create');
            Route::post('/create', 'AdminRootVendorStoreController@postCreate');
            Route::post('/delete', 'AdminRootVendorStoreController@delete')
            ->name('admin_MultiVendor.delete');
            Route::get('/config/{id}', 'AdminRootVendorStoreController@config')
            ->name('admin_MultiVendor.config');
        });

        //Vendor
        Route::group(['prefix' => 'vendor'], function () {
            Route::get('/', 'AdminRootVendorUserController@index')
            ->name('admin_MultiVendorUser.index');
            Route::get('/create', 'AdminRootVendorUserController@create');
            Route::post('/create', 'AdminRootVendorUserController@postCreate')
                ->name('admin_MultiVendorUser.create');
            Route::get('/edit/{id}', 'AdminRootVendorUserController@edit')
                ->name('admin_MultiVendorUser.edit');
            Route::post('/edit/{id}', 'AdminRootVendorUserController@postEdit');
            Route::post('/delete', 'AdminRootVendorUserController@deleteList')
                ->name('admin_MultiVendorUser.delete');
        });

        //Config
        Route::group(['prefix' => 'config'], function () {
            Route::get('/', 'AdminRootVendorConfigController@index')
                ->name('admin_MultiVendorConfig.index');
        });
        
        //Report
        Route::any('/report', 'AdminRootVendorReportController@index')
        ->name('admin_MultiVendorReport.index');
    }
);

if (sc_config_global('MultiVendor') && sc_get_domain_root() == sc_process_domain_store(url('/'))) {
    /**
     * vendor manager
     * Only allow on domain root
     */

    Route::group(
        [
            'prefix' => config('MultiVendor.admin_path'),
            'middleware' => ['web', 'vendor'],
            'namespace' => 'App\Plugins\Other\MultiVendor\Admin\Controllers',
        ], 
        function () {

            if (sc_config_global('MultiVendor_allow_register')) {
                Route::get('register', 'Auth\RegisterController@showRegister')->name('vendor.register');
                Route::post('register', 'Auth\RegisterController@postRegister')->name('vendor.register');
            }
            Route::get('forgot', 'Auth\ForgotPasswordController@getForgot')->name('vendor.forgot');
            Route::post('forgot', 'Auth\ForgotPasswordController@sendRepostForgotsetLinkEmail')->name('vendor.forgot');
            Route::get('password/reset/{token}', 'Auth\ResetPasswordController@formResetPassword')->name('vendor.password_reset');
            Route::post('password/reset', 'Auth\ResetPasswordController@reset')->name('vendor.post_password_reset');
            Route::get('setting', 'Auth\LoginController@getSetting')->name('vendor.setting');
            Route::post('setting', 'Auth\LoginController@putSetting')->name('vendor.setting');
            Route::get('login', 'Auth\LoginController@getLogin')->name('vendor.login');
            Route::post('login', 'Auth\LoginController@postLogin')->name('vendor.login');
            Route::get('logout', 'Auth\LoginController@getLogout')->name('vendor.logout');
        }
    );
    
    Route::group(
        [
            'prefix' => config('MultiVendor.admin_path'),
            'middleware' => ['web', 'vendor'],
            'namespace' => 'App\Plugins\Other\MultiVendor\Admin\Controllers\Vendor',
        ], 
        function () {

            //Language
            Route::get('locale/{code}', function ($code) {
                session(['locale' => $code]);
                return back();
            })->name('vendor_admin.locale');

            Route::get('deny', 'DashboardVendorController@deny')->name('vendor_admin.deny');
            Route::get('data_not_found', 'DashboardVendorController@dataNotFound')->name('vendor_admin.data_not_found');
            Route::get('deny_single', 'DashboardVendorController@denySingle')->name('vendor_admin.deny_single');
            Route::get('account_inactive', 'DashboardVendorController@accountInactive')->name('vendor_admin.account_inactive');


            Route::group(['middleware' => ['checkVendorActive', 'checkStoreExist']], function () {

                Route::get('/', 'DashboardVendorController@index')->name('vendor_admin.home');

                if (starts_with(request()->path(), 'vendor_admin')) {
                    Route::group(['prefix' => 'uploads', 'namespace' => '\\UniSharp\\LaravelFilemanager\\Controllers\\'], function () {

                        // display main layout
                        Route::get('/', [
                            'uses' => 'LfmController@show',
                            'as' => 'unisharp.lfm.show',
                        ]);
                    
                        // display integration error messages
                        Route::get('/errors', [
                            'uses' => 'LfmController@getErrors',
                            'as' => 'unisharp.lfm.getErrors',
                        ]);
                    
                        // upload
                        Route::post('/upload', [
                            'uses' => 'UploadController@upload',
                            'as' => 'unisharp.lfm.upload',
                        ]);
                    
                        // list images & files
                        Route::get('/jsonitems', [
                            'uses' => 'ItemsController@getItems',
                            'as' => 'unisharp.lfm.getItems',
                        ]);
                    
                        Route::get('/move', [
                            'uses' => 'ItemsController@move',
                            'as' => 'unisharp.lfm.move',
                        ]);
                    
                        Route::get('/domove', [
                            'uses' => 'ItemsController@domove',
                            'as' => 'unisharp.lfm.domove',
                        ]);
                    
                        // folders
                        Route::get('/newfolder', [
                            'uses' => 'FolderController@getAddfolder',
                            'as' => 'unisharp.lfm.getAddfolder',
                        ]);
                    
                        // list folders
                        Route::get('/folders', [
                            'uses' => 'FolderController@getFolders',
                            'as' => 'unisharp.lfm.getFolders',
                        ]);
                    
                        // crop
                        Route::get('/crop', [
                            'uses' => 'CropController@getCrop',
                            'as' => 'unisharp.lfm.getCrop',
                        ]);
                        Route::get('/cropimage', [
                            'uses' => 'CropController@getCropimage',
                            'as' => 'unisharp.lfm.getCropimage',
                        ]);
                        Route::get('/cropnewimage', [
                            'uses' => 'CropController@getNewCropimage',
                            'as' => 'unisharp.lfm.getCropimage',
                        ]);
                    
                        // rename
                        Route::get('/rename', [
                            'uses' => 'RenameController@getRename',
                            'as' => 'unisharp.lfm.getRename',
                        ]);
                    
                        // scale/resize
                        Route::get('/resize', [
                            'uses' => 'ResizeController@getResize',
                            'as' => 'unisharp.lfm.getResize',
                        ]);
                        Route::get('/doresize', [
                            'uses' => 'ResizeController@performResize',
                            'as' => 'unisharp.lfm.performResize',
                        ]);
                    
                        // download
                        Route::get('/download', [
                            'uses' => 'DownloadController@getDownload',
                            'as' => 'unisharp.lfm.getDownload',
                        ]);
                    
                        // delete
                        Route::get('/delete', [
                            'uses' => 'DeleteController@getDelete',
                            'as' => 'unisharp.lfm.getDelete',
                        ]);
                    });
                }

                Route::group(['prefix' => '/order'], function () {
                        Route::get('/', 'AdminVendorOrderController@index')->name('admin_mvendor_order.index');
                        Route::get('/detail/{id}', 'AdminVendorOrderController@edit')->name('admin_mvendor_order.detail');
                        Route::post('/update', 'AdminVendorOrderController@postOrderUpdate')->name('admin_mvendor_order.update');
                    }
                );
                Route::group(['prefix' => '/category'], function () {
                        Route::get('/', 'AdminVendorCategoryController@index')->name('admin_mvendor_category.index');
                        Route::get('create', 'AdminVendorCategoryController@create')->name('admin_mvendor_category.create');
                        Route::post('/create', 'AdminVendorCategoryController@postCreate')->name('admin_mvendor_category.create');
                        Route::get('/edit/{id}', 'AdminVendorCategoryController@edit')->name('admin_mvendor_category.edit');
                        Route::post('/edit/{id}', 'AdminVendorCategoryController@postEdit')->name('admin_mvendor_category.edit');
                        Route::post('/delete', 'AdminVendorCategoryController@deleteList')->name('admin_mvendor_category.delete');
                    }
                );

                Route::group(['prefix' => 'product'], function () {
                    Route::get('/', 'AdminVendorProductController@index')->name('admin_mvendor_product.index');
                    Route::get('create', 'AdminVendorProductController@create')->name('admin_mvendor_product.create');
                    Route::get('build_create', 'AdminVendorProductController@createProductBuild')->name('admin_mvendor_product.build_create');
                    Route::get('group_create', 'AdminVendorProductController@createProductGroup')->name('admin_mvendor_product.group_create');
                    Route::post('/create', 'AdminVendorProductController@postCreate')->name('admin_mvendor_product.create');
                    Route::get('/edit/{id}', 'AdminVendorProductController@edit')->name('admin_mvendor_product.edit');
                    Route::post('/edit/{id}', 'AdminVendorProductController@postEdit')->name('admin_mvendor_product.edit');
                    Route::post('/delete', 'AdminVendorProductController@deleteList')->name('admin_mvendor_product.delete');
                    Route::get('/import', 'AdminVendorProductController@import')->name('admin_mvendor_product.import');
                    Route::post('/import', 'AdminVendorProductController@postImport')->name('admin_mvendor_product.import');
                });

                Route::group(['prefix' => 'banner'], function () {
                    Route::get('/', 'AdminVendorBannerController@index')->name('admin_mvendor_banner.index');
                    Route::get('create', 'AdminVendorBannerController@create')->name('admin_mvendor_banner.create');
                    Route::post('/create', 'AdminVendorBannerController@postCreate')->name('admin_mvendor_banner.create');
                    Route::get('/edit/{id}', 'AdminVendorBannerController@edit')->name('admin_mvendor_banner.edit');
                    Route::post('/edit/{id}', 'AdminVendorBannerController@postEdit')->name('admin_mvendor_banner.edit');
                    Route::post('/delete', 'AdminVendorBannerController@deleteList')->name('admin_mvendor_banner.delete');
                });        

                //vendor create store
                Route::group(
                    [
                        'prefix' => 'vendor_update',
                    ], 
                    function () {
                        Route::get('/', 'AdminVendorInfoController@vendorUpdate')
                            ->name('admin_mvendor_info.update');
                        Route::post('/', 'AdminVendorInfoController@vendorPostUpdate');
                    }
                );
            });           
        }
    );
}

//Front-end
if (sc_config_global('MultiVendor')) {
    // Multi vendor only active for vendor root
    if(config('app.storeId') == SC_ID_ROOT) {
        /**
         * Route shop
         */
        Route::group(
            [
                'prefix' => config('MultiVendor.front_path'),
                'namespace' => 'App\Plugins\Other\MultiVendor\Controllers',
            ], 
            function () {
                Route::get('/{code?}', 'FrontController@vendorDetail')->name('MultiVendor.detail');
            }
        );
    }

    $prefixCategoryvendor = sc_config_global('PREFIX_CATEGORY_VENDOR') ?? 'category-vendor';
    Route::group(
        [
            'prefix' => $prefixCategoryvendor,
            'namespace' => 'App\Plugins\Other\MultiVendor\Controllers',
        ], 
        function () {
            Route::get('/{alias}/{storeId}.html', 'FrontController@categoryVendorDetail')->name('MultiVendor_category.detail');
        }
    );
    
}