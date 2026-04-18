@extends('frontend.master')
@section('title')
    {{ ___('frontend.Events') }}
@endsection

@section('main')

<!-- MODERN BREADCRUMB -->
<div class="breadcrumb-modern" style="position: relative; padding: 150px 0 100px; background-image: url('{{ asset(config('frontend_content.breadcrumb_bg')) }}'); background-size: cover; background-position: center; background-attachment: fixed; overflow: hidden;">
    <div class="breadcrumb-overlay" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: linear-gradient(135deg, rgba(61, 93, 148, 0.95) 0%, rgba(57, 44, 125, 0.9) 100%);"></div>
    <div class="container" style="position: relative; z-index: 2;">
        <div class="row justify-content-center">
            <div class="col-lg-8 text-center" data-aos="fade-up">
                <span class="breadcrumb-badge">{{ config('frontend_content.events_page.breadcrumb.badge') }}</span>
                <h1 class="breadcrumb-title">{{ config('frontend_content.events_page.breadcrumb.title') }}</h1>
                <p class="breadcrumb-description">{{ config('frontend_content.events_page.breadcrumb.description') }}</p>
                <div class="breadcrumb-nav">
                    <a href="{{url('/')}}" class="breadcrumb-link">
                        <i class="fas fa-home"></i> Home
                    </a>
                    <span class="breadcrumb-separator">/</span>
                    <span class="breadcrumb-current">Events</span>
                </div>
            </div>
        </div>
    </div>
    <div class="breadcrumb-shape shape-1"></div>
    <div class="breadcrumb-shape shape-2"></div>
</div>

<!-- EVENTS SECTION -->
<div class="events-section py-5" style="background: linear-gradient(to bottom, #f8f9fa 0%, #ffffff 100%);">
    <div class="container">
        <!-- Section Header -->
        <div class="text-center mb-5" data-aos="fade-up">
            <span class="section-badge-modern">{{ config('frontend_content.events_page.section.badge') }}</span>
            <h2 class="section-title-modern">{{ config('frontend_content.events_page.section.title') }} <span class="text-gradient-modern">{{ config('frontend_content.events_page.section.title_gradient') }}</span></h2>
            <p class="section-subtitle-modern">{{ config('frontend_content.events_page.section.subtitle') }}</p>
        </div>

        <!-- Events Grid -->
        @if(isset($events) && count($events) > 0)
        <div class="row g-4 mb-5">
            @foreach ($events as $key => $item)
            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="{{ 100 + ($key * 100) }}">
                <div class="event-card-modern">
                    <!-- Event Image -->
                    <a href="{{route('frontend.events-detail', $item->id)}}" class="event-image-wrapper">
                        <img src="{{ @globalAsset(@$item->upload->path, '600X480px') }}" alt="{{ @$item->defaultTranslate->title }}" class="img-fluid">
                        <div class="event-overlay">
                            <i class="fas fa-calendar-check"></i>
                            <span>View Details</span>
                        </div>
                    </a>
                    
                    <!-- Event Date Badge -->
                    <div class="event-date-badge">
                        <div class="date-content">
                            <span class="date-day">{{ date('d', strtotime($item->date)) }}</span>
                            <span class="date-month">{{ date('M', strtotime($item->date)) }}</span>
                        </div>
                    </div>
                    
                    <!-- Event Content -->
                    <div class="event-card-content">
                        <h3 class="event-card-title">
                            <a href="{{route('frontend.events-detail', $item->id)}}">{{ @$item->defaultTranslate->title }}</a>
                        </h3>
                        <p class="event-card-excerpt">{!! Str::limit(@$item->defaultTranslate->description, 120) !!}</p>
                        
                        <!-- Event Meta -->
                        <div class="event-meta-modern">
                            <div class="event-meta-item">
                                <i class="far fa-clock"></i>
                                <span>{{ timeFormat($item->start_time) }} - {{ timeFormat($item->end_time) }}</span>
                            </div>
                            <div class="event-meta-item">
                                <i class="fas fa-map-marker-alt"></i>
                                <span>{{ @$item->defaultTranslate->address }}</span>
                            </div>
                        </div>
                        
                        <!-- Event Action -->
                        <a href="{{route('frontend.events-detail', $item->id)}}" class="event-btn-modern">
                            <span>Learn More</span>
                            <i class="fas fa-arrow-right"></i>
                        </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        
        <!-- Modern Pagination -->
        @if($events->hasPages())
        <div class="pagination-modern text-center mt-5" data-aos="fade-up">
            <nav>
                <ul class="pagination-list">
                    <!-- Previous Page -->
                    @if ($events->currentPage() == 1)
                        <li><a href="javascript:void(0)" class="page-link-modern disabled"><i class="fas fa-chevron-left"></i></a></li>
                    @else
                        <li><a href="{{ $events->previousPageUrl() }}" class="page-link-modern"><i class="fas fa-chevron-left"></i></a></li>
                    @endif

                    <!-- Page Numbers -->
                    @foreach ($events->links()['elements'][0] as $page => $url)
                        <li>
                            <a href="{{ $url }}" class="page-link-modern {{ $page == $events->currentPage() ? 'active' : '' }}">
                                {{ $page }}
                            </a>
                        </li>
                    @endforeach

                    <!-- Next Page -->
                    @if ($events->currentPage() == $events->lastPage())
                        <li><a href="javascript:void(0)" class="page-link-modern disabled"><i class="fas fa-chevron-right"></i></a></li>
                    @else
                        <li><a href="{{ $events->nextPageUrl() }}" class="page-link-modern"><i class="fas fa-chevron-right"></i></a></li>
                    @endif
                </ul>
            </nav>
        </div>
                    @endif

        @else
        <!-- Empty State -->
        <div class="empty-state-modern text-center py-5" data-aos="fade-up">
            <div class="empty-state-icon">
                <i class="{{ config('frontend_content.events_page.empty_state.icon') }}"></i>
            </div>
            <h3 class="empty-state-title">{{ config('frontend_content.events_page.empty_state.title') }}</h3>
            <p class="empty-state-description">{{ config('frontend_content.events_page.empty_state.description') }}</p>
            <a href="{{ route('frontend.contact') }}" class="btn-empty-state">
                <span>{{ config('frontend_content.events_page.empty_state.button_text') }}</span>
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>
        @endif
    </div>
</div>

<!-- EVENTS CATEGORIES INFO -->
<div class="events-info-section py-5" style="background: linear-gradient(135deg, #3d5d94 0%, #392C7D 100%);">
    <div class="container">
        <div class="row g-4">
            @foreach(config('frontend_content.events_page.categories') as $key => $category)
            <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="{{ 100 + ($key * 100) }}">
                <div class="event-category-card">
                    <div class="category-icon">
                        <i class="{{ $category['icon'] }}"></i>
                    </div>
                    <h4 class="category-title">{{ $category['title'] }}</h4>
                    <p class="category-description">{{ $category['description'] }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>

<!-- CTA SECTION -->
<div class="cta-section-modern py-5" style="background: #fff;">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8" data-aos="fade-right">
                <h2 class="cta-title-dark">{{ config('frontend_content.events_page.cta.title') }}</h2>
                <p class="cta-description-dark">{{ config('frontend_content.events_page.cta.description') }}</p>
            </div>
            <div class="col-lg-4 text-lg-end" data-aos="fade-left">
                <a href="{{ route('frontend.contact') }}" class="btn-cta-primary">
                    <i class="fas fa-bell"></i>
                    <span>{{ config('frontend_content.events_page.cta.button_text') }}</span>
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

/* Section Styles */
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

.section-title-modern {
    font-size: 2.5rem;
    font-weight: 800;
    color: #1a1a2e;
    line-height: 1.3;
    margin-bottom: 20px;
}

.text-gradient-modern {
    background: linear-gradient(135deg, #3d5d94 0%, #FFD700 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.section-subtitle-modern {
    font-size: 1.1rem;
    color: #7f8c8d;
    max-width: 700px;
    margin: 0 auto;
}

/* Event Cards */
.event-card-modern {
    background: #fff;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 10px 30px rgba(0,0,0,0.08);
    transition: all 0.3s ease;
    height: 100%;
    display: flex;
    flex-direction: column;
    position: relative;
}

.event-card-modern:hover {
    transform: translateY(-10px);
    box-shadow: 0 20px 60px rgba(0,0,0,0.15);
}

.event-image-wrapper {
    position: relative;
    overflow: hidden;
    height: 260px;
    display: block;
}

.event-image-wrapper img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.5s ease;
}

.event-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(135deg, rgba(61, 93, 148, 0.95) 0%, rgba(57, 44, 125, 0.95) 100%);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 12px;
    opacity: 0;
    transition: all 0.3s ease;
}

.event-overlay i {
    font-size: 3rem;
    color: #FFD700;
}

.event-overlay span {
    color: #fff;
    font-size: 1.1rem;
    font-weight: 600;
}

.event-card-modern:hover .event-overlay {
    opacity: 1;
}

.event-card-modern:hover .event-image-wrapper img {
    transform: scale(1.1);
}

/* Event Date Badge */
.event-date-badge {
    position: absolute;
    top: 20px;
    left: 20px;
    z-index: 10;
    background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%);
    border-radius: 15px;
    padding: 12px 20px;
    box-shadow: 0 8px 20px rgba(255, 215, 0, 0.4);
}

.date-content {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 2px;
}

.date-day {
    font-size: 1.8rem;
    font-weight: 800;
    color: #1a1a2e;
    line-height: 1;
}

.date-month {
    font-size: 0.9rem;
    font-weight: 600;
    color: #1a1a2e;
    text-transform: uppercase;
}

/* Event Content */
.event-card-content {
    padding: 30px;
    flex-grow: 1;
    display: flex;
    flex-direction: column;
}

.event-card-title {
    font-size: 1.4rem;
    font-weight: 700;
    margin-bottom: 12px;
    line-height: 1.4;
}

.event-card-title a {
    color: #1a1a2e;
    text-decoration: none;
    transition: all 0.3s ease;
}

.event-card-title a:hover {
    color: #3d5d94;
}

.event-card-excerpt {
    font-size: 0.95rem;
    color: #7f8c8d;
    line-height: 1.7;
    margin-bottom: 20px;
    flex-grow: 1;
}

/* Event Meta */
.event-meta-modern {
    display: flex;
    flex-direction: column;
    gap: 12px;
    margin-bottom: 24px;
    padding-top: 20px;
    border-top: 1px solid #f0f0f0;
}

.event-meta-item {
    display: flex;
    align-items: center;
    gap: 10px;
    color: #5a6c7d;
    font-size: 0.9rem;
}

.event-meta-item i {
    color: #3d5d94;
    font-size: 1rem;
    width: 20px;
}

/* Event Button */
.event-btn-modern {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    background: linear-gradient(135deg, #3d5d94 0%, #392C7D 100%);
    color: #fff;
    padding: 12px 28px;
    border-radius: 50px;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s ease;
    font-size: 0.95rem;
}

.event-btn-modern:hover {
    transform: translateX(5px);
    box-shadow: 0 8px 24px rgba(61, 93, 148, 0.3);
    color: #fff;
}

/* Pagination */
.pagination-list {
    display: flex;
    gap: 12px;
    list-style: none;
    padding: 0;
    margin: 0;
    justify-content: center;
    flex-wrap: wrap;
}

.page-link-modern {
    width: 45px;
    height: 45px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #fff;
    color: #3d5d94;
    border-radius: 12px;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s ease;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
}

.page-link-modern:hover {
    background: linear-gradient(135deg, #3d5d94 0%, #392C7D 100%);
    color: #fff;
    transform: translateY(-2px);
}

.page-link-modern.active {
    background: linear-gradient(135deg, #3d5d94 0%, #392C7D 100%);
    color: #fff;
}

.page-link-modern.disabled {
    opacity: 0.5;
    pointer-events: none;
}

/* Empty State */
.empty-state-modern {
    padding: 80px 30px;
}

.empty-state-icon {
    margin-bottom: 30px;
}

.empty-state-icon i {
    font-size: 6rem;
    color: #3d5d94;
    opacity: 0.3;
}

.empty-state-title {
    font-size: 2rem;
    font-weight: 700;
    color: #1a1a2e;
    margin-bottom: 16px;
}

.empty-state-description {
    font-size: 1.1rem;
    color: #7f8c8d;
    margin-bottom: 32px;
    max-width: 500px;
    margin-left: auto;
    margin-right: auto;
}

.btn-empty-state {
    display: inline-flex;
    align-items: center;
    gap: 12px;
    background: linear-gradient(135deg, #3d5d94 0%, #392C7D 100%);
    color: #fff;
    padding: 16px 40px;
    border-radius: 50px;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-empty-state:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 30px rgba(61, 93, 148, 0.3);
    color: #fff;
}

/* Event Categories */
.event-category-card {
    background: rgba(255,255,255,0.1);
    backdrop-filter: blur(20px);
    padding: 40px 30px;
    border-radius: 20px;
    border: 1px solid rgba(255,255,255,0.2);
    text-align: center;
    transition: all 0.3s ease;
}

.event-category-card:hover {
    background: rgba(255,255,255,0.15);
    transform: translateY(-5px);
}

.category-icon {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 20px;
    box-shadow: 0 10px 30px rgba(255, 215, 0, 0.3);
}

.category-icon i {
    font-size: 2rem;
    color: #1a1a2e;
}

.category-title {
    font-size: 1.3rem;
    font-weight: 700;
    color: #fff;
    margin-bottom: 12px;
}

.category-description {
    font-size: 0.95rem;
    color: rgba(255,255,255,0.85);
    margin: 0;
}

/* CTA Section */
.cta-title-dark {
    font-size: 2.5rem;
    font-weight: 800;
    color: #1a1a2e;
    margin-bottom: 16px;
}

.cta-description-dark {
    font-size: 1.2rem;
    color: #7f8c8d;
    margin: 0;
}

.btn-cta-primary {
    background: linear-gradient(135deg, #3d5d94 0%, #392C7D 100%);
    color: #fff;
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

.btn-cta-primary:hover {
    background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%);
    color: #1a1a2e;
    transform: translateY(-3px);
    box-shadow: 0 10px 30px rgba(255, 215, 0, 0.3);
}

/* Responsive */
@media (max-width: 991px) {
    .breadcrumb-title {
        font-size: 2.5rem;
    }
    
    .section-title-modern {
        font-size: 2rem;
    }
    
    .cta-title-dark {
        font-size: 2rem;
        margin-bottom: 20px;
    }
}

@media (max-width: 767px) {
    .breadcrumb-title {
        font-size: 2rem;
    }
    
    .btn-cta-primary,
    .btn-empty-state {
        width: 100%;
        justify-content: center;
    }
    
    .event-date-badge {
        top: 15px;
        left: 15px;
        padding: 10px 16px;
    }
    
    .date-day {
        font-size: 1.5rem;
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
