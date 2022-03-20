@extends($templatePathAdminVendor.'layout')

@section('main')
   <div class="row">
      <div class="col-md-12">
          <div class="box-body">
            <div class="error-page text-center">
                <h2 class="text-red">{{ sc_language_render('multi_vendor.account_inactive_title') }}</h2>
                <span><h4><i class="fa fa-warning text-red" aria-hidden="true"></i> {{ sc_language_render('multi_vendor.account_inactive_msg') }}</h4></span>
            </div>
        </div>
      </div>
  </div>
@endsection


@push('styles')
@endpush

@push('scripts')
@if ($url)
<script>
  window.history.pushState("", "", '{{ $url }}');
</script>
@endif
@endpush
