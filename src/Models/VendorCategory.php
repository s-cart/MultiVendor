<?php
namespace App\Plugins\Other\MultiVendor\Models;

use App\Plugins\Other\MultiVendor\Models\VendorCategoryDescription;
use SCart\Core\Front\Models\ShopProduct;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Model;
use SCart\Core\Front\Models\ShopStore;
use SCart\Core\Front\Models\ModelTrait;

class VendorCategory extends Model
{
    use ModelTrait;
    use \SCart\Core\Front\Models\UuidTrait;

    public $table = 'vendor_category';
    public $tableDescription = 'vendor_category_description';
    protected $guarded = [];
    protected $connection = SC_CONNECTION;

    protected  $sc_store = 0; 

    public function products()
    {
        return $this->belongsToMany(ShopProduct::class, 'vendor_product_category', 'vendor_category_id', 'product_id');
    }

    public function store()
    {
        return $this->belongsTo(ShopStore::class, 'store_id', 'id');
    }

    public function descriptions()
    {
        return $this->hasMany(VendorCategoryDescription::class, 'vendor_category_id', 'id');
    }

    //Function get text description 
    public function getText() {
        return $this->descriptions()->where('lang', sc_get_locale())->first();
    }
    public function getTitle() {
        return $this->getText()->title ?? '';
    }
    public function getDescription() {
        return $this->getText()->description ?? '';
    }
    public function getKeyword() {
        return $this->getText()->keyword ?? '';
    }
    //End  get text description

    protected static function boot()
    {
        parent::boot();
        // before delete() method call this
        static::deleting(function ($category) {
            //Delete category descrition
            $category->descriptions()->delete();
            $category->products()->detach();
        });

        //Uuid
        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = sc_generate_id($type = 'vendor_category');
            }
        });
    }

    /*
    *Get thumb
    */
    public function getThumb()
    {
        return sc_image_get_path_thumb($this->image);
    }

    /*
    *Get image
    */
    public function getImage()
    {
        return sc_image_get_path($this->image);
    }

    public function getUrl()
    {
        return route('MultiVendor_category.detail', ['alias' => $this->alias, 'storeId' => $this->store_id]);
    }

    /**
     * Set store id
     *
     */
    public function setStore($id) {
        $this->sc_store = $id;
        return $this;
    }

    //Scort
    public function scopeSort($query, $sortBy = null, $sortOrder = 'asc')
    {
        $sortBy = $sortBy ?? 'sort';
        return $query->orderBy($sortBy, $sortOrder);
    }

    /**
     * Get sub category detail
     *
     * @param   [type]$key        [$key description]
     * @param   [type]$type       [$type description]
     * @param   null  $storeCode  [$storeCode description]
     *
     * @return  [type]            [return description]
     */
    public function getDetail($key, $type = null, $storeId = null, $status = 1)
    {
        if (empty($key)) {
            return null;
        }
        $storeId = empty($storeId) ? config('app.storeId') : $storeId;
        $tableDescription = (new VendorCategoryDescription)->getTable();
        $category = $this
            ->leftJoin($tableDescription, $tableDescription . '.vendor_category_id', $this->getTable() . '.id')
            ->where($this->getTable() . '.store_id', $storeId)
            ->where($tableDescription . '.lang', sc_get_locale());

        if ($type === null) {
            $category = $category->where($this->getTable() .'.id',  $key);
        } else {
            $category = $category->where($type, $key);
        }
        $category = $category->where($this->getTable() .'.status', $status);
        return $category->first();
    }
    


    /**
     * Start new process get data
     *
     * @return  new model
     */
    public function start() {
        return new VendorCategory;
    }

    /**
     * build Query
     */
    public function buildQuery() {
        $tableDescription = (new VendorCategoryDescription)->getTable();

        $dataSelect = $this->getTable().'.*, '.$tableDescription.'.*';

        //description
        $query = $this
            ->selectRaw($dataSelect)
            ->leftJoin($tableDescription, $tableDescription . '.vendor_category_id', $this->getTable() . '.id')
            ->where($tableDescription . '.lang', sc_get_locale());
        //search keyword
        if ($this->sc_keyword !='') {
            $query = $query->where(function ($sql) use($tableDescription){
                $sql->where($tableDescription . '.title', 'like', '%' . $this->sc_keyword . '%')
                    ->orWhere($tableDescription . '.keyword', 'like', '%' . $this->sc_keyword . '%')
                    ->orWhere($tableDescription . '.description', 'like', '%' . $this->sc_keyword . '%');
            });
        }

        $storeId = $this->sc_store ? $this->sc_store : config('app.storeId');

        //Process store
        if (!empty($this->sc_store)) {
            //If the store is specified or the default is not the primary store
            //Only get sub-category from eligible stores
            $tableStore = (new ShopStore)->getTable();
            $query = $query->join($tableStore, $tableStore . '.id', $this->getTable().'.store_id');
            $query = $query->where($this->getTable().'.store_id', $storeId);
        }
        //End store


        $query = $query->where($this->getTable().'.status', 1);

        if (count($this->sc_moreWhere)) {
            foreach ($this->sc_moreWhere as $key => $where) {
                if(count($where)) {
                    $query = $query->where($where[0], $where[1], $where[2]);
                }
            }
        }

        if ($this->sc_random) {
            $query = $query->inRandomOrder();
        } else {
            if (is_array($this->sc_sort) && count($this->sc_sort)) {
                foreach ($this->sc_sort as  $rowSort) {
                    if(is_array($rowSort) && count($rowSort) == 2) {
                        $query = $query->sort($rowSort[0], $rowSort[1]);
                    }
                }
            }
        }

        return $query;
    }

    //==================================

    public function uninstall()
    {
        if (Schema::hasTable($this->table)) {
            Schema::drop($this->table);
        }

        if (Schema::hasTable($this->tableDescription)) {
            Schema::drop($this->tableDescription);
        }
    }

    public function install()
    {
        $this->uninstall();

        Schema::create($this->table, function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('image', 255)->nullable();
            $table->string('alias', 120)->index();
            $table->tinyInteger('status')->default(0);
            $table->integer('sort')->default(0);
            $table->uuid('store_id')->index();
            $table->timestamps();
        });

        Schema::create($this->table.'_description', function (Blueprint $table) {
            $table->uuid('vendor_category_id');
            $table->string('lang', 10)->index();
            $table->string('title', 300)->nullable();
            $table->string('keyword', 200)->nullable();
            $table->string('description', 500)->nullable();
            $table->unique(['vendor_category_id', 'lang']);
        });
        
    }

}
