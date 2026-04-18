@extends('frontend.master')
@section('title')
    {{settingLocale('application_name')}} - Excellence in Education
@endsection

@section('main')

<!-- MODERN HERO SECTION -->
<section class="hero-modern">
    <div class="hero-slider owl-carousel" id="heroSlider">
        @foreach (config('frontend_content.sliders') ?? [] as $key => $slider)
        <div class="hero-slide" style="background-image: linear-gradient(135deg, rgba(61, 93, 148, 0.9) 0%, rgba(57, 44, 125, 0.85) 100%), url('{{ asset($slider['image']) }}'); background-size: cover; background-position: center;">
            <div class="container">
                <div class="row align-items-center min-vh-100">
                    <div class="col-lg-7">
                        <div class="hero-content" data-aos="fade-right" data-aos-delay="{{ 100 + ($key * 100) }}">
                            @php
                                $schoolName = str_replace('About ', '', config('frontend_content.about_page.breadcrumb.title'));
                            @endphp
                            <span class="hero-badge">WELCOME TO {{ strtoupper($schoolName) }}</span>
                            <h1 class="hero-title">{{ $slider['title'] }}</h1>
                            <p class="hero-description">{{ $slider['description'] }}</p>
                            <div class="hero-buttons">
                                <a href="{{ route('frontend.about') }}" class="btn btn-primary-custom">
                                    <span>Discover More</span>
                                    <i class="fas fa-arrow-right"></i>
                                </a>
                                <a href="{{ route('frontend.contact') }}" class="btn btn-outline-custom">
                                    <span>Contact Us</span>
                                    <i class="fas fa-phone"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</section>

<!-- STATS/FEATURES SECTION -->
<section class="features-stats">
    <div class="container">
        <div class="stats-wrapper" data-aos="fade-up">
            <div class="row g-0">
                @foreach(config('frontend_content.features') ?? [] as $feature)
                <div class="col-lg-3 col-md-6">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="{{ $feature['icon'] }}"></i>
                        </div>
                        <div class="stat-content">
                            <h3 class="stat-number">{{ $feature['number'] }}</h3>
                            <h4 class="stat-title">{{ $feature['title'] }}</h4>
                            <p class="stat-description">{{ $feature['description'] }}</p>
                        </div>
                    </div>
                </div>
            @endforeach
            </div>
        </div>
    </div>
</section>

<!-- ABOUT/VISION SECTION -->
<section class="about-modern py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-4 mb-lg-0" data-aos="fade-right">
                <div class="about-image-wrapper">
                    <img src="{{ asset(config('frontend_content.statement.image')) }}" alt="About Nalopa School" class="img-fluid rounded-custom">
                    <div class="about-badge">
                        <i class="fas fa-award"></i>
                        <div>
                            <strong>25+</strong>
                            <span>Years of Excellence</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6" data-aos="fade-left">
                <div class="about-content">
                    <span class="section-badge">{{ config('frontend_content.home_about.badge') }}</span>
                    <h2 class="section-title">{{ config('frontend_content.home_about.title') }} <br><span class="text-gradient">{{ config('frontend_content.home_about.title_gradient') }}</span></h2>
                    <p class="section-description">{{ config('frontend_content.home_about.description') }}</p>
                    
                    <div class="features-list">
                        @foreach(config('frontend_content.home_about.features') ?? [] as $feature)
                        <div class="feature-item">
                            <div class="feature-icon">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div class="feature-text">
                                <h5>{{ $feature['title'] }}</h5>
                                <p>{{ $feature['description'] }}</p>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <a href="{{ route('frontend.about') }}" class="btn btn-primary-custom mt-4">Learn More About Us</a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- VISION & MISSION CARDS -->
<section class="vision-mission-modern py-5 bg-light-gradient">
    <div class="container">
        <div class="text-center mb-5" data-aos="fade-up">
            <span class="section-badge">{{ config('frontend_content.statement.badge') }}</span>
            <h2 class="section-title">{{ config('frontend_content.statement.title') }} <span class="text-gradient">{{ config('frontend_content.statement.title_gradient') }}</span></h2>
            <p class="section-subtitle">{{ config('frontend_content.statement.subtitle') }}</p>
        </div>
        
        <div class="row g-4">
            @foreach(config('frontend_content.statement.items') ?? [] as $key => $item)
            <div class="col-lg-6" data-aos="fade-up" data-aos-delay="{{ 100 + ($key * 100) }}">
                <div class="vm-card {{ $key == 0 ? 'vm-vision' : 'vm-mission' }}">
                    <div class="vm-icon">
                        @if($key == 0)
                        <i class="fas fa-eye"></i>
                        @else
                        <i class="fas fa-bullseye"></i>
                        @endif
                    </div>
                    <h3 class="vm-title">{{ $item['title'] }}</h3>
                    <p class="vm-description">{{ $item['description'] }}</p>
                                </div>
                            </div>
                        @endforeach
        </div>
    </div>
</section>

<!-- CORE VALUES SECTION -->
<section class="core-values-modern py-5" style="background: linear-gradient(135deg, rgba(61, 93, 148, 0.95) 0%, rgba(57, 44, 125, 0.95) 100%), url('{{ asset(config('frontend_content.core_values.background_image')) }}'); background-size: cover; background-attachment: fixed;">
    <div class="container">
        <div class="text-center mb-5" data-aos="fade-up">
            <span class="section-badge-light">{{ config('frontend_content.core_values.badge') }}</span>
            <h2 class="section-title text-white">{{ config('frontend_content.core_values.title') }} <span class="text-gold">{{ config('frontend_content.core_values.title_gradient') }}</span></h2>
            <p class="section-subtitle text-white-80">{{ config('frontend_content.core_values.subtitle') }}</p>
        </div>
        
        <div class="row g-4">
            @php
                $valueIcons = ['fas fa-book-reader', 'fas fa-heart', 'fas fa-users'];
                $valueColors = ['#FFD700', '#FF6B6B', '#4ECDC4'];
            @endphp
            @foreach(config('frontend_content.core_values.values') ?? [] as $key => $value)
            <div class="col-lg-4" data-aos="fade-up" data-aos-delay="{{ 100 + ($key * 100) }}">
                <div class="value-card">
                    <div class="value-icon" style="background: {{ $valueColors[$key] }};">
                        <i class="{{ $valueIcons[$key] }}"></i>
                    </div>
                    <h3 class="value-title">{{ $value['title'] }}</h3>
                    @if(isset($value['motto']))
                    <div class="value-motto">{{ $value['motto'] }}</div>
                    @endif
                    <p class="value-description">{{ $value['description'] }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

<!-- PROGRAMS/EXPLORE SECTION -->
<section class="programs-modern py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-5 mb-4 mb-lg-0" data-aos="fade-right">
                <div class="programs-image-wrapper">
                    <img src="{{ asset(config('frontend_content.explore.image')) }}" alt="Programs" class="img-fluid rounded-custom">
                    <div class="programs-overlay">
                        <div class="overlay-content">
                            <i class="fas fa-graduation-cap"></i>
                            <h4>Excellence in Education</h4>
                        </div>
                    </div>
                                </div>
                                </div>
            <div class="col-lg-7" data-aos="fade-left">
                <span class="section-badge">{{ config('frontend_content.explore.badge') }}</span>
                <h2 class="section-title mb-4">{{ config('frontend_content.explore.title') }} <span class="text-gradient">{{ config('frontend_content.explore.title_gradient') }}</span></h2>
                
                <div class="programs-tabs">
                    <ul class="nav nav-pills programs-nav" role="tablist">
                        @foreach(config('frontend_content.explore.tabs') ?? [] as $key => $tab)
                        <li class="nav-item" role="presentation">
                            <button class="nav-link {{ $key == 0 ? 'active' : '' }}" data-bs-toggle="pill" data-bs-target="#{{ $tab['id'] }}" type="button" role="tab">
                                <span>{{ $tab['tab'] }}</span>
                            </button>
                        </li>
                        @endforeach
                    </ul>
                    
                    <div class="tab-content programs-content">
                        @foreach(config('frontend_content.explore.tabs') ?? [] as $key => $tab)
                        <div class="tab-pane fade {{ $key == 0 ? 'show active' : '' }}" id="{{ $tab['id'] }}" role="tabpanel">
                            <h4 class="program-title">{{ $tab['title'] }}</h4>
                            <p class="program-description">{{ $tab['description'] }}</p>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA SECTION -->
<section class="cta-modern py-5 bg-gradient-primary">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8" data-aos="fade-right">
                <h2 class="cta-title text-white mb-3">{{ config('frontend_content.home_cta.title') }}</h2>
                <p class="cta-description text-white-80">{{ config('frontend_content.home_cta.description') }}</p>
            </div>
            <div class="col-lg-4 text-lg-end" data-aos="fade-left">
                <a href="{{ route('frontend.contact') }}" class="btn btn-light-custom btn-lg">
                    <span>{{ config('frontend_content.home_cta.button_text') }}</span>
                    <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>
</section>

<!-- MODERN STYLES -->
<style>
/* Hero Modern */
.hero-modern {
    position: relative;
    overflow: hidden;
}

.hero-slider {
    position: relative;
}

.hero-slide {
    min-height: 100vh;
    background-size: cover;
    background-position: center;
    position: relative;
}

/* Owl Carousel Navigation */
.hero-slider .owl-nav {
    position: absolute;
    top: 50%;
    width: 100%;
    transform: translateY(-50%);
    z-index: 10;
}

.hero-slider .owl-nav button {
    position: absolute;
    width: 50px;
    height: 50px;
    background: rgba(255, 255, 255, 0.2) !important;
    border: 2px solid rgba(255, 255, 255, 0.5) !important;
    border-radius: 50%;
    color: #fff !important;
    font-size: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
    backdrop-filter: blur(10px);
}

.hero-slider .owl-nav button.owl-prev {
    left: 30px;
}

.hero-slider .owl-nav button.owl-next {
    right: 30px;
}

.hero-slider .owl-nav button:hover {
    background: rgba(255, 215, 0, 0.8) !important;
    border-color: #FFD700 !important;
    transform: scale(1.1);
}

.hero-slider .owl-nav button:focus {
    outline: none;
}

/* Owl Carousel Dots */
.hero-slider .owl-dots {
    position: absolute;
    bottom: 30px;
    left: 50%;
    transform: translateX(-50%);
    z-index: 10;
    display: flex;
    gap: 12px;
}

.hero-slider .owl-dots button {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.4) !important;
    border: 2px solid rgba(255, 255, 255, 0.6) !important;
    transition: all 0.3s ease;
    outline: none;
}

.hero-slider .owl-dots button.active {
    background: #FFD700 !important;
    border-color: #FFD700 !important;
    width: 30px;
    border-radius: 6px;
}

.hero-slider .owl-dots button:hover {
    background: rgba(255, 215, 0, 0.8) !important;
    border-color: #FFD700 !important;
}

.hero-content {
    padding: 40px 0;
}

.hero-badge {
    display: inline-block;
    background: rgba(255, 215, 0, 0.2);
    color: #FFD700;
    padding: 8px 24px;
    border-radius: 50px;
    font-size: 13px;
    font-weight: 600;
    letter-spacing: 2px;
    margin-bottom: 24px;
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 215, 0, 0.3);
}

.hero-title {
    font-size: 4rem;
    font-weight: 800;
    color: #fff;
    line-height: 1.2;
    margin-bottom: 24px;
    text-shadow: 2px 4px 12px rgba(0,0,0,0.3);
}

.hero-description {
    font-size: 1.2rem;
    color: rgba(255,255,255,0.9);
    line-height: 1.8;
    margin-bottom: 32px;
    max-width: 600px;
}

.hero-buttons {
    display: flex;
    gap: 16px;
    flex-wrap: wrap;
}

.btn-primary-custom {
    background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%);
    color: #1a1a2e;
    padding: 16px 40px;
    border-radius: 50px;
    font-weight: 600;
    border: none;
    display: inline-flex;
    align-items: center;
    gap: 12px;
    transition: all 0.3s ease;
    box-shadow: 0 8px 24px rgba(255, 215, 0, 0.3);
    text-decoration: none;
}

.btn-primary-custom:hover {
    transform: translateY(-3px);
    box-shadow: 0 12px 32px rgba(255, 215, 0, 0.4);
    color: #1a1a2e;
}

.btn-outline-custom {
    background: transparent;
    color: #fff;
    padding: 16px 40px;
    border-radius: 50px;
    font-weight: 600;
    border: 2px solid rgba(255,255,255,0.3);
    display: inline-flex;
    align-items: center;
    gap: 12px;
    transition: all 0.3s ease;
    backdrop-filter: blur(10px);
    text-decoration: none;
}

.btn-outline-custom:hover {
    background: rgba(255,255,255,0.1);
    border-color: #fff;
    transform: translateY(-3px);
    color: #fff;
}

/* Stats Section */
.features-stats {
    margin-top: -80px;
    position: relative;
    z-index: 10;
    padding: 0 0 80px;
}

.stats-wrapper {
    background: #fff;
    border-radius: 20px;
    box-shadow: 0 20px 60px rgba(0,0,0,0.1);
    overflow: hidden;
}

.stat-card {
    padding: 48px 32px;
    text-align: center;
    border-right: 1px solid #f0f0f0;
    transition: all 0.3s ease;
}

.stat-card:last-child {
    border-right: none;
}

.stat-card:hover {
    background: linear-gradient(135deg, #3d5d94 0%, #392C7D 100%);
    transform: translateY(-10px);
}

.stat-card:hover * {
    color: #fff !important;
}

.stat-icon {
    font-size: 3rem;
    color: #3d5d94;
    margin-bottom: 20px;
    transition: all 0.3s ease;
}

.stat-number {
    font-size: 3rem;
    font-weight: 800;
    color: #1a1a2e;
    margin-bottom: 8px;
}

.stat-title {
    font-size: 1.2rem;
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 8px;
}

.stat-description {
    font-size: 0.9rem;
    color: #7f8c8d;
    margin: 0;
}

/* About Modern */
.about-modern {
    background: #fff;
}

.about-image-wrapper {
    position: relative;
}

.about-image-wrapper img {
    border-radius: 20px;
    box-shadow: 0 20px 60px rgba(0,0,0,0.1);
}

.about-badge {
    position: absolute;
    bottom: 30px;
    left: 30px;
    background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%);
    padding: 20px 30px;
    border-radius: 15px;
    display: flex;
    align-items: center;
    gap: 15px;
    box-shadow: 0 10px 30px rgba(255, 215, 0, 0.3);
    animation: float 3s ease-in-out infinite;
}

.about-badge i {
    font-size: 2rem;
    color: #1a1a2e;
}

.about-badge strong {
    display: block;
    font-size: 1.5rem;
    color: #1a1a2e;
    line-height: 1;
}

.about-badge span {
    display: block;
    font-size: 0.9rem;
    color: #1a1a2e;
}

@keyframes float {
    0%, 100% { transform: translateY(0px); }
    50% { transform: translateY(-10px); }
}

.section-badge {
    display: inline-block;
    background: rgba(61, 93, 148, 0.1);
    color: #3d5d94;
    padding: 8px 20px;
    border-radius: 50px;
    font-size: 13px;
    font-weight: 600;
    letter-spacing: 1px;
    margin-bottom: 16px;
    text-transform: uppercase;
}

.section-badge-light {
    background: rgba(255, 215, 0, 0.2);
    color: #FFD700;
}

.section-title {
    font-size: 2.5rem;
    font-weight: 800;
    color: #1a1a2e;
    line-height: 1.3;
    margin-bottom: 20px;
}

.text-gradient {
    background: linear-gradient(135deg, #3d5d94 0%, #FFD700 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.text-gold {
    color: #FFD700;
}

.section-description {
    font-size: 1.1rem;
    color: #5a6c7d;
    line-height: 1.8;
    margin-bottom: 32px;
}

.features-list {
    margin-bottom: 24px;
}

.feature-item {
    display: flex;
    gap: 16px;
    margin-bottom: 24px;
}

.feature-icon {
    flex-shrink: 0;
    width: 48px;
    height: 48px;
    background: linear-gradient(135deg, #3d5d94 0%, #392C7D 100%);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 1.2rem;
}

.feature-text h5 {
    font-size: 1.1rem;
    font-weight: 600;
    color: #1a1a2e;
    margin-bottom: 4px;
}

.feature-text p {
    font-size: 0.95rem;
    color: #7f8c8d;
    margin: 0;
}

/* Vision Mission Cards */
.bg-light-gradient {
    background: linear-gradient(180deg, #f8f9fa 0%, #fff 100%);
}

.section-subtitle {
    font-size: 1.1rem;
    color: #7f8c8d;
    max-width: 700px;
    margin: 0 auto;
}

.vm-card {
    background: #fff;
    padding: 48px;
    border-radius: 20px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.08);
    height: 100%;
    transition: all 0.3s ease;
    border-top: 4px solid;
}

.vm-vision {
    border-top-color: #3d5d94;
}

.vm-mission {
    border-top-color: #FFD700;
}

.vm-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 20px 60px rgba(0,0,0,0.12);
}

.vm-icon {
    width: 80px;
    height: 80px;
    border-radius: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    margin-bottom: 24px;
    background: linear-gradient(135deg, #3d5d94 0%, #392C7D 100%);
    color: #fff;
}

.vm-mission .vm-icon {
    background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%);
    color: #1a1a2e;
}

.vm-title {
    font-size: 1.8rem;
    font-weight: 700;
    color: #1a1a2e;
    margin-bottom: 16px;
}

.vm-description {
    font-size: 1rem;
    color: #5a6c7d;
    line-height: 1.8;
    margin: 0;
}

/* Core Values */
.value-card {
    background: rgba(255,255,255,0.1);
    backdrop-filter: blur(20px);
    padding: 48px 32px;
    border-radius: 20px;
    border: 1px solid rgba(255,255,255,0.2);
    text-align: center;
    transition: all 0.3s ease;
    height: 100%;
}

.value-card:hover {
    background: rgba(255,255,255,0.15);
    transform: translateY(-10px);
}

.value-icon {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 24px;
    font-size: 2.5rem;
    color: #fff;
    box-shadow: 0 10px 30px rgba(0,0,0,0.2);
}

.value-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: #fff;
    margin-bottom: 16px;
}

.value-motto {
    font-size: 1.3rem;
    font-weight: 700;
    color: #FFD700;
    font-style: italic;
    margin-bottom: 16px;
}

.value-description {
    font-size: 1rem;
    color: rgba(255,255,255,0.85);
    line-height: 1.7;
    margin: 0;
}

.text-white-80 {
    color: rgba(255,255,255,0.8);
}

/* Programs */
.programs-image-wrapper {
    position: relative;
    border-radius: 20px;
    overflow: hidden;
}

.programs-overlay {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    background: linear-gradient(to top, rgba(0,0,0,0.8) 0%, transparent 100%);
    padding: 40px;
    opacity: 0;
    transition: all 0.3s ease;
}

.programs-image-wrapper:hover .programs-overlay {
    opacity: 1;
}

.overlay-content {
    color: #fff;
    text-align: center;
}

.overlay-content i {
    font-size: 3rem;
    margin-bottom: 16px;
}

.programs-nav {
    border: none;
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    margin-bottom: 32px;
}

.programs-nav .nav-link {
    background: #f8f9fa;
    color: #1a1a2e;
    border: none;
    border-radius: 50px;
    padding: 12px 28px;
    font-weight: 600;
    transition: all 0.3s ease;
}

.programs-nav .nav-link.active {
    background: linear-gradient(135deg, #3d5d94 0%, #392C7D 100%);
    color: #fff;
    box-shadow: 0 5px 20px rgba(61, 93, 148, 0.3);
}

.programs-nav .nav-link:hover {
    transform: translateY(-2px);
}

.programs-content {
    background: #f8f9fa;
    padding: 32px;
    border-radius: 15px;
}

.program-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: #1a1a2e;
    margin-bottom: 16px;
}

.program-description {
    font-size: 1rem;
    color: #5a6c7d;
    line-height: 1.8;
    margin: 0;
}

/* CTA Section */
.bg-gradient-primary {
    background: linear-gradient(135deg, #3d5d94 0%, #392C7D 100%);
}

.cta-title {
    font-size: 2.5rem;
    font-weight: 800;
}

.cta-description {
    font-size: 1.2rem;
}

.btn-light-custom {
    background: #fff;
    color: #3d5d94;
    padding: 16px 40px;
    border-radius: 50px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 12px;
    transition: all 0.3s ease;
    border: none;
    text-decoration: none;
}

.btn-light-custom:hover {
    background: #FFD700;
    color: #1a1a2e;
    transform: translateY(-3px);
    box-shadow: 0 10px 30px rgba(255, 215, 0, 0.3);
}

.rounded-custom {
    border-radius: 20px !important;
}

/* Responsive */
@media (max-width: 991px) {
    .hero-title {
        font-size: 2.5rem;
    }
    
    .section-title {
        font-size: 2rem;
    }
    
    .stat-card {
        border-right: none;
        border-bottom: 1px solid #f0f0f0;
    }
    
    .stat-card:last-child {
        border-bottom: none;
    }
    
    .cta-title {
        font-size: 2rem;
    }
    
    /* Carousel Navigation on Mobile */
    .hero-slider .owl-nav button.owl-prev {
        left: 15px;
        width: 40px;
        height: 40px;
        font-size: 16px;
    }
    
    .hero-slider .owl-nav button.owl-next {
        right: 15px;
        width: 40px;
        height: 40px;
        font-size: 16px;
    }
    
    .hero-slider .owl-dots {
        bottom: 20px;
    }
}

@media (max-width: 767px) {
    .hero-slider .owl-nav {
        display: none; /* Hide navigation on very small screens */
    }
    
    .hero-slider .owl-dots button {
        width: 10px;
        height: 10px;
    }
    
    .hero-slider .owl-dots button.active {
        width: 25px;
    }
}
</style>

<!-- AOS Animation Library -->
<link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>

@push('script')
<script>
    // Wait for all scripts to load
    window.addEventListener('load', function() {
        // Initialize AOS
        if (typeof AOS !== 'undefined') {
            AOS.init({
                duration: 800,
                once: true,
                offset: 100
            });
        }

        // Initialize Owl Carousel for Hero Slider
        if (typeof jQuery !== 'undefined' && jQuery('#heroSlider').length) {
            jQuery('#heroSlider').owlCarousel({
                items: 1,
                loop: true,
                autoplay: true,
                autoplayTimeout: 5000,
                autoplayHoverPause: true,
                nav: true,
                navText: [
                    '<i class="fas fa-chevron-left"></i>',
                    '<i class="fas fa-chevron-right"></i>'
                ],
                dots: true,
                animateOut: 'fadeOut',
                animateIn: 'fadeIn',
                smartSpeed: 1000,
                autoplaySpeed: 1000,
                navSpeed: 1000,
                dotsSpeed: 1000,
                responsive: {
                    0: {
                        items: 1,
                        nav: false,
                        dots: true
                    },
                    600: {
                        items: 1,
                        nav: true,
                        dots: true
                    },
                    1000: {
                        items: 1,
                        nav: true,
                        dots: true
                    }
                }
            });
        }
    });
</script>
@endpush

@endsection
