@extends($templatePathAdmin.'layout')

@section('main')

        <div class="row">

          <div class="col-md-12">
            <div class="card">
              <div class="card-header">
             
              <div class="input-group float-left">
              <form action="{{ $urlProcess }}" method="POST">
                @csrf
                {{-- <div class="col-md-3"> --}}
                  <div class="form-group">
                      <label>{{ sc_language_render('action.from') }}:</label>
                      <div class="input-group">
                      <input type="text" name="startDate" id="startDate" class="form-control input-sm date_time rounded-0" data-date-format="yyyy-mm-dd" placeholder="yyyy-mm-dd" value="{{ $startDate }}"> 
                      </div>
                  </div>
                {{-- </div> --}}
                {{-- <div class="col-md-3"> --}}
                  <div class="form-group">
                      <label>{{ sc_language_render('action.to') }}:</label>
                      <div class="input-group">
                      <input type="text" name="endDate" id="endDate" class="form-control input-sm date_time rounded-0" data-date-format="yyyy-mm-dd" placeholder="yyyy-mm-dd" value="{{ $endDate }}"> 
                      </div>
                  </div>
                {{-- </div> --}}
                {{-- <div class="col-md-3"> --}}
                  <div class="form-group">
                      <label>{{ sc_language_render('order.status') }}:</label>
                      <div class="input-group">
                        <select class="form-control select2" name="status_id" id="status_id">
                          <option value="">{{ sc_language_render('order.admin.search_order_status') }}</option>
                          @foreach ($statusOrder as $statusId => $statusName)
                          <option {{ ($statusId == $status) ? 'selected':'' }} value="{{ $statusId }}">{{ $statusName }}</option>
                          @endforeach
                        </select>
                      </div>
                  </div>
                {{-- </div> --}}
                <div class="form-group {{ session('store_empty') ? 'text-red':'' }}">
                    <label>{{ sc_language_render('front.store_list') }}:</label>
                    <div class="input-group">
                      <select  class="form-control select2" name="store_id" id="store_id">
                        <option value="">{{ sc_language_render('admin.select_store') }}</option>
                        @foreach (sc_get_list_code_store() as $keyId => $keyCode)
                        <option {{ ($keyId == $storeId) ? 'selected':'' }} value="{{ $keyId }}">{{ $keyCode }}</option>
                        @endforeach
                      </select>
                      <div class="input-group-append">
                        <button type="button" id="button-filter" class="btn btn-primary  btn-flat"><i class="fa fa-filter" aria-hidden="true"></i></button>
                      </div>
                    </div>
                    @if (session('store_empty'))
                    <span class="form-text">
                      <i class="fa fa-info-circle"></i>
                      {{ sc_language_render('multi_vendor.store_empty') }}
                    </span>
                    @endif
                </div>
                
                <div class="form-group">
                  <div class="input-group">
                    <div class="input-group-append">
                      <button type="submit"  class="btn btn-success  btn-flat"><i class="far fa-file-excel" aria-hidden="true"></i> {{ sc_language_render('multi_vendor.export_order_list') }}</button>
                    </div>
                  </div>
              </div>


              </form>
            </div>



              </div>
            </div>
          </div>

          <div class="col-md-12">
            <div class="card">
              <div class="card-header">
                <h5 class="card-title"></h5>

                <div class="card-tools">
                  <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                  </button>
                  <button type="button" class="btn btn-tool" data-card-widget="remove">
                    <i class="fas fa-times"></i>
                  </button>
                </div>
              </div>
              <!-- /.card-header -->
              <div class="card-body">
                <div class="row">
                  <div class="col-md-12">
                    <div id="chart-count" style="width:100%; height:auto;"></div>
                  </div>
                </div>
                <!-- /.row -->
              </div>
              <!-- ./card-body -->
              <!-- /.card-footer -->
            </div>
            <!-- /.card -->
          </div>
          <!-- /.col -->
        </div>
        <!-- /.row -->
@endsection


@push('styles')
@endpush

@push('scripts')
  <script src="{{ sc_file('admin/plugin/chartjs/highcharts.js') }}"></script>
  <script src="{{ sc_file('admin/plugin/chartjs/highcharts-3d.js') }}"></script>
  <script type="text/javascript">
  $('#button-filter').click(function(){
    var url = '{{ $urlProcess }}';
    url = url+'?startDate='+$('#startDate').val()+'&endDate='+$('#endDate').val()+'&status_id='+$('#status_id option:selected').val()+'&store_id='+$('#store_id option:selected').val();
    window.location.href = url;
  });
  document.addEventListener('DOMContentLoaded', function () {
      var myChart = Highcharts.chart('chart-count', {
          credits: {
              enabled: false
          },
          title: {
              text: '{{ sc_language_render('multi_vendor.top_count_order_vendor') }}'
          },
          xAxis: {
              categories: {!! json_encode(array_keys($countOrderVendor)) !!},
              crosshair: false

          },

          yAxis: [{
              min: 0,
              title: {
                  text: '{{ sc_language_render('admin.chart.order') }}'
              },
          }
          ],

          series: [
          {
              type: 'column',
              name: '{{ sc_language_render('admin.chart.order') }}',
              data: {!! json_encode(array_values($countOrderVendor)) !!},
              dataLabels: {
                  enabled: true,
                  format: '{point.y:.0f}'
              }
          }
        ]
      });
  });
</script>


@endpush
