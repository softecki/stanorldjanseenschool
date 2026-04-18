@extends('frontend.master')
@section('title')
    {{ ___('frontend.News') }}
@endsection

@section('main')

<!-- MODERN BREADCRUMB -->
<div class="breadcrumb-modern" style="position: relative; padding: 150px 0 100px; background-image: url('{{ asset(config('frontend_content.breadcrumb_bg')) }}'); background-size: cover; background-position: center; background-attachment: fixed; overflow: hidden;">
    <div class="breadcrumb-overlay" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: linear-gradient(135deg, rgba(61, 93, 148, 0.95) 0%, rgba(57, 44, 125, 0.9) 100%);"></div>
    <div class="container" style="position: relative; z-index: 2;">
        <div class="row justify-content-center">
            <div class="col-lg-8 text-center" data-aos="fade-up">
                <span class="breadcrumb-badge">{{ config('frontend_content.news_page.breadcrumb.badge') }}</span>
                <h1 class="breadcrumb-title">{{ config('frontend_content.news_page.breadcrumb.title') }}</h1>
                <p class="breadcrumb-description">{{ config('frontend_content.news_page.breadcrumb.description') }}</p>
                <div class="breadcrumb-nav">
                    <a href="{{url('/')}}" class="breadcrumb-link">
                        <i class="fas fa-home"></i> Home
                    </a>
                    <span class="breadcrumb-separator">/</span>
                    <span class="breadcrumb-current">News</span>
                </div>
            </div>
        </div>
    </div>
    <div class="breadcrumb-shape shape-1"></div>
    <div class="breadcrumb-shape shape-2"></div>
</div>

<!-- NEWS SECTION -->
<div class="news-section py-5" style="background: linear-gradient(to bottom, #f8f9fa 0%, #ffffff 100%);">
    <div class="container">
        <div class="text-center mb-5" data-aos="fade-up">
            <span class="section-badge-modern">{{ config('frontend_content.news_page.section.badge') }}</span>
            <h2 class="section-title-modern">{{ config('frontend_content.news_page.section.title') }} <span class="text-gradient-modern">{{ config('frontend_content.news_page.section.title_gradient') }}</span></h2>
            <p class="section-subtitle-modern">{{ config('frontend_content.news_page.section.subtitle') }}</p>
        </div>
        
        <!-- Featured News -->
        <div class="featured-news mb-5" data-aos="fade-up">
            <div class="row g-0 bg-white rounded-modern shadow-modern overflow-hidden">
                <div class="col-lg-7">
                    <div class="featured-news-image" style="background: url('{{ config('frontend_content.sample_news.featured.image') }}') center/cover; height: 100%; min-height: 400px;"></div>
                </div>
                <div class="col-lg-5">
                    <div class="featured-news-content">
                        <span class="news-badge featured-badge">{{ config('frontend_content.sample_news.featured.badge') }}</span>
                        <h3 class="featured-news-title">{{ config('frontend_content.sample_news.featured.title') }}</h3>
                        <div class="news-meta">
                            <span><i class="far fa-calendar-alt"></i> {{ config('frontend_content.sample_news.featured.date') }}</span>
                            <span><i class="far fa-user"></i> {{ config('frontend_content.sample_news.featured.author') }}</span>
                        </div>
                        <p class="featured-news-excerpt">{{ config('frontend_content.sample_news.featured.excerpt') }}</p>
                        <a href="#" class="btn-read-more">
                            <span>Read Full Story</span>
                            <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- News Grid -->
        <div class="row g-4">
            @foreach(config('frontend_content.sample_news.articles') as $key => $article)
            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="{{ 100 + (($key % 3) * 100) }}">
                <div class="news-card-modern">
                    <div class="news-card-image">
                        <img src="{{ $article['image'] }}" alt="News" class="img-fluid">
                        <span class="news-badge category-badge {{ strtolower($article['category']) == 'sports' ? 'sports-badge' : '' }} {{ strtolower($article['category']) == 'event' ? 'event-badge' : '' }} {{ strtolower($article['category']) == 'community' ? 'community-badge' : '' }} {{ strtolower($article['category']) == 'announcement' ? 'announcement-badge' : '' }}">{{ $article['category'] }}</span>
                    </div>
                    <div class="news-card-content">
                        <div class="news-meta-small">
                            <span><i class="far fa-calendar-alt"></i> {{ $article['date'] }}</span>
                        </div>
                        <h4 class="news-card-title">{{ $article['title'] }}</h4>
                        <p class="news-card-excerpt">{{ $article['excerpt'] }}</p>
                        <a href="#" class="news-card-link">Read More <i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        
        <!-- Pagination -->
        <div class="pagination-modern text-center mt-5" data-aos="fade-up">
            <nav>
                <ul class="pagination-list">
                    <li><a href="#" class="page-link-modern disabled"><i class="fas fa-chevron-left"></i></a></li>
                    <li><a href="#" class="page-link-modern active">1</a></li>
                    <li><a href="#" class="page-link-modern">2</a></li>
                    <li><a href="#" class="page-link-modern">3</a></li>
                    <li><a href="#" class="page-link-modern">4</a></li>
                    <li><a href="#" class="page-link-modern"><i class="fas fa-chevron-right"></i></a></li>
                </ul>
            </nav>
        </div>
    </div>
</div>

<!-- NEWSLETTER SECTION -->
<div class="newsletter-section py-5" style="background: linear-gradient(135deg, #3d5d94 0%, #392C7D 100%);">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-7" data-aos="fade-right">
                <div class="newsletter-content">
                    <i class="{{ config('frontend_content.news_page.newsletter.icon') }} newsletter-icon"></i>
                    <h2 class="newsletter-title">{{ config('frontend_content.news_page.newsletter.title') }}</h2>
                    <p class="newsletter-description">{{ config('frontend_content.news_page.newsletter.description') }}</p>
                </div>
            </div>
            <div class="col-lg-5" data-aos="fade-left">
                <form class="newsletter-form">
                    <div class="newsletter-input-group">
                        <input type="email" class="newsletter-input" placeholder="{{ config('frontend_content.news_page.newsletter.placeholder') }}" required>
                        <button type="submit" class="newsletter-button">
                            <span>{{ config('frontend_content.news_page.newsletter.button') }}</span>
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </div>
                </form>
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

.rounded-modern {
    border-radius: 20px;
}

.shadow-modern {
    box-shadow: 0 20px 60px rgba(0,0,0,0.15);
}

/* Featured News */
.featured-news-content {
    padding: 50px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    height: 100%;
}

.news-badge {
    display: inline-block;
    padding: 6px 16px;
    border-radius: 50px;
    font-size: 12px;
    font-weight: 600;
    letter-spacing: 1px;
    text-transform: uppercase;
    margin-bottom: 20px;
}

.featured-badge {
    background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%);
    color: #1a1a2e;
}

.featured-news-title {
    font-size: 2rem;
    font-weight: 700;
    color: #1a1a2e;
    margin-bottom: 20px;
    line-height: 1.4;
}

.news-meta {
    display: flex;
    gap: 24px;
    margin-bottom: 20px;
    flex-wrap: wrap;
}

.news-meta span {
    color: #7f8c8d;
    font-size: 0.9rem;
    display: flex;
    align-items: center;
    gap: 8px;
}

.featured-news-excerpt {
    font-size: 1.05rem;
    color: #5a6c7d;
    line-height: 1.7;
    margin-bottom: 24px;
}

.btn-read-more {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    background: linear-gradient(135deg, #3d5d94 0%, #392C7D 100%);
    color: #fff;
    padding: 14px 32px;
    border-radius: 50px;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-read-more:hover {
    transform: translateX(5px);
    box-shadow: 0 8px 24px rgba(61, 93, 148, 0.3);
    color: #fff;
}

/* News Cards */
.news-card-modern {
    background: #fff;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 10px 30px rgba(0,0,0,0.08);
    transition: all 0.3s ease;
    height: 100%;
    display: flex;
    flex-direction: column;
}

.news-card-modern:hover {
    transform: translateY(-10px);
    box-shadow: 0 20px 60px rgba(0,0,0,0.15);
}

.news-card-image {
    position: relative;
    overflow: hidden;
    height: 240px;
}

.news-card-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.5s ease;
}

.news-card-modern:hover .news-card-image img {
    transform: scale(1.1);
}

.category-badge {
    position: absolute;
    top: 20px;
    left: 20px;
    background: linear-gradient(135deg, #3d5d94 0%, #392C7D 100%);
    color: #fff;
}

.sports-badge {
    background: linear-gradient(135deg, #FF6B6B 0%, #FF4757 100%);
}

.event-badge {
    background: linear-gradient(135deg, #4ECDC4 0%, #44A08D 100%);
}

.community-badge {
    background: linear-gradient(135deg, #A770EF 0%, #CF8BF3 100%);
}

.announcement-badge {
    background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%);
    color: #1a1a2e;
}

.news-card-content {
    padding: 28px;
    flex-grow: 1;
    display: flex;
    flex-direction: column;
}

.news-meta-small {
    margin-bottom: 12px;
}

.news-meta-small span {
    color: #7f8c8d;
    font-size: 0.85rem;
    display: flex;
    align-items: center;
    gap: 6px;
}

.news-card-title {
    font-size: 1.3rem;
    font-weight: 700;
    color: #1a1a2e;
    margin-bottom: 12px;
    line-height: 1.4;
}

.news-card-excerpt {
    font-size: 0.95rem;
    color: #7f8c8d;
    line-height: 1.7;
    margin-bottom: 20px;
    flex-grow: 1;
}

.news-card-link {
    color: #3d5d94;
    font-weight: 600;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s ease;
}

.news-card-link:hover {
    color: #FFD700;
    gap: 12px;
}

/* Pagination */
.pagination-list {
    display: flex;
    gap: 12px;
    list-style: none;
    padding: 0;
    margin: 0;
    justify-content: center;
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

/* Newsletter Section */
.newsletter-content {
    display: flex;
    align-items: flex-start;
    gap: 24px;
}

.newsletter-icon {
    font-size: 4rem;
    color: #FFD700;
    flex-shrink: 0;
}

.newsletter-title {
    font-size: 2.2rem;
    font-weight: 800;
    color: #fff;
    margin-bottom: 12px;
}

.newsletter-description {
    font-size: 1.1rem;
    color: rgba(255,255,255,0.85);
    margin: 0;
}

.newsletter-input-group {
    display: flex;
    gap: 12px;
}

.newsletter-input {
    flex: 1;
    padding: 16px 24px;
    border: 2px solid rgba(255,255,255,0.3);
    border-radius: 50px;
    background: rgba(255,255,255,0.1);
    color: #fff;
    font-size: 1rem;
    transition: all 0.3s ease;
    backdrop-filter: blur(10px);
}

.newsletter-input::placeholder {
    color: rgba(255,255,255,0.6);
}

.newsletter-input:focus {
    outline: none;
    border-color: #FFD700;
    background: rgba(255,255,255,0.15);
}

.newsletter-button {
    padding: 16px 36px;
    background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%);
    color: #1a1a2e;
    border: none;
    border-radius: 50px;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 10px;
    transition: all 0.3s ease;
    cursor: pointer;
    white-space: nowrap;
}

.newsletter-button:hover {
    transform: translateX(3px);
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
    
    .featured-news-content {
        padding: 40px 30px;
    }
    
    .featured-news-title {
        font-size: 1.6rem;
    }
    
    .newsletter-content {
        flex-direction: column;
        text-align: center;
        margin-bottom: 30px;
    }
    
    .newsletter-icon {
        margin: 0 auto;
    }
}

@media (max-width: 767px) {
    .breadcrumb-title {
        font-size: 2rem;
    }
    
    .newsletter-input-group {
        flex-direction: column;
    }
    
    .newsletter-button {
        justify-content: center;
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
