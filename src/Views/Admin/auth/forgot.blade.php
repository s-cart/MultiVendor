@extends($templatePathAdminVendor.'layout_portable')

@section('main')
<form class="form-horizontal" method="POST" action="{{ sc_route('vendor.forgot') }}" id="form-process">
    {{ csrf_field() }}
    <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
        <label for="email" class="col-md-12 control-label"><i class="fas fa-envelope"></i>
            {{ sc_language_render('customer.email') }}</label>
        <div class="col-md-12">
            <input id="email" type="email" class="form-control" name="email" value="{{ old('email') }}" required placeholder="{{ sc_language_render('multi_vendor.email') }}">
            @if ($errors->has('email'))
            <span class="text-red">
                <strong>{{ $errors->first('email') }}</strong>
            </span>
            @endif
        </div>
        <div class="col-md-12">
            <button class="login-btn btn btn-warning btn-block btn-lg gradient-custom-4 text-body mt-1" type="submit">
                {{ sc_language_render('action.submit') }}
            </button>
        </div>
    </div>
</form>
@endsection