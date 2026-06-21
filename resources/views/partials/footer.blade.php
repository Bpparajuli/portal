@php
    $siteName = \App\Models\Setting::getValue('site_name', 'Idea Consultancy');
    $siteEmail = \App\Models\Setting::getValue('site_email', 'info@ideaconsultancy.com');
    $sitePhone = \App\Models\Setting::getValue('site_phone', '+977 9761799575');
    $siteAddress = \App\Models\Setting::getValue('address', 'Baneshwor-10, Kathmandu, Nepal');
    $copyrightText = \App\Models\Setting::getValue('copyright_text', '&copy; ' . date('Y') . ' Idea Consultancy. All rights reserved.');

    $socialLinks = [
        'facebook'  => ['url' => \App\Models\Setting::getValue('social_facebook', ''), 'icon' => 'fab fa-facebook-f'],
        'instagram' => ['url' => \App\Models\Setting::getValue('social_instagram', ''), 'icon' => 'fab fa-instagram'],
        'tiktok'    => ['url' => \App\Models\Setting::getValue('social_tiktok', ''), 'icon' => 'fab fa-tiktok'],
        'youtube'   => ['url' => \App\Models\Setting::getValue('social_youtube', ''), 'icon' => 'fab fa-youtube'],
        'twitter'   => ['url' => \App\Models\Setting::getValue('social_twitter', ''), 'icon' => 'fab fa-twitter'],
        'linkedin'  => ['url' => \App\Models\Setting::getValue('social_linkedin', ''), 'icon' => 'fab fa-linkedin-in'],
    ];
@endphp
<footer class="bg-dark text-white pt-5 pb-4"
    style="background: linear-gradient(35deg, #1a0262 0%, #000000 40%, #820b5c 100%);">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-4 col-md-6">
                <h4 class="mb-3 fw-bold">{{ $siteName }}</h4>
                <p class="text-white-50 mb-3">Your trusted partner for educational and career guidance. Helping students achieve their dreams since 2020.</p>
                <div class="d-flex gap-2 mt-3">
                    @foreach($socialLinks as $social)
                        @if($social['url'])
                        <a href="{{ $social['url'] }}" class="text-white d-inline-flex align-items-center justify-content-center" target="_blank"
                            style="width:36px;height:36px;border-radius:50%;background:rgba(255,255,255,0.1);transition:all 0.3s;"><i class="{{ $social['icon'] }}"></i></a>
                        @endif
                    @endforeach
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <h5 class="mb-3 fw-semibold"><i class="fas fa-building me-2"></i> Putalisadak Branch</h5>
                <div class="mb-2 text-white-50"><i class="fas fa-map-marker-alt me-2"></i>Putalisadak, Kathmandu, Nepal</div>
                <div class="mb-2 text-white-50"><i class="fas fa-phone me-2"></i>+977 9761799575 / 01-4547547</div>
                <div class="text-white-50"><i class="fas fa-envelope me-2"></i>admin@ideacs.com.np</div>
            </div>
            <div class="col-lg-4 col-md-6">
                <h5 class="mb-3 fw-semibold"><i class="fas fa-building me-2"></i> New Baneshwor Branch</h5>
                <div class="mb-2 text-white-50"><i class="fas fa-map-marker-alt me-2"></i>New Baneshwor, Kathmandu, Nepal</div>
                <div class="mb-2 text-white-50"><i class="fas fa-phone me-2"></i>+977 9705547547 / 01-5318333</div>
                <div class="text-white-50"><i class="fas fa-envelope me-2"></i>admin@ideacs.com.np</div>
            </div>
        </div>
        <hr class="mt-4 mb-3" style="border-color: rgba(255,255,255,0.1);">
        <div class="row align-items-center">
            <div class="col-md-6 text-center text-md-start">
                <p class="mb-0 text-white-50 small">{!! $copyrightText !!}</p>
            </div>
            <div class="col-md-6 text-center text-md-end mt-2 mt-md-0">
                <p class="mb-0 text-white-50 small"><i class="fas fa-heart text-danger"></i> Designed with care for students</p>
            </div>
        </div>
    </div>
</footer>
