<?php
namespace App\Plugins\Other\MultiVendor\Admin\Models;


use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Model;
use SCart\Core\Front\Models\ShopStore;

class AdminMoneyProcess extends Model
{
    use \SCart\Core\Front\Models\UuidTrait;

    public $table = 'vendor_money_process';
    protected $guarded = [];
    protected $connection = SC_CONNECTION;

    public function store()
    {
        return $this->belongsTo(ShopStore::class, 'store_id', 'id');
    }

    protected static function boot()
    {
        parent::boot();
        // before delete() method call this
        static::deleting(function ($model) {
            //
        });

        //Uuid
        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = sc_generate_id($type = 'vendor_money_process');
            }
        });
    }


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
            $table->uuid('id')->primary();
            $table->string('content', 255)->nullable();
            $table->string('comment', 255)->nullable();
            $table->uuid('store_id')->index();
            $table->integer('total_sum')->default(0);
            $table->integer('order_count')->default(0);
            $table->integer('amount')->default(0);
            $table->integer('commission_rate')->default(0);
            $table->string('currency')->index();
            $table->date('date_process')->index();
            $table->string('status', 50)->default('processing')->comment('processing,pending,canceled,done')->index();
            $table->date('date_pay')->nullable()->index();
            $table->timestamps();
        });
        
    }

}
