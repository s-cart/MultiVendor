@extends($templatePathAdmin.'.layout')

@if (!empty($dataNotFound))
  @section('main')
      <div class="card">
        <div class="card-tools">
          <div class="btn-group float-right">
              <a href="{{ sc_route_admin('admin_store.index') }}" class="btn  btn-flat btn-default" title="List">
                  <i class="fa fa-list"></i><span class="hidden-xs"> {{sc_language_render('admin.back_list')}}</span>
              </a>
          </div>
      </div>
        <div class="card-header with-border">
          <h2 class="card-title">{{ $title_description??'' }}</h2>
          <div class="card-tools">
              <div class="btn-group float-right mr-5">
                  <a href="{{ sc_route_admin('admin_MultiVendor.index') }}" class="btn  btn-flat btn-default" title="List">
                      <i class="fa fa-list"></i><span class="hidden-xs"> {{sc_language_render('admin.back_list')}}</span>
                  </a>
              </div>
          </div>
      </div>
        <div class="card-body table-responsivep-0">
          {{ sc_language_render('admin.data_notfound') }}
        </div>
      </div>
  @endsection
@else
@section('main')
      <div class="card card-primary card-outline card-outline-tabs">
        <div class="card-tools">
          <div class="btn-group float-right">
              <a href="{{ sc_route_admin('admin_MultiVendor.index') }}" class="btn  btn-flat btn-default" title="List">
                  <i class="fa fa-list"></i><span class="hidden-xs"> {{sc_language_render('admin.back_list')}}</span>
              </a>
          </div>
      </div>
        <div class="card-header p-0 border-bottom-0">
          <ul class="nav nav-tabs" id="custom-tabs-four-tab" role="tablist">          
            <li class="nav-item">
              <a class="nav-link active" id="store-info" data-toggle="pill" href="#tab-store-info" role="tab" aria-controls="tab-store-info" aria-selected="true">{{ sc_language_render('store.admin.config_info') }}</a>
            </li>
          </ul>
        </div>
        <div class="card-body">
          <div class="tab-content" id="custom-tabs-four-tabContent">
              {{-- Tab infomation --}}
              <div class="tab-pane fade  fade active show" id="tab-store-info" role="tabpanel" aria-labelledby="store-info">
                @include($templatePathAdminVendor.'screen.config_store.config_info')
              </div>
              {{-- //End tab infomation --}}

          </div>
        </div>
        <!-- /.card -->
</div>

@endsection
@endif

@push('styles')
<!-- Ediable -->
<link rel="stylesheet" href="{{ sc_file('admin/plugin/bootstrap-editable.css')}}">
<style type="text/css">
  #maintain_content img{
    max-width: 100%;
  }
</style>
@endpush

@if (empty($dataNotFound))
@push('scripts')
<!-- Ediable -->
<script src="{{ sc_file('admin/plugin/bootstrap-editable.min.js')}}"></script>

<script type="text/javascript">

  // Editable
$(document).ready(function() {

      //  $.fn.editable.defaults.mode = 'inline';
      $.fn.editable.defaults.params = function (params) {
        params._token = "{{ csrf_token() }}";
        params.storeId = "{{ $storeId }}";
        return params;
      };

      $('.editable-required').editable({
        validate: function(value) {
            if (value == '') {
                return '{{  sc_language_render('admin.not_empty') }}';
            }
        },
        success: function(data) {
          if(data.error == 0){
            alertJs('success', '{{ sc_language_render('admin.msg_change_success') }}');
          } else {
            alertJs('error', data.msg);
          }
      }
    });

    $('.editable').editable({
        validate: function(value) {
        },
        success: function(data) {
          console.log(data);
          if(data.error == 0){
            alertJs('success', '{{ sc_language_render('admin.msg_change_success') }}');
          } else {
            alertMsg('error', data.msg);
          }
      }
    });

});
</script>


  <script type="text/javascript">

    {!! $script_sort??'' !!}

  </script>

{{-- //Pjax --}}
<script src="{{ sc_file('admin/plugin/jquery.pjax.js')}}"></script>

<script>
  // Update store_info

//Logo
  $('.logo, .icon').change(function() {
        $.ajax({
        url: '{{ sc_route_admin('admin_store.update') }}',
        type: 'POST',
        dataType: 'JSON',
        data: {"name": $(this).attr('name'),"value":$(this).val(),"_token": "{{ csrf_token() }}", "storeId": "{{ $storeId }}" },
      })
      .done(function(data) {
        if(data.error == 0){
          alertJs('success', '{{ sc_language_render('admin.msg_change_success') }}');
        } else {
          alertJs('error', data.msg);
        }
      });
  });
//End logo


$('input.check-data-config').iCheck({
    checkboxClass: 'icheckbox_square-blue',
    radioClass: 'iradio_square-blue',
    increaseArea: '20%' /* optional */
  }).on('ifChanged', function(e) {
  var isChecked = e.currentTarget.checked;
  isChecked = (isChecked == false)?0:1;
  var name = $(this).attr('name');
    $.ajax({
      url: '{{ $urlUpdateConfig }}',
      type: 'POST',
      dataType: 'JSON',
      data: {
          "_token": "{{ csrf_token() }}",
          "name": $(this).attr('name'),
          "storeId": $(this).data('store'),
          "value": isChecked
        },
    })
    .done(function(data) {
      if(data.error == 0){
        alertJs('success', '{{ sc_language_render('admin.msg_change_success') }}');
      } else {
        alertJs('error', data.msg);
      }
    });

    });



  //End update store_info
</script>

@endpush
@endif