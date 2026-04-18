@extends('frontend.master')
@section('title')
    {{ ___('frontend.contact_us') }}
@endsection

@section('main')

<!-- MODERN BREADCRUMB -->
<div class="breadcrumb-modern" style="position: relative; padding: 150px 0 100px; background-image: url('{{ asset(config('frontend_content.breadcrumb_bg')) }}'); background-size: cover; background-position: center; background-attachment: fixed; overflow: hidden;">
    <div class="breadcrumb-overlay" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: linear-gradient(135deg, rgba(61, 93, 148, 0.95) 0%, rgba(57, 44, 125, 0.9) 100%);"></div>
    <div class="container" style="position: relative; z-index: 2;">
        <div class="row justify-content-center">
            <div class="col-lg-8 text-center" data-aos="fade-up">
                <span class="breadcrumb-badge">{{ config('frontend_content.contact_page.breadcrumb.badge') }}</span>
                <h1 class="breadcrumb-title">{{ config('frontend_content.contact_page.breadcrumb.title') }}</h1>
                <p class="breadcrumb-description">{{ config('frontend_content.contact_page.breadcrumb.description') }}</p>
                <div class="breadcrumb-nav">
                    <a href="{{url('/')}}" class="breadcrumb-link">
                        <i class="fas fa-home"></i> Home
                    </a>
                    <span class="breadcrumb-separator">/</span>
                    <span class="breadcrumb-current">Contact Us</span>
                </div>
            </div>
        </div>
    </div>
    <div class="breadcrumb-shape shape-1"></div>
    <div class="breadcrumb-shape shape-2"></div>
</div>

<!-- CONTACT INFO CARDS -->
<div class="contact-info-section py-5" style="background: linear-gradient(to bottom, #f8f9fa 0%, #ffffff 100%);">
    <div class="container">
        <div class="text-center mb-5" data-aos="fade-up">
            <span class="section-badge-modern">{{ config('frontend_content.contact_page.section.badge') }}</span>
            <h2 class="section-title-modern">{!! config('frontend_content.contact_page.section.title') !!}</h2>
            <div class="title-underline"></div>
        </div>
        
        <div class="row g-4 mb-5">
            <!-- Phone Card -->
            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="100">
                <div class="contact-card phone-card">
                    <div class="contact-icon-wrapper">
                        <div class="contact-icon">
                            <i class="fas fa-phone-alt"></i>
                        </div>
                    </div>
                    <h3 class="contact-card-title">Phone Number</h3>
                    <p class="contact-card-info">{{ config('frontend_content.contact.phone') }}</p>
                    <a href="tel:{{ config('frontend_content.contact.phone') }}" class="contact-card-link">Call Us Now <i class="fas fa-arrow-right"></i></a>
                </div>
            </div>
            
            <!-- Email Card -->
            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="200">
                <div class="contact-card email-card">
                    <div class="contact-icon-wrapper">
                        <div class="contact-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                    </div>
                    <h3 class="contact-card-title">Email Address</h3>
                    <p class="contact-card-info">{{ config('frontend_content.contact.email') }}</p>
                    <a href="mailto:{{ config('frontend_content.contact.email') }}" class="contact-card-link">Send Email <i class="fas fa-arrow-right"></i></a>
                </div>
            </div>
            
            <!-- Location Card -->
            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="300">
                <div class="contact-card location-card">
                    <div class="contact-icon-wrapper">
                        <div class="contact-icon">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                    </div>
                    <h3 class="contact-card-title">Visit Us</h3>
                    <p class="contact-card-info">Nalopa School Campus<br>Education District</p>
                    <a href="#map-section" class="contact-card-link">View on Map <i class="fas fa-arrow-right"></i></a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- CONTACT FORM & MAP SECTION -->
<div class="contact-main-section py-5">
    <div class="container">
        <div class="row g-5">
            <!-- Contact Form -->
            <div class="col-lg-6" data-aos="fade-right">
                <div class="contact-form-wrapper">
                    <div class="form-header mb-4">
                        <span class="section-badge-modern">Send Message</span>
                        <h3 class="form-title">Leave a Message</h3>
                        <p class="form-description">Fill out the form below and we'll get back to you as soon as possible</p>
                    </div>
                    
                    <form class="modern-contact-form" id="myForm" method="post">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="form-group-modern">
                                    <label class="form-label-modern">
                                        <i class="fas fa-user"></i> Full Name
                                    </label>
                                    <input type="text" name="name" class="form-input-modern" placeholder="Enter your name" required>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group-modern">
                                    <label class="form-label-modern">
                                        <i class="fas fa-phone"></i> Phone Number
                                    </label>
                                    <input type="text" name="phone" class="form-input-modern" placeholder="Enter phone number" required>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group-modern">
                                    <label class="form-label-modern">
                                        <i class="fas fa-envelope"></i> Email Address
                                    </label>
                                    <input type="email" name="email" class="form-input-modern" placeholder="Enter email address" required>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group-modern">
                                    <label class="form-label-modern">
                                        <i class="fas fa-tag"></i> Subject
                                    </label>
                                    <input type="text" name="subject" class="form-input-modern" placeholder="Enter subject" required>
                                </div>
                            </div>
                            
                            <div class="col-12">
                                <div class="form-group-modern">
                                    <label class="form-label-modern">
                                        <i class="fas fa-comment-dots"></i> Message
                                    </label>
                                    <textarea name="message" class="form-textarea-modern" rows="6" placeholder="Write your message here..." required></textarea>
                                </div>
                            </div>
                            
                            <div class="col-12">
                                <button type="submit" class="btn-submit-modern">
                                    <span>Send Message</span>
                                    <i class="fas fa-paper-plane"></i>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Map -->
            <div class="col-lg-6" data-aos="fade-left">
                <div class="map-wrapper" id="map-section">
                    <div class="map-header mb-3">
                        <h3 class="map-title">Find Us Here</h3>
                        <p class="map-description">Located in the heart of the education district</p>
                    </div>
                    <div class="map-container">
                        <iframe src="https://www.google.com/maps/embed?pb={{ config('frontend_content.contact.map_embed') }}" width="100%" height="600" style="border:0; border-radius: 20px;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- QUICK CONTACT CTA -->
<div class="quick-contact-cta py-5" style="background: linear-gradient(135deg, #3d5d94 0%, #392C7D 100%);">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8" data-aos="fade-right">
                <h2 class="cta-title-modern">Have Questions? We're Here to Help!</h2>
                <p class="cta-description-modern">Our admissions team is available Monday through Friday, 8:00 AM - 5:00 PM</p>
            </div>
            <div class="col-lg-4 text-lg-end" data-aos="fade-left">
                <a href="tel:{{ config('frontend_content.contact.phone') }}" class="btn-cta-modern">
                    <i class="fas fa-phone-alt"></i>
                    <span>Call Now</span>
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

.title-underline {
    width: 80px;
    height: 4px;
    background: linear-gradient(to right, #3d5d94, #FFD700);
    margin: 0 auto;
}

/* Contact Cards */
.contact-card {
    background: #fff;
    padding: 40px 30px;
    border-radius: 20px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.08);
    text-align: center;
    transition: all 0.3s ease;
    border-top: 4px solid;
    height: 100%;
}

.phone-card {
    border-top-color: #3d5d94;
}

.email-card {
    border-top-color: #FFD700;
}

.location-card {
    border-top-color: #FF6B6B;
}

.contact-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 20px 60px rgba(0,0,0,0.15);
}

.contact-icon-wrapper {
    margin-bottom: 24px;
}

.contact-icon {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    background: linear-gradient(135deg, #3d5d94 0%, #392C7D 100%);
    color: #fff;
    box-shadow: 0 8px 20px rgba(61, 93, 148, 0.3);
}

.email-card .contact-icon {
    background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%);
    color: #1a1a2e;
    box-shadow: 0 8px 20px rgba(255, 215, 0, 0.3);
}

.location-card .contact-icon {
    background: linear-gradient(135deg, #FF6B6B 0%, #FF4757 100%);
    color: #fff;
    box-shadow: 0 8px 20px rgba(255, 107, 107, 0.3);
}

.contact-card-title {
    font-size: 1.4rem;
    font-weight: 700;
    color: #1a1a2e;
    margin-bottom: 12px;
}

.contact-card-info {
    font-size: 1.1rem;
    color: #7f8c8d;
    margin-bottom: 20px;
    line-height: 1.6;
}

.contact-card-link {
    color: #3d5d94;
    font-weight: 600;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s ease;
}

.contact-card-link:hover {
    color: #FFD700;
    gap: 12px;
}

/* Contact Form */
.contact-form-wrapper {
    background: linear-gradient(135deg, #3d5d94 0%, #392C7D 100%);
    padding: 50px 40px;
    border-radius: 20px;
    box-shadow: 0 20px 60px rgba(0,0,0,0.15);
}

.form-header {
    text-align: center;
}

.form-title {
    font-size: 2rem;
    font-weight: 700;
    color: #fff;
    margin-bottom: 12px;
}

.form-description {
    font-size: 1rem;
    color: rgba(255,255,255,0.85);
    margin: 0;
}

.form-group-modern {
    margin-bottom: 0;
}

.form-label-modern {
    display: flex;
    align-items: center;
    gap: 8px;
    color: #fff;
    font-weight: 600;
    margin-bottom: 10px;
    font-size: 0.95rem;
}

.form-input-modern,
.form-textarea-modern {
    width: 100%;
    padding: 14px 18px;
    border: 2px solid rgba(255,255,255,0.2);
    border-radius: 12px;
    background: rgba(255,255,255,0.1);
    color: #fff;
    font-size: 1rem;
    transition: all 0.3s ease;
    backdrop-filter: blur(10px);
}

.form-input-modern::placeholder,
.form-textarea-modern::placeholder {
    color: rgba(255,255,255,0.5);
}

.form-input-modern:focus,
.form-textarea-modern:focus {
    outline: none;
    border-color: #FFD700;
    background: rgba(255,255,255,0.15);
}

.form-textarea-modern {
    resize: vertical;
}

.btn-submit-modern {
    width: 100%;
    padding: 18px 40px;
    background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%);
    color: #1a1a2e;
    border: none;
    border-radius: 12px;
    font-size: 1.1rem;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 12px;
    transition: all 0.3s ease;
    cursor: pointer;
    box-shadow: 0 10px 30px rgba(255, 215, 0, 0.3);
}

.btn-submit-modern:hover {
    transform: translateY(-3px);
    box-shadow: 0 15px 40px rgba(255, 215, 0, 0.4);
}

/* Map Section */
.map-wrapper {
    height: 100%;
}

.map-title {
    font-size: 1.8rem;
    font-weight: 700;
    color: #1a1a2e;
    margin-bottom: 8px;
}

.map-description {
    font-size: 1rem;
    color: #7f8c8d;
    margin: 0;
}

.map-container {
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 20px 60px rgba(0,0,0,0.15);
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
    
    .section-title-modern {
        font-size: 2rem;
    }
    
    .form-title {
        font-size: 1.6rem;
    }
    
    .cta-title-modern {
        font-size: 2rem;
    }
    
    .contact-form-wrapper {
        padding: 40px 30px;
    }
}

@media (max-width: 767px) {
    .breadcrumb-title {
        font-size: 2rem;
    }
    
    .btn-submit-modern,
    .btn-cta-modern {
        width: 100%;
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
