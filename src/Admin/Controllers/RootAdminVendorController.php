<?php
namespace App\Plugins\Other\MultiVendor\Admin\Controllers;

use SCart\Core\Admin\Controllers\RootAdminController;
use App\Plugins\Other\MultiVendor\AppConfig;
use App\Http\Controllers\Controller;

class RootAdminVendorController extends Controller
{
    public $plugin;
    public $templatePathAdmin;
    public function __construct()
    {
        $this->plugin = new AppConfig;
        $this->templatePathAdmin = (new AppConfig)->pathPlugin.'::Admin.';
    }
}
