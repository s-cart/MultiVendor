<?php

namespace App\Plugins\Other\MultiVendor\Models;

// use Illuminate\Auth\Authenticatable;
// use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Foundation\Auth\User as Authenticatable;
// use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use SCart\Core\Front\Models\ShopEmailTemplate;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class VendorUser extends Authenticatable
{
    use Notifiable, HasApiTokens;
    use \SCart\Core\Front\Models\UuidTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'vendor_user';
    protected $guarded = [];
    protected $connection = SC_CONNECTION;
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];
    protected $appends = [
        'name',
    ];
    
    /**
     * Send email reset password
     * @param  [type] $token [description]
     * @return [type]        [description]
     */
    public function sendPasswordResetNotification($token)
    {
        $checkContent = (new ShopEmailTemplate)->where('group', 'forgot_password')->where('status', 1)->first();
        if ($checkContent) {
            $content = $checkContent->text;
            $dataFind = [
                '/\{\{\$title\}\}/',
                '/\{\{\$reason_sendmail\}\}/',
                '/\{\{\$note_sendmail\}\}/',
                '/\{\{\$note_access_link\}\}/',
                '/\{\{\$reset_link\}\}/',
                '/\{\{\$reset_button\}\}/',
            ];
            $url = sc_route('vendor.password_reset', ['token' => $token]);
            $dataReplace = [
                sc_language_render('email.forgot_password.title'),
                sc_language_render('email.forgot_password.reason_sendmail'),
                sc_language_render('email.forgot_password.note_sendmail', ['count' => config('auth.passwords.'.config('auth.defaults.passwords').'.expire')]),
                sc_language_render('email.forgot_password.note_access_link', ['reset_button' => sc_language_render('email.forgot_password.reset_button'), 'url' => $url]),
                $url,
                sc_language_render('email.forgot_password.reset_button'),
            ];
            $content = preg_replace($dataFind, $dataReplace, $content);
            $dataView = [
                'content' => $content,
            ];

            $config = [
                'to' => $this->getEmailForPasswordReset(),
                'subject' => sc_language_render('email.forgot_password.reset_button'),
            ];

            sc_send_mail('templates.' . sc_store('template') . '.mail.forgot_password', $dataView, $config, $dataAtt = []);
        }

    }


    /*
    Full name
     */
    public function getNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;

    }


    protected static function boot()
    {
        parent::boot();
        // before delete() method call this
        static::deleting(function ($customer) {
            //
        }
        );
        //Uuid
        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = sc_generate_id($type = 'vendor_user');
            }
        });
    }


    /**
     * Update info customer
     * @param  [array] $dataUpdate
     * @param  [int] $id
     */
    public static function updateInfo($dataUpdate, $id)
    {
        $dataUpdate = sc_clean($dataUpdate);
        $obj = self::find($id);
        return $obj->update($dataUpdate);
    }

    /**
     * Create new customer
     * @return [type] [description]
     */
    public static function createCustomer($dataInsert)
    {
        $dataClean = sc_clean($dataInsert);
        $user = self::create($dataClean);
        return $user;
    }


    /**
     * Check customer has Check if the user is verified
     *
     * @return boolean
     */
    public function isVerified() {
        return ! is_null($this->email_verified_at)  || $this->provider_id ;
    }

    /**
     * Check customer need verify email
     *
     * @return boolean
     */
    public function hasVerifiedEmail() {
        return !$this->isVerified() && sc_config('customer_verify');
    }
    /**
     * Send the email verification notification.
     *
     * @return void
     */
    public function sendEmailVerify() {

        if ($this->hasVerifiedEmail()) {
            
            $url = \Illuminate\Support\Facades\URL::temporarySignedRoute(
                'customer.verify_process',
                \Carbon\Carbon::now()->addMinutes(config('auth.verification', 60)),
                [
                    'id' => $this->id,
                    'token' => sha1($this->email),
                ]
            );

            $checkContent = (new ShopEmailTemplate)->where('group', 'customer_verify')->where('status', 1)->first();
            if ($checkContent) {
                $content = $checkContent->text;
                $dataFind = [
                    '/\{\{\$title\}\}/',
                    '/\{\{\$reason_sendmail\}\}/',
                    '/\{\{\$note_sendmail\}\}/',
                    '/\{\{\$note_access_link\}\}/',
                    '/\{\{\$url_verify\}\}/',
                    '/\{\{\$button\}\}/',
                ];
                $dataReplace = [
                    sc_language_render('email.verification_content.title'),
                    sc_language_render('email.verification_content.reason_sendmail'),
                    sc_language_render('email.verification_content.note_sendmail', ['count' => config('auth.verification')]),
                    sc_language_render('email.verification_content.note_access_link', ['reset_button' => sc_language_render('email.verification_content.reset_button'), 'url' => $url]),
                    $url,
                    sc_language_render('email.verification_content.reset_button'),
                ];
                $content = preg_replace($dataFind, $dataReplace, $content);
                $dataView = [
                    'content' => $content,
                ];
    
                $config = [
                    'to' => $this->email,
                    'subject' => sc_language_render('email.verification_content.reset_button'),
                ];
    
                sc_send_mail('templates.' . sc_store('template') . '.mail.customer_verify', $dataView, $config, $dataAtt = []);
                return true;
            }
        } 
        return false;
    }

    public function uninstall()
    {
        if (Schema::hasTable($this->table)) {
            Schema::drop($this->table);
        }
        if (Schema::hasTable('vendor_password_resets')) {
            Schema::drop('vendor_password_resets');
        }
    }

    public function install()
    {
        $this->uninstall();

        Schema::create($this->table, function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('first_name', 100);
            $table->string('last_name', 100)->nullable();
            $table->string('email', 150)->nullable();
            $table->string('password', 100)->nullable();;
            $table->string('postcode', 10)->nullable();
            $table->string('address1', 100)->nullable();
            $table->string('address2', 100)->nullable();
            $table->string('address3', 100)->nullable();
            $table->string('avatar', 255)->nullable();
            $table->string('country', 10)->nullable()->default('VN');
            $table->string('phone', 20)->nullable();
            $table->uuid('store_id')->index();
            $table->string('remember_token', 100)->nullable();
            $table->tinyInteger('status')->default(1);
            $table->timestamp('email_verified_at', $precision = 0)->nullable();
            $table->timestamps();
        });

        Schema::create('vendor_password_resets', function (Blueprint $table) {
            $table->string('email', 150);
            $table->string('token', 255);
            $table->dateTime('created_at');
            $table->index('email');
            }
        );
    }

}
