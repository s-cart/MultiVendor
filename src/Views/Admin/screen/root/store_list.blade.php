@extends($templatePathAdmin.'layout')

@section('main')

<div class="row">
  <div class="col-md-12">
    <div class="card">

      <div class="card-header with-border">
        <div class="card-tools">
          <div class="menu-right">
              <a href="{{ sc_route_admin('admin_MultiVendor.create') }}" class="btn btn-success btn-flat btn-md" title="New" id="button_create_new">
              <i class="fa fa-plus" title="{{ sc_language_render('store.admin.add_new') }}"></i> <span class="fab fa-shopify"></span>
              </a>
          </div>
        </div>
      </div>

      <div class="card-body" id="pjax-container">
        <div class="table-responsive">
        <table class="table table-hover box-body text-wrap">
          <thead>                  
            <tr>
              <th>{{ sc_language_render('store.admin.title') }}</th>
              <th>{{ sc_language_render($pathPlugin.'::lang.admin.store_url') }}</th>
              <th>{{ sc_language_render($pathPlugin.'::lang.admin.store_open') }}</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            @foreach ($stories as $key => $store)
            <tr>
              <td><i class="fas fa-home"></i> {{ $store->getTitle() }} (#{{ $store->code }})</td>
              <td>
                &nbsp; <a title="{{ sc_language_render($pathPlugin.'::lang.admin.store_shop') }}" target=_new href="{{ sc_route_admin('MultiVendor.detail', ['code' => $store->code]) }}"><span class="fab fa-shopify"></span></a> &nbsp; 
              </td>
              <td>
                @if ($key != SC_ID_ROOT)
                <input data-store="{{ $store->id }}" name="status" class="check-data-config-store" type="checkbox" {{ ($store->status == '1'?'checked':'') }}>
                @endif
              </td>               
              <td>
                @if ($key != SC_ID_ROOT)
                <span onclick="deleteItem({{ $store->id }});" title="{{ sc_language_render($pathPlugin.'::lang.admin.store_remove') }}" class="btn btn-flat btn-danger">
                  <i class="fas fa-trash-alt"></i>
                </span> &nbsp; 
                @endif

              <a href="{{ sc_route_admin('admin_MultiVendor.config', ['id' => $store->id]) }}" title="{{ sc_language_render($pathPlugin.'::lang.admin.store_config') }}">
                <span class="btn btn-flat btn-primary">
                <i class="fas fa-cogs"></i>
              </span>
              </a>
              </td>
            </tr>
            @endforeach
          </tbody>
          <tfoot>
            <tr>
              <td colspan="6" style="font-size: 12px">
                <span style="color:red">*{{ sc_language_render($pathPlugin.'::lang.admin.store_open') }}</span> : {{ sc_language_render($pathPlugin.'::lang.admin.store_open_help') }}<br>
              </td>
            </tr>
          </tfoot>
        </table>
        </div>

      </div>
    </div>
    </div>
</div>

@endsection


@push('styles')
<!-- Ediable -->
<link rel="stylesheet" href="{{ sc_file('admin/plugin/bootstrap-editable.css')}}">
<style type="text/css">
  #maintain_content img{
    max-width: 100%;
  }
</style>
@endpush

@push('scripts')
<!-- Ediable -->
<script src="{{ sc_file('admin/plugin/bootstrap-editable.min.js')}}"></script>

  <script type="text/javascript">

    {!! $script_sort??'' !!}

  </script>

{{-- //Pjax --}}
<script src="{{ sc_file('admin/plugin/jquery.pjax.js')}}"></script>

<script>
  // Update store_info

  function deleteItem(id){
  Swal.mixin({
    customClass: {
      confirmButton: 'btn btn-success',
      cancelButton: 'btn btn-danger'
    },
    buttonsStyling: true,
  }).fire({
    title: '{{ sc_language_render('action.delete_confirm') }} #'+id,
    text: "",
    type: 'warning',
    showCancelButton: true,
    confirmButtonText: '{{ sc_language_render('action.confirm_yes') }}',
    confirmButtonColor: "#DD6B55",
    cancelButtonText: '{{ sc_language_render('action.confirm_no') }}',
    reverseButtons: true,

    preConfirm: function() {
        return new Promise(function(resolve) {
            $.ajax({
                method: 'post',
                url: '{{ $urlDeleteItem ?? '' }}',
                data: {
                  id:id,
                    _token: '{{ csrf_token() }}',
                },
                success: function (data) {
                  console.log(data);
                    if(data.error == 1){
                      alertMsg('error', data.msg, '{{ sc_language_render('admin.warning') }}');
                      $.pjax.reload('#pjax-container');
                      return;
                    }else{
                      alertMsg('success', data.msg);
                      $.pjax.reload('#pjax-container');
                      resolve(data);
                    }

                }
            });
        });
    }

  }).then((result) => {
    if (result.value) {
      alertMsg('success', '{{ sc_language_render('action.delete_confirm_deleted_msg') }}', '{{ sc_language_render('action.delete_confirm_deleted') }}');
    } else if (
      // Read more about handling dismissals
      result.dismiss === Swal.DismissReason.cancel
    ) {
    }
  })
}
  //End update store_info
</script>

<script type="text/javascript">
$('input.check-data-config-store').iCheck({
    checkboxClass: 'icheckbox_square-blue',
    radioClass: 'iradio_square-blue',
    increaseArea: '20%' /* optional */
  }).on('ifChanged', function(e) {
  var isChecked = e.currentTarget.checked;
  isChecked = (isChecked == false)?0:1;
    
  $.ajax({
        type: 'POST',
        dataType:'json',
        url: "{{ sc_route_admin('admin_store.update') }}",
        data: {
          "_token": "{{ csrf_token() }}",
          "storeId": $(this).data('store'),
          "name": $(this).attr('name'),
          "value": isChecked
        },
        success: function (response) {
            // console.log(site_status);
          if(parseInt(response.error) ==0){
            alertMsg('success', '{{ sc_language_render('admin.msg_change_success') }}');
          }else{
            alertMsg('error', response.msg);
          }
          $('#loading').hide();
        }
      });

    });

  $(".store-status, .store-active").bootstrapSwitch();
  $('.store-status, .store-active').on('switchChange.bootstrapSwitch', function (event, state) {
      var site_status;
      if (state == true) {
          site_status =  '1';
      } else {
          site_status = '0';
      }
      $('#loading').show();

      $.ajax({
        type: 'POST',
        dataType:'json',
        url: "{{ sc_route_admin('admin_store.update') }}",
        data: {
          "_token": "{{ csrf_token() }}",
          "storeId": $(this).data('store'),
          "name": $(this).attr('name'),
          "value": site_status
        },
        success: function (response) {
            // console.log(site_status);
          if(parseInt(response.error) ==0){
            alertMsg('success', '{{ sc_language_render('admin.msg_change_success') }}');
          }else{
            alertMsg('error', response.msg);
          }
          $('#loading').hide();
        }
      });
  }); 

</script>

@endpush
