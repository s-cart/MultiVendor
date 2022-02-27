@extends($templatePathAdminVendor.'layout')

@section('main')
 <div class="row">
    <div class="col-md-12">
       <div class="card">

          <div class="card-header with-border">
              <h3 class="card-title">{{ sc_language_render('order.detail') }} #{{ $order->id }}</h3>
              <div class="card-tools not-print">
                  <div class="btn-group float-right" style="margin-right: 0px">
                      <a href="{{ sc_route_admin('admin_mvendor_order.index') }}" class="btn btn-flat btn-default"><i class="fa fa-list"></i>&nbsp;{{ sc_language_render('admin.back_list') }}</a>
                  </div>
              </div>
          </div>

          <div class="row" id="order-body">
            <div class="col-sm-6">
                 <table class="table table-bordered">
                    <tr>
                      <td class="td-title">{{ sc_language_render('order.first_name') }}:</td><td>{!! $order->first_name !!}</td>
                    </tr>

                    @if (sc_config_admin('customer_lastname'))
                    <tr>
                      <td class="td-title">{{ sc_language_render('order.last_name') }}:</td><td>{!! $order->last_name !!}</td>
                    </tr>
                    @endif

                    @if (sc_config_admin('customer_phone'))
                    <tr>
                      <td class="td-title">{{ sc_language_render('order.phone') }}:</td><td>{!! $order->phone !!}</td>
                    </tr>
                    @endif

                    <tr>
                      <td class="td-title">{{ sc_language_render('order.email') }}:</td><td>{!! empty($order->email)?'N/A':$order->email!!}</td>
                    </tr>

                    @if (sc_config_admin('customer_company'))
                    <tr>
                      <td class="td-title">{{ sc_language_render('order.company') }}:</td><td>{!! $order->company !!}</td>
                    </tr>
                    @endif

                    @if (sc_config_admin('customer_postcode'))
                    <tr>
                      <td class="td-title">{{ sc_language_render('order.postcode') }}:</td><td>{!! $order->postcode !!}</td>
                    </tr>
                    @endif

                    <tr>
                      <td class="td-title">{{ sc_language_render('order.address1') }}:</td><td>{!! $order->address1 !!}</td>
                    </tr>

                    @if (sc_config_admin('customer_address2'))
                    <tr>
                      <td class="td-title">{{ sc_language_render('order.address2') }}:</td><td>{!! $order->address2 !!}</td>
                    </tr>
                    @endif

                    @if (sc_config_admin('customer_country'))
                    <tr>
                      <td class="td-title">{{ sc_language_render('order.country') }}:</td><td>{{ $country[$order->country] ?? $order->country }}</td>
                    </tr>
                    @endif

                </table>
            </div>
            <div class="col-sm-6">
                <table  class="table table-bordered">
                    <tr><td  class="td-title">{{ sc_language_render('order.status') }}:</td><td>{{ $statusOrder[$order->status] ?? '' }}</td></tr>
                    <tr>
                      <td>{{ sc_language_render('order.shipping_status') }}:</td>
                      <td><a href="#" class="updateStatus" data-name="shipping_status_store" data-type="select" data-source ="{{ json_encode($statusShipping) }}"  data-pk="{{ $order->id }}" data-value="{!! $order->shipping_status_store !!}" data-url="{{ sc_route("admin_mvendor_order.update") }}" data-title="{{ sc_language_render('order.shipping_status') }}">{{ $statusShipping[$order->shipping_status_store]??'' }}</a></td>
                    </tr>
                    <tr><td>{{ sc_language_render('order.payment_status') }}:</td><td>{{ $statusPayment[$order->payment_status]??'' }}</td></tr>
                    <tr><td>{{ sc_language_render('order.shipping_method') }}:</td><td>{{ $order->shipping_method }}</td></tr>
                  </table>
                 <table class="table table-bordered">
                    <tr>
                      <td><i class="far fa-money-bill-alt nav-icon"></i> {{ sc_language_render('order.currency') }}:</td><td>{{ $order->currency }}</td>
                    </tr>
                    <tr>
                      <td></i> {{ sc_language_render('order.domain') }}:</td><td>{{ $order->domain }}</td>
                    </tr>
                    <tr>
                      <td></i> {{ sc_language_render('admin.created_at') }}:</td><td>{{ $order->created_at }}</td>
                    </tr>
                </table>
            </div>
          </div>

      <div class="row">
        <div class="col-sm-12">
          <div class="card collapsed-card">
          <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                  <tr>
                    <th>{{ sc_language_render('product.name') }}</th>
                    <th>{{ sc_language_render('product.sku') }}</th>
                    <th class="product_price">{{ sc_language_render('product.price') }}</th>
                    <th class="product_qty">{{ sc_language_render('product.quantity') }}</th>
                    <th class="product_total">{{ sc_language_render('product.total_price') }}</th>
                    <th class="product_tax">{{ sc_language_render('product.tax') }}</th>
                  </tr>
                </thead>
                <tbody>
                  @php
                      $details = $order->details;
                  @endphp
                    @foreach ($details as $item)
                          <tr>
                            <td>{{ $item->name }}
                              @php
                              $html = '';
                                if($item->attribute && is_array(json_decode($item->attribute,true))){
                                  $array = json_decode($item->attribute,true);
                                      foreach ($array as $key => $element){
                                        $html .= '<br><b>'.$attributesGroup[$key].'</b> : <i>'.sc_render_option_price($element, $order->currency, $order->exchange_rate).'</i>';
                                      }
                                }
                              @endphp
                            {!! $html !!}
                            </td>
                            <td>{{ $item->sku }}</td>
                            <td class="product_price">{{ $item->price }}</td>
                            <td class="product_qty">x  {{ $item->qty }}</td>
                            <td class="product_total">{{ sc_currency_render_symbol($item->total_price,$order->currency)}}</td>
                            <td class="product_tax"> {{ $item->tax }}</td>
                          </tr>
                    @endforeach
                </tbody>
              </table>
            </div>
        </div>
        </div>

      </div>


      <div class="row">
        {{-- Total --}}
          <div class="col-sm-6">
            <div class="card collapsed-card">
                <table   class="table table-bordered">
                  @foreach ($dataTotal as $element)
                    @if ($element['code'] =='subtotal')
                      <tr><td  class="td-title-normal">{!! $element['title'] !!}:</td><td style="text-align:right" class="data-{{ $element['code'] }}">{{ sc_currency_format($element['value']) }}</td></tr>
                    @endif
                    @if ($element['code'] =='tax')
                    <tr><td  class="td-title-normal">{!! $element['title'] !!}:</td><td style="text-align:right" class="data-{{ $element['code'] }}">{{ sc_currency_format($element['value']) }}</td></tr>
                    @endif

                    @if ($element['code'] =='shipping')
                      <tr><td>{!! $element['title'] !!}:</td><td style="text-align:right">{{$element['value'] }}</td></tr>
                    @endif
                    @if ($element['code'] =='discount')
                      <tr><td>{!! $element['title'] !!}(-):</td><td style="text-align:right">{{$element['value'] }}</td></tr>
                    @endif

                     @if ($element['code'] =='total')
                      <tr style="background:#f5f3f3;font-weight: bold;"><td>{!! $element['title'] !!}:</td><td style="text-align:right" class="data-{{ $element['code'] }}">{{ sc_currency_format($element['value']) }}</td></tr>
                    @endif

                    @if ($element['code'] =='received')
                      <tr><td>{!! $element['title'] !!}(-):</td><td style="text-align:right">{{$element['value'] }}</td></tr>
                    @endif

                  @endforeach

                  </table>
            </div>
          </div>
          {{-- //End total --}}

          {{-- History --}}
          <div class="col-sm-6">
            <div class="card">
              <table class="table table-bordered">
                <tr>
                  <td  class="td-title">{{ sc_language_render('order.note') }}:</td>
                  <td>
                      {{ $order->comment }}
                </td>
                </tr>
              </table>
            </div>
          </div>
          {{-- //End history --}}
      </div>
    </div>
  </div>
</div>
@endsection


@push('styles')
<style type="text/css">
.history{
  max-height: 50px;
  max-width: 300px;
  overflow-y: auto;
}
.td-title{
  width: 35%;
  font-weight: bold;
}
.td-title-normal{
  width: 35%;
}
.product_qty{
  width: 80px;
  text-align: right;
}
.product_price,.product_total{
  width: 120px;
  text-align: right;
}

</style>
<!-- Ediable -->
<link rel="stylesheet" href="{{ sc_file('admin/plugin/bootstrap-editable.css')}}">
@endpush

@push('scripts')
{{-- //Pjax --}}
<script src="{{ sc_file('admin/plugin/jquery.pjax.js')}}"></script>

<!-- Ediable -->
<script src="{{ sc_file('admin/plugin/bootstrap-editable.min.js')}}"></script>



<script type="text/javascript">

$(document).ready(function() {
  all_editable();
});

function all_editable(){
    $.fn.editable.defaults.params = function (params) {
        params._token = "{{ csrf_token() }}";
        return params;
    };

    $('.updateStatus').editable({
        validate: function(value) {
            if (value == '') {
                return '{{  sc_language_render('admin.not_empty') }}';
            }
        },
        success: function(response) {
          if(response.error ==0){
            alertJs('success', response.msg);
          } else {
            alertJs('error', response.msg);
          }
      }
    });
}

  $(document).ready(function(){
  // does current browser support PJAX
    if ($.support.pjax) {
      $.pjax.defaults.timeout = 2000; // time in milliseconds
    }

  });
</script>

@endpush
