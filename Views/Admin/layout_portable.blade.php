@php
    $styleDefine = 'admin.theme_define.'.config('admin.theme_default');
@endphp
<!DOCTYPE html>
<html lang="{{ config('app.locale') }}">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <link rel="icon" href="{{ sc_file('images/icon.png') }}" type="image/png" sizes="16x16">
  <title>{{sc_config_admin('ADMIN_TITLE')}} | {{ $title??'' }}</title>
  <!-- Tell the browser to be responsive to screen width -->
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="{{ sc_file('admin/LTE/plugins/fontawesome-free/css/all.min.css')}}">
  <!-- Google Font: Source Sans Pro -->
  <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700" rel="stylesheet">

  <!-- iCheck -->
  <link rel="stylesheet" href="{{ sc_file('admin/LTE/plugins/icheck-bootstrap/icheck-bootstrap.min.css')}}">

  <link rel="stylesheet" href="{{ sc_file('admin/LTE/dist/css/adminlte.min.css')}}">

  <style>
    .logo {
      max-width: 200px;
    }
    body {
      background-color:#c4bcc4;
    }
    .note-admin-login {
      text-align: center;
      color: #fb6800;
    }
  </style>
</head>

<body>
  <section class="vh-100 bg-image">
    <div class="mask d-flex align-items-center h-100 gradient-custom-3">
      <div class="container h-100">
        <div class="row d-flex justify-content-center align-items-center h-100">
          <div class="col-12 col-md-9 col-lg-7 col-xl-6">
            <div class="card">
              <div class="col-md-12 text-center pt-5">
                <a href="{{ sc_route_admin('vendor_admin.home') }}"><img src="{{ sc_file(sc_store('logo')) }}" alt="logo" class="logo"></a>
              </div>
              <div class="card-body p-5">
                <h2 class="text-uppercase text-center">{{ $title ?? '' }}</h2>
                  @yield('main')  
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

<!-- ./wrapper -->

<!-- jQuery -->
<script src="{{ sc_file('admin/LTE/plugins/jquery/jquery.min.js')}}"></script>
<!-- jQuery UI 1.11.4 -->
<script src="{{ sc_file('admin/LTE/plugins/jquery-ui/jquery-ui.min.js')}}"></script>
<!-- Resolve conflict in jQuery UI tooltip with Bootstrap tooltip -->
{{-- <script>
  $.widget.bridge('uibutton', $.ui.button)
</script> --}}
<!-- Bootstrap 4 -->
<script src="{{ sc_file('admin/LTE/plugins/bootstrap/js/bootstrap.bundle.min.js')}}"></script>
{{-- <!-- ChartJS -->

<!-- AdminLTE App -->
<script src="{{ sc_file('admin/LTE/dist/js/adminlte.js')}}"></script>
{{-- sweetalert2 --}}
<script src="{{ sc_file('admin/plugin/sweetalert2.all.min.js')}}"></script>

<script src="{{ sc_file('admin/LTE/plugins/iCheck/icheck.min.js')}}"></script>

@include($templatePathAdminVendor.'component.alerts')

</body>
</html>