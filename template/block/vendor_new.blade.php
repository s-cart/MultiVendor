<section class="section section-xxl bg-image-1">
    <div class="container">
        <h3 class="font-weight-regular">Top new vendors</h3>
        <div class="row row-30 justify-content-center">
        <div class="col-xl-12">
            <div class="slick-quote">
            <!-- Slick Carousel-->
            <div class="slick-quote-nav">
                <div class="owl-carousel owl-style-7" data-items="1" data-sm-items="2" data-xl-items="3" data-xxl-items="4" data-nav="true" data-dots="true" data-margin="30" data-autoplay="true">
                    @if (function_exists('sc_vendor_top_new') && count(sc_vendor_top_new()))
                    @foreach (sc_vendor_top_new() as $vendor)
                    <div class="item">
                    <div class="quote-minimal-figure">
                        <a href="{{ sc_link_vendor($vendor->code) }}"><img src="{{ sc_file($vendor->logo) }}" alt="" width="87" height="87"/></a>
                    </div>
                        Shop: <a href="{{ sc_link_vendor($vendor->code) }}">{{ $vendor->code }}</a>
                    </div>
                    @endforeach
                    @endif
                </div>
            </div>
            </div>
        </div>
        </div>
    </div>
</section>