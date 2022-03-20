<?php
    $this->loadTranslationsFrom(__DIR__.'/Lang', 'Plugins/Other/MultiVendor');
    $this->loadViewsFrom(__DIR__.'/Views', 'Plugins/Other/MultiVendor');

    if(sc_config_global('MultiVendor')) {
        config(['auth.guards.vendor.driver' => 'session']);
        config(['auth.guards.vendor.provider' => 'vendors']);
        config(['auth.providers.vendors.driver' => 'eloquent']);
        config(['auth.providers.vendors.model' => 'App\Plugins\Other\MultiVendor\Models\VendorUser']);
        config(['auth.passwords.vendors.provider' => 'vendors']);
        config(['auth.passwords.vendors.table' => 'vendor_password_resets']);
        config(['auth.passwords.vendors.expire' => '60']);


        app('router')->aliasMiddleware('checkStoreExist', \App\Plugins\Other\MultiVendor\Middleware\CheckStoreExist::class);
        app('router')->aliasMiddleware('checkVendorActive', \App\Plugins\Other\MultiVendor\Middleware\CheckVendorActive::class);
        app('router')->aliasMiddleware('vendor.auth', \App\Plugins\Other\MultiVendor\Middleware\Authenticate::class);
        app('router')->aliasMiddleware('vendor.storeId', \App\Plugins\Other\MultiVendor\Middleware\AdminStoreId::class);
        app('router')->middlewareGroup('vendor', ['vendor.auth', 'vendor.storeId', 'localization']);

        require_once __DIR__.'/function.php';
        //Path view admin
        view()->share('templatePathAdminVendor', 'Plugins/Other/MultiVendor::Admin.');

        $this->mergeConfigFrom(
            __DIR__.'/config.php', 'MultiVendor'
        );
    }