<?php
namespace App\Plugins\Other\MultiVendor\Models;

use Illuminate\Database\Eloquent\Model;

class VendorCategoryDescription extends Model
{
    protected $primaryKey = ['vendor_category_id', 'lang'];
    public $incrementing  = false;
    public $timestamps    = false;
    public $table = 'vendor_category_description';
    protected $connection = SC_CONNECTION;
    protected $guarded    = [];
}
