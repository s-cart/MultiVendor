@extends($templatePathAdmin.'layout')

@section('main')

<div class="row">

  <div class="col-md-6">

    <div class="card">

      <div class="card-body table-responsivep-0">
       <table class="table table-hover box-body text-wrap table-bordered">
         <tbody>
          <tr>
            <td>{{ sc_language_render('store.admin.active_strict') }}</td>
            <td>
              <a href="#" class="fied-required editable editable-click" data-name="domain_strict" data-type="select" data-pk="" data-source="{{ json_encode(['1'=>'ON','0'=>'OFF']) }}" data-url="{{ sc_route_admin('admin_config_global.update') }}" data-title="{{ sc_language_render('multi_vendor.MultiVendor_domain_strict') }}" data-value="{{ sc_config_global('domain_strict') }}" data-original-title="" title=""></a>
            </td>
          </tr>
          <tr>
            <td colspan="2"><span class="text-sm">
                <i class="fa fa-info-circle"></i> {{ sc_language_render('multi_vendor.MultiVendor_domain_strict_help') }}
              </span>
            </td>
          </tr>
           <tr>
            <td>{{ sc_language_render('multi_vendor.MultiVendor_allow_register') }}</td>
            <td><input class="check-data-config-global" type="checkbox" name="MultiVendor_allow_register"  {{ sc_config_global('MultiVendor_allow_register') ? "checked":"" }}></td>
          </tr>
           <tr>
            <td colspan="2"><span class="text-sm">
                <i class="fa fa-info-circle"></i> {{ sc_language_render('multi_vendor.MultiVendor_allow_register_help') }}
              </span>
            </td>
          </tr>
           <tr>
            <td>{{ sc_language_render('multi_vendor.MultiVendor_product_auto_approve') }}</td>
            <td><input class="check-data-config-global" type="checkbox" name="MultiVendor_product_auto_approve"  {{ sc_config_global('MultiVendor_product_auto_approve') ? "checked":"" }}></td>
          </tr>
          <tr>
            <td colspan="2"><span class="text-sm">
                <i class="fa fa-info-circle"></i> {{ sc_language_render('multi_vendor.MultiVendor_product_auto_approve_help') }}
              </span>
            </td>
          </tr>
           <tr>
            <td>{{ sc_language_render('multi_vendor.MultiVendor_quick_order') }}</td>
            <td><input class="check-data-config-global" type="checkbox" name="MultiVendor_quick_order"  {{ sc_config_global('MultiVendor_quick_order') ? "checked":"" }}></td>
          </tr>
          <tr>
            <td colspan="2"><span class="text-sm">
                <i class="fa fa-info-circle"></i> {{ sc_language_render('multi_vendor.MultiVendor_quick_order_help') }}
              </span>
            </td>
          </tr>
         </tbody>
       </table>
      </div>
    </div>
  </div>


</div>


@endsection


@push('styles')
<!-- Ediable -->
<link rel="stylesheet" href="{{ sc_file('admin/plugin/bootstrap-editable.css')}}">
@endpush

@push('scripts')
<!-- Ediable -->
<script src="{{ sc_file('admin/plugin/bootstrap-editable.min.js')}}"></script>

<script type="text/javascript">
  // Editable
$(document).ready(function() {

      $.fn.editable.defaults.params = function (params) {
        params._token = "{{ csrf_token() }}";
        return params;
      };
        $('.fied-required').editable({
        validate: function(value) {
            if (value == '') {
                return '{{  sc_language_render('admin.not_empty') }}';
            }
        },
        success: function(data) {
          if(data.error == 0){
            alertJs('success', '{{ sc_language_render('admin.msg_change_success') }}');
          } else {
            alertJs('error', response.msg);
          }
      }
    }); 
});



$('.clear-cache').click(function() {
  $(this).button('loading');
  $.ajax({
    url: '{{ sc_route_admin('admin_cache_config.clear_cache') }}',
    type: 'POST',
    dataType: 'JSON',
    data: {
      action: $(this).data('clear'),
        _token: '{{ csrf_token() }}',
    },
  })
  .done(function(data) {
    var obj = 'data-clear="'+data.action+'"';
    $("["+obj+"]").button('reset');
    if( data.action == 'cache_all') {
      setTimeout(function () {
        $(".clear-cache").prop('disabled', true);
      }, 100);
    } else {
      setTimeout(function () {
        $("["+obj+"]").prop('disabled', true);
      }, 100);
    }

    
    if(data.error == 0){
      alertJs('success', '{{ sc_language_render('admin.cache.cache_clear_success') }}');
    } else {
      alertJs('error', data.msg);
    }
  });
});


$('input.check-data-config-global').iCheck({
    checkboxClass: 'icheckbox_square-blue',
    radioClass: 'iradio_square-blue',
    increaseArea: '20%' /* optional */
  }).on('ifChanged', function(e) {
  var isChecked = e.currentTarget.checked;
  isChecked = (isChecked == false)?0:1;
  var name = $(this).attr('name');
    $.ajax({
      url: '{{ $urlUpdateConfigGlobal }}',
      type: 'POST',
      dataType: 'JSON',
      data: {
          "_token": "{{ csrf_token() }}",
          "name": $(this).attr('name'),
          "value": isChecked
        },
    })
    .done(function(data) {
      if(data.error == 0){
        if (isChecked == 0) {
          $('#smtp-config').hide();
        } else {
          $('#smtp-config').show();
        }
        alertJs('success', '{{ sc_language_render('admin.msg_change_success') }}');
      } else {
        alertJs('error', data.msg);
      }
    });

    });


</script>

@endpush
