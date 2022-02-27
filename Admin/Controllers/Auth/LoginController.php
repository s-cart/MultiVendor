<?php

namespace App\Plugins\Other\MultiVendor\Admin\Controllers\Auth;

use App\Plugins\Other\MultiVendor\Admin\Controllers\RootAdminVendorController;
use App\Plugins\Other\MultiVendor\Admin\Models\AdminVendorUser;
use SCart\Core\Front\Models\ShopCountry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;

class LoginController extends RootAdminVendorController
{
    public function __construct()
    {
        parent::__construct();
    }
    /**
     * Show the login page.
     *
     * @return \Illuminate\Contracts\View\Factory|Redirect|\Illuminate\View\View
     */
    public function getLogin()
    {
        if ($this->guard()->check()) {
            return redirect($this->redirectPath());
        }
        return view($this->templatePathAdmin.'auth.login', ['title'=> sc_language_render('multi_vendor.admin_login')]);
    }

    /**
     * Handle a login request.
     *
     * @param Request $request
     *
     * @return mixed
     */
    public function postLogin(Request $request)
    {
        // Check admin login
        if (admin()->user()) {
            return redirect(sc_route_admin('vendor.login'));
        }

        $this->loginValidator($request->all())->validate();

        $credentials = $request->only([$this->username(), 'password']);
        $remember = $request->get('remember', false);

        if ($this->guard()->attempt($credentials, $remember)) {
            return $this->sendLoginResponse($request);
        }
        return back()->withInput()->withErrors([
            $this->username() => $this->getFailedLoginMessage(),
        ]);
    }

    /**
     * Get a validator for an incoming login request.
     *
     * @param array $data
     *
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function loginValidator(array $data)
    {
        return Validator::make($data, [
            $this->username() => 'required',
            'password' => 'required',
        ]);
    }

    /**
     * User logout.
     *
     * @return Redirect
     */
    public function getLogout(Request $request)
    {
        $this->guard()->logout();

        $request->session()->invalidate();

        return redirect($this->redirectPath());
    }

    public function getSetting()
    {
        $vendor = vendor()->user();
        if ($vendor === null) {
            return 'no data';
        }
        $data = [
            'title'             => sc_language_render('action.edit'),
            'subTitle'          => '',
            'title_description' => '',
            'icon'              => 'fa fa-edit',
            'vendor'              => $vendor,
            'countries'         => (new ShopCountry)->getCodeAll(),
            'url_action'        => sc_route_admin('vendor.setting'),

        ];
        return view($this->plugin->pathPlugin.'::Admin.auth.setting')
            ->with($data);
    }

    public function putSetting()
    {
        $vendor = vendor()->user();
        $data = request()->all();

        $arraycountry = (new ShopCountry)->pluck('code')->toArray();

        $data = request()->all();
        $validate = [
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'password' => 'nullable|string|min:6',
            'email' => 'nullable|string|email|max:255|unique:"'.AdminVendorUser::class.'",email,'.$vendor['id'].',id',
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

        if (!empty($data['password'])) {
            $data['password'] = bcrypt($data['password']);
        } else {
            unset($data['password']);
        }
        $data['email'] = strtolower($data['email']);
        $dataClean = sc_clean($data);
        $vendor->update($dataClean);

        return redirect()->route('vendor_admin.home')->with('success', sc_language_render('action.edit_success'));

    }

    /**
     * @return string|\Symfony\Component\Translation\TranslatorInterface
     */
    protected function getFailedLoginMessage()
    {
        return lang::has('auth.failed')
        ? sc_language_render('admin.failed')
        : 'These credentials do not match our records.';
    }

    /**
     * Get the post login redirect path.
     *
     * @return string
     */
    protected function redirectPath()
    {
        return sc_route_admin('vendor_admin.home');
    }

    /**
     * Send the response after the user was authenticated.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    protected function sendLoginResponse(Request $request)
    {

        $request->session()->regenerate();

        return redirect()->intended($this->redirectPath())->with(['success' => sc_language_render('admin.login_successful')]);
    }

    /**
     * Get the login username to be used by the controller.
     *
     * @return string
     */
    protected function username()
    {
        return 'email';
    }

    /**
     * Get the guard to be used during authentication.
     *
     * @return \Illuminate\Contracts\Auth\StatefulGuard
     */
    protected function guard()
    {
        return Auth::guard('vendor');
    }

}
