  <!-- Navbar -->
  <nav class="main-header navbar navbar-expand {{ config($styleDefine.'.main-header') }}">
    <!-- Left navbar links -->
    <ul class="navbar-nav">
      <li class="nav-item">
        <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
      </li>
        @include($templatePathAdminVendor.'component.language')
    </ul>

    <!-- SEARCH FORM -->
    <form action="{{ sc_route_admin('admin_mvendor_order.index') }}" method="get" class="form-inline ml-3 d-none d-sm-block" >
      <div class="input-group input-group-sm">
        <input name="keyword" class="form-control form-control-navbar" type="search" placeholder="{{sc_language_render('admin.order.search')}}" aria-label="Search">
        <div class="input-group-append">
          <button class="btn btn-navbar" type="submit">
            <i class="fas fa-search"></i>
          </button>
        </div>
      </div>
    </form>

    <!-- Right navbar links -->
    <ul class="navbar-nav ml-auto">
      <a class="nav-link" href="{{ sc_vendor_get_url(session('adminStoreId')) }}" target=_new>
        <i class="fas fa-home"></i>
      </a> 

      @include($templatePathAdminVendor.'component.admin_profile')

    </ul>
  </nav>
  <!-- /.navbar -->
