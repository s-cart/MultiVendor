<?php
namespace App\Plugins\Other\MultiVendor\Models;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use Illuminate\Database\Eloquent\Model;

class VendorProductCategory extends Model
{
    protected $primaryKey = ['vendor_category_id', 'product_id'];
    public $incrementing  = false;
    protected $guarded    = [];
    public $timestamps    = false;
    public $table = 'vendor_product_category';
    protected $connection = SC_CONNECTION;

    
    public function uninstall()
    {
        if (Schema::hasTable($this->table)) {
            Schema::drop($this->table);
        }
    }

    public function install()
    {
        $this->uninstall();
        Schema::create($this->table, function (Blueprint $table) {
            $table->uuid('vendor_category_id');
            $table->uuid('product_id');
            $table->unique(['vendor_category_id', 'product_id']);
        });
    }
}
