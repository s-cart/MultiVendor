@extends($templatePathAdminVendor.'layout_portable')

@section('main')
<form action="{{ $url_action }}" method="post" accept-charset="UTF-8" class="form-horizontal" id="form-main"  enctype="multipart/form-data">

            <div class="form-group row {{ $errors->has('first_name') ? ' text-red' : '' }}">
                <label for="first_name"
                    class="col-sm-4 col-form-label">{{ sc_language_render('multi_vendor.first_name') }}</label>

                <div class="col-sm-8">
                    <div class="input-group">
                        <div class="input-group-prepend">
                        <span class="input-group-text"><i class="fas fa-pencil-alt"></i></span>
                        </div>
                    <input id="first_name" type="text" class="form-control" name="first_name"
                        value="{{ (old('first_name', $vendor['first_name'] ?? ''))}}">
                    </div>
                    @if($errors->has('first_name'))
                    <span class="form-text">{{ $errors->first('first_name') }}</span>
                    @endif

                </div>
            </div>
            <div class="form-group row {{ $errors->has('last_name') ? ' text-red' : '' }}">
                <label for="last_name"
                    class="col-sm-4 col-form-label">{{ sc_language_render('multi_vendor.last_name') }}</label>

                <div class="col-sm-8">
                    <div class="input-group">
                        <div class="input-group-prepend">
                        <span class="input-group-text"><i class="fas fa-pencil-alt"></i></span>
                        </div>
                    <input id="last_name" type="text" class="form-control" name="last_name"
                        value="{{ (old('last_name', $vendor['last_name'] ?? ''))}}">
                    </div>
                    @if($errors->has('last_name'))
                    <span class="form-text">{{ $errors->first('last_name') }}</span>
                    @endif

                </div>
            </div>


            <div class="form-group row {{ $errors->has('phone') ? ' text-red' : '' }}">
                <label for="phone"
                    class="col-sm-4 col-form-label">{{ sc_language_render('multi_vendor.phone') }}</label>

                <div class="col-sm-8">
                    <div class="input-group">
                        <div class="input-group-prepend">
                        <span class="input-group-text"><i class="fas fa-pencil-alt"></i></span>
                        </div>
                    <input id="phone" type="text" class="form-control" name="phone"
                        value="{{ (old('phone', $vendor['phone'] ?? ''))}}">
                    </div>
                    @if($errors->has('phone'))
                    <span class="form-text">{{ $errors->first('phone') }}</span>
                    @endif

                </div>
            </div>

            <div class="form-group row {{ $errors->has('postcode') ? ' text-red' : '' }}">
                <label for="postcode"
                    class="col-sm-4 col-form-label">{{ sc_language_render('multi_vendor.postcode') }}</label>

                <div class="col-sm-8">
                    <div class="input-group">
                        <div class="input-group-prepend">
                        <span class="input-group-text"><i class="fas fa-pencil-alt"></i></span>
                        </div>
                    <input id="postcode" type="text" class="form-control" name="postcode"
                        value="{{ (old('postcode', $vendor['postcode'] ?? ''))}}">
                    </div>

                    @if($errors->has('postcode'))
                    <span class="form-text">{{ $errors->first('postcode') }}</span>
                    @endif

                </div>
            </div>

            <div class="form-group row {{ $errors->has('email') ? ' text-red' : '' }}">
                <label for="email"
                    class="col-sm-4 col-form-label">{{ sc_language_render('multi_vendor.email') }}</label>

                <div class="col-sm-8">
                    <div class="input-group">
                        <div class="input-group-prepend">
                        <span class="input-group-text"><i class="fas fa-pencil-alt"></i></span>
                        </div>
                    <input id="email" type="text" class="form-control" name="email"
                        value="{{ (old('email',$vendor['email'] ?? ''))}}">
                    </div>

                    @if($errors->has('email'))
                    <span class="form-text">{{ $errors->first('email') }}</span>
                    @endif

                </div>
            </div>

            
            <div class="form-group row {{ $errors->has('address1') ? ' text-red' : '' }}">
                <label for="address1"
                    class="col-sm-4 col-form-label">{{ sc_language_render('multi_vendor.address1') }}</label>

                <div class="col-sm-8">
                    <div class="input-group">
                        <div class="input-group-prepend">
                        <span class="input-group-text"><i class="fas fa-pencil-alt"></i></span>
                        </div>
                    <input id="address1" type="text" class="form-control" name="address1"
                        value="{{ (old('address1', $vendor['address1'] ?? ''))}}">
                    </div>
                    @if($errors->has('address1'))
                    <span class="form-text">{{ $errors->first('address1') }}</span>
                    @endif

                </div>
            </div>

             <div class="form-group row {{ $errors->has('address2') ? ' text-red' : '' }}">
                <label for="address2"
                    class="col-sm-4 col-form-label">{{ sc_language_render('multi_vendor.address2') }}</label>
                <div class="col-sm-8">
                    <div class="input-group">
                        <div class="input-group-prepend">
                        <span class="input-group-text"><i class="fas fa-pencil-alt"></i></span>
                        </div>
                    <input id="address2" type="text" class="form-control" name="address2"
                        value="{{ (old('address2', $vendor['address2'] ?? ''))}}">
                    </div>
                    @if($errors->has('address2'))
                    <span class="form-text">{{ $errors->first('address2') }}</span>
                    @endif

                </div>
            </div>



            @php
            $country = old('country', $vendor['country'] ?? '');
            @endphp

            <div class="form-group row {{ $errors->has('country') ? ' text-red' : '' }}">
                <label for="country"
                    class="col-sm-4 col-form-label">{{ sc_language_render('multi_vendor.country') }}</label>
                <div class="col-sm-8">
                    <select class="form-control country" style="width: 100%;" name="country">
                        <option>__{{ sc_language_render('multi_vendor.country') }}__</option>
                        @foreach ($countries as $k => $v)
                        <option value="{{ $k }}" {{ ($country == $k) ? 'selected':'' }}>{{ $v }}</option>
                        @endforeach
                    </select>
                    @if ($errors->has('country'))
                    <span class="form-text">
                        {{ $errors->first('country') }}
                    </span>
                    @endif
                </div>
            </div>


            <div class="form-group  row {{ $errors->has('password') ? ' text-red' : '' }}">
                <label for="password" class="col-sm-4  col-form-label">{{ sc_language_render('multi_vendor.password') }}</label>
                <div class="col-sm-8">
                    <div class="input-group">
                        <div class="input-group-prepend">
                        <span class="input-group-text"><i class="fas fa-pencil-alt"></i></span>
                        </div>
                        <input type="password"   id="password" name="password" value="{{ old('password')??'' }}" class="form-control password" placeholder="" />
                    </div>
                        @if ($errors->has('password'))
                            <span class="form-text">
                                <i class="fa fa-info-circle"></i> {{ $errors->first('password') }}
                            </span>
                        @else

                        @endif
                </div>
            </div>


            <div class="form-group  row {{ $errors->has('password_confirmation') ? ' text-red' : '' }}">
                <label for="password" class="col-sm-4  control-label">{{ sc_language_render('multi_vendor.password_confirm') }}</label>
                <div class="col-sm-8">
                    <div class="input-group">
                        <div class="input-group-prepend">
                        <span class="input-group-text"><i class="fas fa-pencil-alt"></i></span>
                        </div>
                        <input type="password"   id="password_confirmation" name="password_confirmation" value="{{ old('password_confirmation')??'' }}" class="form-control password_confirmation" placeholder="" />
                    </div>
                        @if ($errors->has('password_confirmation'))
                            <span class="form-text">
                                <i class="fa fa-info-circle"></i> {{ $errors->first('password_confirmation') }}
                            </span>
                        @else

                        @endif
                </div>
            </div>
            <input type="hidden" name="_token" value="{{ csrf_token() }}">

        <div class="form-group row mb-0">
            <div class="col-md-6 offset-md-4">
                <button class="login-btn btn btn-warning btn-block btn-lg gradient-custom-4 text-body" type="submit">
                    {{ sc_language_render('multi_vendor.title_register') }}
                </button>
            </div>
        </div>
</form>
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