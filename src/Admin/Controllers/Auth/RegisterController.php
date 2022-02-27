<?php

namespace App\Plugins\Other\MultiVendor\Admin\Controllers\Auth;

use App\Plugins\Other\MultiVendor\Admin\Controllers\RootAdminVendorController;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Auth\Events\Registered;
use App\Plugins\Other\MultiVendor\Admin\Models\AdminVendorUser;
use SCart\Core\Front\Models\ShopCountry;
use Illuminate\Http\JsonResponse;

class RegisterController extends RootAdminVendorController
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
     */

    // use RegistersUsers;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->middleware('guest:vendor');
    }

    /**
     * Form create new item in admin
     * @return [type] [description]
     */
    public function showRegister()
    {
        $data = [
            'title'             => sc_language_render('multi_vendor.vendor_add'),
            'subTitle'          => '',
            'title_description' => '',
            'icon'              => 'fa fa-plus',
            'vendor'          => [],
            'countries'         => (new ShopCountry)->getCodeAll(),
            'url_action'        => sc_route_admin('vendor.register'),
        ];

        return view($this->plugin->pathPlugin.'::Admin.auth.register')
            ->with($data);
    }


    /**
    * Post create new item in admin
    * @return [type] [description]
    */
    public function postRegister()
    {
        $arraycountry = (new ShopCountry)->pluck('code')->toArray();

        $data = request()->all();
        $validate = [
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'password' => 'required|string|confirmed|min:6',
            'email' => 'required|string|email|max:255|unique:"'.AdminVendorUser::class.'",email',
        ];
        $validate['address1'] = 'nullable|string|max:100';
        $validate['address2'] = 'nullable|string|max:100';
        $validate['postcode'] = 'nullable|min:5';
        $validate['country'] = 'nullable|string|min:2|in:'. implode(',', $arraycountry);
        $validate['phone'] = 'nullable';

        $messages = [
            'last_name.required'   => sc_language_render('validation.required', ['attribute'=> sc_language_render('multi_vendor.last_name')]),
            'first_name.required'  => sc_language_render('validation.required', ['attribute'=> sc_language_render('multi_vendor.first_name')]),
            'email.required'       => sc_language_render('validation.required', ['attribute'=> sc_language_render('multi_vendor.email')]),
            'password.required'    => sc_language_render('validation.required', ['attribute'=> sc_language_render('multi_vendor.password')]),
            'address1.required'    => sc_language_render('validation.required', ['attribute'=> sc_language_render('multi_vendor.address1')]),
            'address2.required'    => sc_language_render('validation.required', ['attribute'=> sc_language_render('multi_vendor.address2')]),
            'phone.required'       => sc_language_render('validation.required', ['attribute'=> sc_language_render('multi_vendor.phone')]),
            'country.required'     => sc_language_render('validation.required', ['attribute'=> sc_language_render('multi_vendor.country')]),
            'postcode.required'    => sc_language_render('validation.required', ['attribute'=> sc_language_render('multi_vendor.postcode')]),
            'email.email'          => sc_language_render('validation.email', ['attribute'=> sc_language_render('multi_vendor.email')]),
            'phone.regex'          => sc_language_render('multi_vendor.phone_regex'),
            'postcode.min'         => sc_language_render('validation.min', ['attribute'=> sc_language_render('multi_vendor.postcode')]),
            'password.min'         => sc_language_render('validation.min', ['attribute'=> sc_language_render('multi_vendor.password')]),
            'password.confirmed'   => sc_language_render('validation.confirmed', ['attribute'=> sc_language_render('multi_vendor.password')]),
            'country.min'          => sc_language_render('validation.min', ['attribute'=> sc_language_render('multi_vendor.country')]),
            'first_name.max'       => sc_language_render('validation.max', ['attribute'=> sc_language_render('multi_vendor.first_name')]),
            'email.max'            => sc_language_render('validation.max', ['attribute'=> sc_language_render('multi_vendor.email')]),
            'address1.max'         => sc_language_render('validation.max', ['attribute'=> sc_language_render('multi_vendor.address1')]),
            'address2.max'         => sc_language_render('validation.max', ['attribute'=> sc_language_render('multi_vendor.address2')]),
            'last_name.max'        => sc_language_render('validation.max', ['attribute'=> sc_language_render('multi_vendor.last_name')]),
        ];

        $validator = Validator::make($data, $validate, $messages);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        $data['password'] = bcrypt($data['password']);
        $data['email'] = strtolower($data['email']);
        unset($data['password_confirmation']);
        $dataClean = sc_clean($data, ['password']);
        $vendor = AdminVendorUser::create($dataClean);
        //Login
        \Auth::guard('vendor')->login($vendor);
        return redirect()->route('vendor_admin.home')->with('success', sc_language_render('action.create_success'));

    }
    
}
