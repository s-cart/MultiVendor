   <!-- Main Sidebar Container -->
   <aside class="main-sidebar sidebar-light-pink elevation-4 sidebar-no-expand">
    <a href="{{ sc_route_admin('vendor_admin.home') }}" class="brand-link navbar-secondary"  style="background:#969696 !important">
      Vendor
      <span class="brand-text font-weight-light">Admin</span>
    </a>
    <!-- Sidebar -->
    <div class="sidebar {{ config($styleDefine.'.sidebar') }}">
      <!-- Sidebar Menu -->
      <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column nav-legacy" data-widget="treeview" role="menu" >

        @if (vendor()->user()->status)
          {{-- User active --}}
          <!-- SEARCH FORM -->
          <form action="{{ sc_route_admin('admin_mvendor_order.index') }}" method="get" class="form-inline m-1 d-block d-sm-none" >
            <div class="input-group input-group-sm">
              <input name="keyword" class="form-control form-control-navbar" type="search" placeholder="{{sc_language_render('admin.order.search')}}" aria-label="Search">
              <div class="input-group-append">
                <button class="btn btn-navbar" type="submit">
                  <i class="fas fa-search"></i>
                </button>
              </div>
            </div>
          </form>


          <li class="nav-link header">
            <i class="nav-icon fab fa-shopify "></i> 
            <p class="sub-header"> {{ sc_language_render('admin.menu_titles.ADMIN_SHOP') }}</p>
          </li>

          <li class="nav-item has-treeview">
            <a href="#" class="nav-link">
              <i class="nav-icon  fas fa-cart-arrow-down "></i>
              <p>
                {{ sc_language_render('admin.menu_titles.order_manager') }}
                <i class="right fas fa-angle-left"></i>
              </p>
            </a>

            <ul class="nav nav-treeview">
                <li class="nav-item {{ \Admin::checkUrlIsChild(url()->current(), sc_route_admin('admin_mvendor_order.index')) ? 'active' : '' }}">
                  <a href="{{ sc_route_admin('admin_mvendor_order.index') }}" class="nav-link">
                    <i class="fas fa-shopping-cart nav-icon"></i>
                    <p>{{ sc_language_render('admin.menu_titles.order_manager') }}</p>
                  </a>
                </li>                                             
              </ul>
            </li>

            <li class="nav-item has-treeview">
              <a href="#" class="nav-link">
                <i class="nav-icon  fas fa-folder-open "></i>
                <p>
                  {{ sc_language_render('admin.menu_titles.catalog_mamager') }}
                  <i class="right fas fa-angle-left"></i>
                </p>
              </a>

                <ul class="nav nav-treeview">
                  <li class="nav-item {{ \Admin::checkUrlIsChild(url()->current(), sc_route_admin('admin_mvendor_category.index')) ? 'active' : '' }}">
                    <a href="{{ sc_route_admin('admin_mvendor_category.index') }}" class="nav-link">
                      <i class="fas fa-folder-open nav-icon"></i>
                      <p>{{ sc_language_render('admin.menu_titles.category') }}</p>
                    </a>
                  </li>
                  <li class="nav-item {{ \Admin::checkUrlIsChild(url()->current(), sc_route_admin('admin_mvendor_product.index')) ? 'active' : '' }}">
                    <a href="{{ sc_route_admin('admin_mvendor_product.index') }}" class="nav-link">
                      <i class="far fa-file-image nav-icon"></i>
                      <p>{{ sc_language_render('admin.menu_titles.product') }}</p>
                    </a>
                  </li>
                </ul>
            </li>            
              <li class="nav-link header">
                <i class="nav-icon  fas fa-file-signature "></i> 
                <p class="sub-header"> {{ sc_language_render('admin.menu_titles.ADMIN_CONTENT') }}</p>
              </li>

              <li class="nav-item {{ \Admin::checkUrlIsChild(url()->current(), sc_route_admin('admin_mvendor_banner.index')) ? 'active' : '' }}">
                <a href="{{ sc_route_admin('admin_mvendor_banner.index') }}" class="nav-link">
                  <i class="nav-icon fas fa-image"></i>
                  <p>
                    {{ sc_language_render('admin.menu_titles.banner') }}
                  </p>
                </a>
              </li>

                <li class="nav-link header">
                  <i class="nav-icon  fas fa-store-alt "></i> 
                  <p class="sub-header"> {{ sc_language_render('admin.menu_titles.ADMIN_SHOP_SETTING') }}</p>
                </li>

                <li class="nav-item {{ \Admin::checkUrlIsChild(url()->current(), sc_route_admin('admin_mvendor_info.update')) ? 'active' : '' }}">
                  <a href="{{ sc_route_admin('admin_mvendor_info.update') }}" class="nav-link">
                    <i class="nav-icon fas fa-h-square"></i>
                    <p>
                      {{ sc_language_render('store.admin.title') }}
                    </p>
                  </a>
                </li>
               
              {{-- //User active --}}
              @endif

      </ul>

      </nav>
      <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
  </aside>
  