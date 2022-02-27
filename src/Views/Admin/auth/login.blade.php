@extends($templatePathAdminVendor.'layout_portable')

@section('main')

@if (admin()->user())
<div class="note-admin-login">
  {!! sc_language_render('Plugins/Other/MultiVendor::lang.admin.note_admin_login') !!} 
  <a href="{{ sc_route_admin('admin.logout') }}">CLICK HERE</a>
</div>
@else
<form action="{{ sc_route_admin('vendor.login') }}" method="post">
  <div class="col-md-12 form-group has-feedback {!! !$errors->has('email') ?: 'text-red' !!}">
    <div class="wrap-input100 validate-input form-group ">
      <input class="input100 form-control" type="text" placeholder="{{ sc_language_render('multi_vendor.email') }}"
        name="email" value="{{ old('email') }}">
      @if($errors->has('email'))
      @foreach($errors->get('email') as $message)
      <label class="control-label" for="inputError"><i class="fa fa-times-circle-o"></i>{{$message}}</label><br>
      @endforeach
      @endif
    </div>
  </div>
  <div class="col-md-12 form-group has-feedback {!! !$errors->has('password') ?: 'text-red' !!}">
    <div class="wrap-input100 validate-input form-group ">
      <input class="input100 form-control" type="password" placeholder="{{ sc_language_render('multi_vendor.password') }}"
        name="password">
      @if($errors->has('password'))
      @foreach($errors->get('password') as $message)
      <label class="control-label" for="inputError"><i class="fa fa-times-circle-o"></i>{{$message}}</label><br>
      @endforeach
      @endif
    </div>
  </div>
  <div class="col-md-12">
    <div class="container-login-btn">
      <button class="login-btn btn btn-warning btn-block btn-lg gradient-custom-4 text-body" type="submit">
        {{ sc_language_render('multi_vendor.login') }}
      </button>
    </div>
    <div class="checkbox text-center remember">
      <label>
        <input class="checkbox" type="checkbox" name="remember" value="1"
          {{ (old('remember')) ? 'checked' : '' }}>
        <b>{{ sc_language_render('multi_vendor.remember_me') }}</b>
      </label>
    </div>
  </div>
  <input type="hidden" name="_token" value="{{ csrf_token() }}">

@if (sc_config_global('MultiVendor_allow_register'))
<div class="form-check d-flex justify-content-center">
  <label class="form-check-label" for="form2Example3g">
    <a href="{{ sc_route_admin('vendor.register') }}">
      <i class="fa fa-caret-right"></i> <b>{{ sc_language_render('multi_vendor.title_register') }}</b>
    </a>
  </label>
</div> 
@endif

  <div class="form-check d-flex justify-content-center">
    <label class="form-check-label" for="form2Example3g">
      <a href="{{ sc_route_admin('vendor.forgot') }}">
        <i class="fa fa-caret-right"></i> <b>{{ sc_language_render('multi_vendor.password_forgot') }}</b>
      </a>
    </label>
  </div>
</form>
@endif
@endsection


    @push('styles')
    @endpush

    @push('scripts')
    <script>
      $(function () {
        $('.checkbox').iCheck({
          checkboxClass: 'icheckbox_square-blue',
          radioClass: 'iradio_square-blue',
          increaseArea: '20%' /* optional */
        });
      });
    </script>
    @endpush