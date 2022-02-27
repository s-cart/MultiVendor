<?php

namespace App\Plugins\Other\MultiVendor\Admin\Controllers\Auth;

use App\Plugins\Other\MultiVendor\Admin\Controllers\RootAdminVendorController;
use Auth;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Support\Facades\Password;

class ForgotPasswordController extends RootAdminVendorController
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset emails and
    | includes a trait which assists in sending these notifications from
    | your application to your users. Feel free to explore this trait.
    |
     */

    use SendsPasswordResetEmails;

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
     * Send a reset link to the given user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function sendRepostForgotsetLinkEmail(\Illuminate\Http\Request $request)
    {
        $data = $request->all();
        $dataMapping['email'] = 'required|string|email';
        $validator = \Illuminate\Support\Facades\Validator::make($data, $dataMapping);
        if ($validator->fails()) {
            return redirect()->back()
                        ->withErrors($validator)
                        ->withInput();
        }

        // We will send the password reset link to this user. Once we have attempted
        // to send the link, we will examine the response then see the message we
        // need to show to the user. Finally, we'll send out a proper response.
        $response = $this->broker()->sendResetLink(
            $this->credentials($request)
        );

        return $response == \Illuminate\Support\Facades\Password::RESET_LINK_SENT
                    ? $this->sendResetLinkResponse($request, $response)
                    : $this->sendResetLinkFailedResponse($request, $response);
    }

    /**
     * Form forgot password
     * @return [view] 
     */
    public function getForgot()
    {
        if (vendor()->user()) {
            return redirect()->route('vendor_admin.home');
        }
        $data = [
            'title'       => sc_language_render('multi_vendor.password_forgot'),
            'breadcrumbs' => [
                ['url'    => '', 'title' => sc_language_render('multi_vendor.password_forgot')],
            ],
        ];
        return view($this->plugin->pathPlugin.'::Admin.auth.forgot')
            ->with($data);
    }
    
    public function redirectPath() {
        return sc_route_admin('vendor_admin.home');
    }

    protected function broker()
    {
        return Password::broker('vendors');
    }

    protected function guard()
    {
        return Auth::guard('vendor');
    }
}
