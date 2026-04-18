@extends('frontend.master')
@section('title')
    {{ ___('frontend.about_US') }}
@endsection

@section('main')

<!-- MODERN BREADCRUMB -->
<div class="breadcrumb-modern" style="position: relative; padding: 150px 0 100px; background-image: url('{{ asset(config('frontend_content.breadcrumb_bg')) }}'); background-size: cover; background-position: center; background-attachment: fixed; overflow: hidden;">
    <div class="breadcrumb-overlay" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: linear-gradient(135deg, rgba(61, 93, 148, 0.95) 0%, rgba(57, 44, 125, 0.9) 100%);"></div>
    <div class="container" style="position: relative; z-index: 2;">
        <div class="row justify-content-center">
            <div class="col-lg-8 text-center" data-aos="fade-up">
                <span class="breadcrumb-badge">{{ config('frontend_content.about_page.breadcrumb.badge') }}</span>
                <h1 class="breadcrumb-title">{{ config('frontend_content.about_page.breadcrumb.title') }}</h1>
                <p class="breadcrumb-description">{{ config('frontend_content.about_page.breadcrumb.description') }}</p>
                <div class="breadcrumb-nav">
                    <a href="{{url('/')}}" class="breadcrumb-link">
                        <i class="fas fa-home"></i> Home
                    </a>
                    <span class="breadcrumb-separator">/</span>
                    <span class="breadcrumb-current">About Us</span>
                </div>
            </div>
        </div>
    </div>
    <div class="breadcrumb-shape shape-1"></div>
    <div class="breadcrumb-shape shape-2"></div>
</div>

<!-- INTRO SECTION -->
<div class="intro-section py-5 bg-white">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-4 mb-lg-0" data-aos="fade-right">
                <div class="intro-image-wrapper">
                    <img src="{{ asset(config('frontend_content.statement.image')) }}" alt="About Us" class="img-fluid rounded-modern">
                    <div class="intro-badge">
                        <div class="badge-content">
                            <i class="fas fa-graduation-cap"></i>
                            <div>
                                <strong>Excellence</strong>
                                <span>Since 1998</span>
                            </div>
                                </div>
                            </div>
                </div>
            </div>
            <div class="col-lg-6" data-aos="fade-left">
                <span class="section-badge-modern">{{ config('frontend_content.about_page.intro.badge') }}</span>
                <h2 class="section-title-modern">{{ config('frontend_content.about_page.intro.title') }} <br><span class="text-gradient-modern">{{ config('frontend_content.about_page.intro.title_gradient') }}</span></h2>
                <p class="section-description-modern">{{ config('frontend_content.about_page.intro.description') }}</p>
                <div class="intro-stats">
                    @foreach(config('frontend_content.about_page.intro.stats') as $stat)
                    <div class="intro-stat-item">
                        <h3>{{ $stat['number'] }}</h3>
                        <p>{{ $stat['label'] }}</p>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

<!-- VISION & MISSION SECTION -->
<div class="vm-section-modern py-5" style="background: linear-gradient(180deg, #f8f9fa 0%, #fff 100%);">
    <div class="container">
        <div class="text-center mb-5" data-aos="fade-up">
            <span class="section-badge-modern">{{ config('frontend_content.about_page.vision_mission.badge') }}</span>
            <h2 class="section-title-modern">{{ config('frontend_content.about_page.vision_mission.title') }} <span class="text-gradient-modern">{{ config('frontend_content.about_page.vision_mission.title_gradient') }}</span></h2>
            <p class="section-subtitle-modern">{{ config('frontend_content.about_page.vision_mission.subtitle') }}</p>
        </div>
        
        <div class="row g-4">
            @foreach(config('frontend_content.statement.items') as $key => $item)
            <div class="col-lg-6" data-aos="fade-up" data-aos-delay="{{ 100 + ($key * 100) }}">
                <div class="vm-card-modern {{ $key == 0 ? 'vm-vision-modern' : 'vm-mission-modern' }}">
                    <div class="vm-icon-wrapper">
                        <div class="vm-icon-modern">
                            @if($key == 0)
                            <i class="fas fa-eye"></i>
                            @else
                            <i class="fas fa-bullseye"></i>
                            @endif
                        </div>
                    </div>
                    <div class="vm-content">
                        <h3 class="vm-title-modern">{{ $item['title'] }}</h3>
                        <p class="vm-description-modern">{{ $item['description'] }}</p>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>

<!-- WHY CHOOSE US SECTION -->
<div class="why-choose-modern py-5" style="background: linear-gradient(135deg, rgba(61, 93, 148, 0.97) 0%, rgba(57, 44, 125, 0.95) 100%), url('{{ asset(config('frontend_content.why_choose.background_image')) }}'); background-size: cover; background-attachment: fixed; background-position: center;">
    <div class="container">
        <div class="text-center mb-5" data-aos="fade-up">
            <span class="section-badge-light-modern">{{ config('frontend_content.why_choose.badge') }}</span>
            <h2 class="section-title-white-modern">{{ config('frontend_content.why_choose.title') }} <span class="text-gold-modern">{{ config('frontend_content.why_choose.title_gradient') }}</span></h2>
            <p class="section-subtitle-white-modern">{{ config('frontend_content.why_choose.description') }}</p>
        </div>
        
        <div class="row g-4">
            @foreach (config('frontend_content.why_choose.items') as $key => $item)
            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="{{ 100 + ($key * 100) }}">
                <div class="service-card-modern">
                    <div class="service-icon-modern">
                        <img src="{{ asset($item['icon']) }}" alt="{{ $item['title'] }}" style="width: 60px; height: 60px; filter: brightness(0) invert(1);">
                    </div>
                    <h3 class="service-title-modern">{{ $item['title'] }}</h3>
                    <p class="service-description-modern">{{ $item['description'] }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>

<!-- ABOUT GALLERY SECTION -->
<div class="about-gallery-modern py-5">
    <div class="container">
        @foreach (config('frontend_content.about') as $key => $item)
        <div class="gallery-row-modern {{ $key % 2 == 0 ? 'gallery-row-normal' : 'gallery-row-reverse' }}" data-aos="fade-up" data-aos-delay="{{ 100 + ($key * 150) }}">
            <div class="gallery-image-wrapper">
                <img src="{{ asset($item['image']) }}" alt="{{ $item['title'] }}" class="img-fluid rounded-modern">
                <div class="gallery-overlay">
                    <i class="fas fa-search-plus"></i>
                            </div>
                        </div>
            <div class="gallery-content-wrapper">
                <div class="gallery-icon-modern">
                    <img src="{{ asset($item['icon']) }}" alt="{{ $item['title'] }}" style="width: 60px; height: 60px;">
                                </div>
                <h3 class="gallery-title-modern">{{ $item['title'] }}</h3>
                <p class="gallery-description-modern">{{ $item['description'] }}</p>
            </div>
        </div>
        @endforeach
    </div>
</div>

<!-- TEACHERS SECTION -->
<div class="teachers-section-modern py-5" style="background: linear-gradient(to bottom, #f8f9fa 0%, #fff 100%);">
    <div class="container">
        <div class="text-center mb-5" data-aos="fade-up">
            <span class="section-badge-modern">{{ config('frontend_content.teachers_section.badge') }}</span>
            <h2 class="section-title-modern">{{ config('frontend_content.teachers_section.title') }}</h2>
            <p class="section-subtitle-modern">{{ config('frontend_content.teachers_section.description') }}</p>
        </div>
        
        <div class="row g-4">
            @if(isset($data['teachers']) && count($data['teachers']) > 0)
                @foreach ($data['teachers'] as $key => $item)
                <div class="col-lg-3 col-md-4 col-sm-6" data-aos="fade-up" data-aos-delay="{{ 100 + ($key * 100) }}">
                    <div class="teacher-card-modern">
                        <div class="teacher-image-wrapper">
                            <img src="{{ @globalAsset(@$item->upload->path, '340X340.webp') }}" alt="Teacher" class="img-fluid">
                            <div class="teacher-overlay">
                                <div class="teacher-social">
                                    <a href="#"><i class="fab fa-facebook-f"></i></a>
                                    <a href="#"><i class="fab fa-twitter"></i></a>
                                    <a href="#"><i class="fab fa-linkedin-in"></i></a>
                                </div>
                            </div>
                        </div>
                        <div class="teacher-info-modern">
                            <h4>{{ @$item->first_name }} {{ @$item->last_name }}</h4>
                            <p>{{ @$item->designation->name }}</p>
                        </div>
                    </div>
                </div>
            @endforeach
            @else
                <div class="col-12 text-center">
                    <div class="empty-state-modern">
                        <i class="fas fa-users"></i>
                        <h3>{{ config('frontend_content.teachers_section.empty_state.title') }}</h3>
                        <p>{{ config('frontend_content.teachers_section.empty_state.description') }} <a href="{{ route('frontend.contact') }}" class="link-modern">{{ config('frontend_content.teachers_section.empty_state.link_text') }}</a> to learn more.</p>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- CTA SECTION -->
<div class="cta-section-modern py-5" style="background: linear-gradient(135deg, #3d5d94 0%, #392C7D 100%);">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8" data-aos="fade-right">
                <h2 class="cta-title-modern">{{ config('frontend_content.about_page.cta.title') }}</h2>
                <p class="cta-description-modern">{{ config('frontend_content.about_page.cta.description') }}</p>
            </div>
            <div class="col-lg-4 text-lg-end" data-aos="fade-left">
                <a href="{{ route('frontend.contact') }}" class="btn-cta-modern">
                    <span>{{ config('frontend_content.about_page.cta.button_text') }}</span>
                    <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>
</div>

<style>
/* Breadcrumb Modern */
.breadcrumb-modern {
    position: relative;
}

.breadcrumb-badge {
    display: inline-block;
    background: rgba(255, 215, 0, 0.2);
    color: #FFD700;
    padding: 8px 24px;
    border-radius: 50px;
    font-size: 12px;
    font-weight: 600;
    letter-spacing: 2px;
    margin-bottom: 20px;
    border: 1px solid rgba(255, 215, 0, 0.3);
}

.breadcrumb-title {
    font-size: 3.5rem;
    font-weight: 800;
    color: #fff;
    margin-bottom: 20px;
    text-shadow: 2px 4px 12px rgba(0,0,0,0.3);
}

.breadcrumb-description {
    font-size: 1.2rem;
    color: rgba(255,255,255,0.9);
    margin-bottom: 30px;
}

.breadcrumb-nav {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 15px;
}

.breadcrumb-link {
    color: #FFD700;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s ease;
}

.breadcrumb-link:hover {
    color: #FFA500;
}

.breadcrumb-separator {
    color: rgba(255,255,255,0.5);
}

.breadcrumb-current {
    color: rgba(255,255,255,0.9);
    font-weight: 600;
}

.breadcrumb-shape {
    position: absolute;
    border-radius: 50%;
    opacity: 0.1;
}

.breadcrumb-shape.shape-1 {
    width: 300px;
    height: 300px;
    background: #FFD700;
    top: -100px;
    right: -100px;
}

.breadcrumb-shape.shape-2 {
    width: 200px;
    height: 200px;
    background: #FFA500;
    bottom: -50px;
    left: -50px;
}

/* Intro Section */
.intro-image-wrapper {
    position: relative;
}

.rounded-modern {
    border-radius: 20px;
    box-shadow: 0 20px 60px rgba(0,0,0,0.15);
}

.intro-badge {
    position: absolute;
    bottom: 30px;
    left: 30px;
    background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%);
    padding: 20px 30px;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(255, 215, 0, 0.4);
}

.badge-content {
    display: flex;
    align-items: center;
    gap: 15px;
    color: #1a1a2e;
}

.badge-content i {
    font-size: 2.5rem;
}

.badge-content strong {
    display: block;
    font-size: 1.3rem;
    line-height: 1;
}

.badge-content span {
    display: block;
    font-size: 0.9rem;
}

.section-badge-modern {
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

.section-badge-light-modern {
    background: rgba(255, 215, 0, 0.2);
    color: #FFD700;
}

.section-title-modern {
    font-size: 2.5rem;
    font-weight: 800;
    color: #1a1a2e;
    line-height: 1.3;
    margin-bottom: 20px;
}

.section-title-white-modern {
    font-size: 2.5rem;
    font-weight: 800;
    color: #fff;
    line-height: 1.3;
    margin-bottom: 20px;
}

.text-gradient-modern {
    background: linear-gradient(135deg, #3d5d94 0%, #FFD700 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.text-gold-modern {
    color: #FFD700;
}

.section-description-modern {
    font-size: 1.1rem;
    color: #5a6c7d;
    line-height: 1.8;
    margin-bottom: 32px;
}

.section-subtitle-modern {
    font-size: 1.1rem;
    color: #7f8c8d;
    max-width: 700px;
    margin: 0 auto;
}

.section-subtitle-white-modern {
    font-size: 1.1rem;
    color: rgba(255,255,255,0.85);
    max-width: 700px;
    margin: 0 auto;
}

.intro-stats {
    display: flex;
    gap: 30px;
    flex-wrap: wrap;
    margin-top: 30px;
}

.intro-stat-item {
    flex: 1;
    min-width: 120px;
}

.intro-stat-item h3 {
    font-size: 2.5rem;
    font-weight: 800;
    color: #3d5d94;
    margin-bottom: 8px;
}

.intro-stat-item p {
    font-size: 0.95rem;
    color: #7f8c8d;
    margin: 0;
}

/* Vision Mission Cards */
.vm-card-modern {
    background: #fff;
    padding: 40px;
    border-radius: 20px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.08);
    height: 100%;
    transition: all 0.3s ease;
    border-top: 4px solid;
    display: flex;
    gap: 24px;
}

.vm-vision-modern {
    border-top-color: #3d5d94;
}

.vm-mission-modern {
    border-top-color: #FFD700;
}

.vm-card-modern:hover {
    transform: translateY(-10px);
    box-shadow: 0 20px 60px rgba(0,0,0,0.12);
}

.vm-icon-modern {
    width: 80px;
    height: 80px;
    border-radius: 15px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    background: linear-gradient(135deg, #3d5d94 0%, #392C7D 100%);
    color: #fff;
    flex-shrink: 0;
}

.vm-mission-modern .vm-icon-modern {
    background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%);
    color: #1a1a2e;
}

.vm-title-modern {
    font-size: 1.6rem;
    font-weight: 700;
    color: #1a1a2e;
    margin-bottom: 12px;
}

.vm-description-modern {
    font-size: 1rem;
    color: #5a6c7d;
    line-height: 1.8;
    margin: 0;
}

/* Service Cards */
.service-card-modern {
    background: rgba(255,255,255,0.1);
    backdrop-filter: blur(20px);
    padding: 40px 30px;
    border-radius: 20px;
    border: 1px solid rgba(255,255,255,0.2);
    text-align: center;
    transition: all 0.3s ease;
    height: 100%;
}

.service-card-modern:hover {
    background: rgba(255,255,255,0.15);
    transform: translateY(-10px);
}

.service-icon-modern {
    margin-bottom: 24px;
}

.service-title-modern {
    font-size: 1.4rem;
    font-weight: 700;
    color: #fff;
    margin-bottom: 16px;
}

.service-description-modern {
    font-size: 1rem;
    color: rgba(255,255,255,0.85);
    line-height: 1.7;
    margin: 0;
}

/* Gallery Section */
.gallery-row-modern {
    display: flex;
    gap: 50px;
    align-items: center;
    margin-bottom: 80px;
    flex-wrap: wrap;
}

.gallery-row-reverse {
    flex-direction: row-reverse;
}

.gallery-image-wrapper {
    flex: 1;
    min-width: 300px;
    position: relative;
    border-radius: 20px;
    overflow: hidden;
}

.gallery-image-wrapper img {
    width: 100%;
    transition: transform 0.5s ease;
}

.gallery-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(61, 93, 148, 0.9);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: all 0.3s ease;
}

.gallery-overlay i {
    font-size: 3rem;
    color: #fff;
}

.gallery-image-wrapper:hover .gallery-overlay {
    opacity: 1;
}

.gallery-image-wrapper:hover img {
    transform: scale(1.1);
}

.gallery-content-wrapper {
    flex: 1;
    min-width: 300px;
}

.gallery-icon-modern {
    margin-bottom: 24px;
}

.gallery-title-modern {
    font-size: 2rem;
    font-weight: 700;
    color: #1a1a2e;
    margin-bottom: 16px;
}

.gallery-description-modern {
    font-size: 1.05rem;
    color: #5a6c7d;
    line-height: 1.8;
    margin: 0;
}

/* Teachers Section */
.teacher-card-modern {
    background: #fff;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 10px 30px rgba(0,0,0,0.08);
    transition: all 0.3s ease;
}

.teacher-card-modern:hover {
    transform: translateY(-10px);
    box-shadow: 0 20px 60px rgba(0,0,0,0.15);
}

.teacher-image-wrapper {
    position: relative;
    overflow: hidden;
}

.teacher-image-wrapper img {
    width: 100%;
    transition: transform 0.5s ease;
}

.teacher-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(135deg, rgba(61, 93, 148, 0.95) 0%, rgba(57, 44, 125, 0.95) 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: all 0.3s ease;
}

.teacher-card-modern:hover .teacher-overlay {
    opacity: 1;
}

.teacher-card-modern:hover .teacher-image-wrapper img {
    transform: scale(1.1);
}

.teacher-social {
    display: flex;
    gap: 15px;
}

.teacher-social a {
    width: 45px;
    height: 45px;
    background: rgba(255,255,255,0.2);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    transition: all 0.3s ease;
}

.teacher-social a:hover {
    background: #FFD700;
    color: #1a1a2e;
    transform: translateY(-3px);
}

.teacher-info-modern {
    padding: 24px;
    text-align: center;
}

.teacher-info-modern h4 {
    font-size: 1.2rem;
    font-weight: 700;
    color: #1a1a2e;
    margin-bottom: 8px;
}

.teacher-info-modern p {
    font-size: 0.95rem;
    color: #7f8c8d;
    margin: 0;
}

/* Empty State */
.empty-state-modern {
    padding: 60px 30px;
    text-align: center;
}

.empty-state-modern i {
    font-size: 4rem;
    color: #3d5d94;
    margin-bottom: 24px;
}

.empty-state-modern h3 {
    font-size: 1.8rem;
    font-weight: 700;
    color: #1a1a2e;
    margin-bottom: 16px;
}

.empty-state-modern p {
    font-size: 1.1rem;
    color: #7f8c8d;
    margin: 0;
}

.link-modern {
    color: #3d5d94;
    font-weight: 600;
    text-decoration: none;
    border-bottom: 2px solid #3d5d94;
    transition: all 0.3s ease;
}

.link-modern:hover {
    color: #FFD700;
    border-bottom-color: #FFD700;
}

/* CTA Section */
.cta-title-modern {
    font-size: 2.5rem;
    font-weight: 800;
    color: #fff;
    margin-bottom: 16px;
}

.cta-description-modern {
    font-size: 1.2rem;
    color: rgba(255,255,255,0.85);
    margin: 0;
}

.btn-cta-modern {
    background: #fff;
    color: #3d5d94;
    padding: 18px 45px;
    border-radius: 50px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 12px;
    transition: all 0.3s ease;
    text-decoration: none;
    font-size: 1.1rem;
}

.btn-cta-modern:hover {
    background: #FFD700;
    color: #1a1a2e;
    transform: translateY(-3px);
    box-shadow: 0 10px 30px rgba(255, 215, 0, 0.3);
}

/* Responsive */
@media (max-width: 991px) {
    .breadcrumb-title {
        font-size: 2.5rem;
    }
    
    .section-title-modern,
    .section-title-white-modern {
        font-size: 2rem;
    }
    
    .gallery-row-modern,
    .gallery-row-reverse {
        flex-direction: column;
    }
    
    .intro-stats {
        justify-content: space-between;
    }
}
</style>

<!-- AOS Library -->
<link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        AOS.init({
            duration: 800,
            once: true,
            offset: 100
        });
    });
</script>

@endsection
