@extends($templatePathAdminVendor.'layout_portable')

@section('main')
<form method="POST" action="{{ sc_route('vendor.post_password_reset') }}" aria-label="{{ sc_language_render('multi_vendor.password_reset') }}">
    @csrf

    <input type="hidden" name="token" value="{{ $token }}">

    <div class="form-group row">
        <label for="email" class="col-md-4 col-form-label text-md-right">{{ sc_language_render('multi_vendor.email') }}</label>

        <div class="col-md-6">
            <input id="email" type="email" class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}" name="email" value="{{ old('email') }}" required autofocus>

            @if ($errors->has('email'))
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $errors->first('email') }}</strong>
                </span>
            @endif
        </div>
    </div>

    <div class="form-group row">
        <label for="password" class="col-md-4 col-form-label text-md-right">{{ sc_language_render('multi_vendor.password') }}</label>

        <div class="col-md-6">
            <input id="password" type="password" class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}" name="password" required>

            @if ($errors->has('password'))
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $errors->first('password') }}</strong>
                </span>
            @endif
        </div>
    </div>

    <div class="form-group row">
        <label for="password-confirm" class="col-md-4 col-form-label text-md-right">{{ sc_language_render('multi_vendor.password_confirm') }}</label>

        <div class="col-md-6">
            <input id="password-confirm" type="password" class="form-control" name="password_confirmation" required>
        </div>
    </div>

    <div class="form-group row mb-0">
        <div class="col-md-6 offset-md-4">
            <button class="login-btn btn btn-warning btn-block btn-lg gradient-custom-4 text-body" type="submit">
                {{ sc_language_render('multi_vendor.password_reset') }}
              </button>
        </div>
    </div>
</form>
@endsection
