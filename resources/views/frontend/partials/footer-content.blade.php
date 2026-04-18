
    <!-- FOOTER::START  -->
    <footer class="home_three_footer" style="background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%); position: relative; overflow: hidden;">
        <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background-image: url('data:image/svg+xml,%3Csvg width=\'60\' height=\'60\' viewBox=\'0 0 60 60\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cg fill=\'none\' fill-rule=\'evenodd\'%3E%3Cg fill=\'%23ffffff\' fill-opacity=\'0.03\'%3E%3Cpath d=\'M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z\'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E'); opacity: 0.5;"></div>
        <div class="main_footer_wrap" style="padding: 80px 0 40px; position: relative; z-index: 1;">
            <div class="container">
                 <div class="row">
                    <div class="col-xl-4 col-lg-4 col-md-6 mb-4">
                        <div class="footer_widget">
                            <div class="footer_logo mb-4" style="background: #fff; padding: 20px; border-radius: 15px; display: inline-block; box-shadow: 0 10px 25px rgba(0,0,0,0.2);">
                                <a href="{{ route('frontend.home') }}">
                                    <img height="60" src="{{ @globalAsset(setting('dark_logo'), '154X38.webp') }}" alt="Logo" style="max-height: 60px;">
                                </a>
                            </div>
                            <p class="description_text" style="color: rgba(255,255,255,0.8); line-height: 1.8; margin-top: 20px; font-size: 15px;">Empowering students to achieve excellence through quality education and character development. Building tomorrow's leaders today.</p>
                            <div class="social__Links mt-4" style="display: flex; gap: 12px;">
                                @foreach ($sections['social_links']->data ?? [] as $item)
                                    <a target="_blank" href="{{ $item['link'] }}" style="width: 45px; height: 45px; background: rgba(255,255,255,0.1); backdrop-filter: blur(10px); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #FFD700; font-size: 18px; transition: all 0.3s ease; border: 1px solid rgba(255,255,255,0.2);"><i class="{{ $item['icon'] }}"></i></a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-2 col-lg-2 col-md-6 mb-4">
                        <div class="footer_widget">
                            <div class="footer_title mb-4">
                                <h3 style="color: #FFD700; font-size: 1.3rem; font-weight: 700; position: relative; padding-bottom: 15px;">{{ ___('frontend.Menus') }}</h3>
                                <div style="width: 50px; height: 3px; background: linear-gradient(to right, #FFD700, #FFA500);"></div>
                            </div>
                            <ul class="footer_links" style="list-style: none; padding: 0; margin: 0;">
                                <li style="margin-bottom: 12px;"><a href="{{ route('frontend.home') }}" style="color: rgba(255,255,255,0.8); text-decoration: none; transition: all 0.3s ease; display: inline-flex; align-items: center; font-size: 15px;"><i class="fas fa-chevron-right" style="font-size: 10px; margin-right: 10px; color: #FFD700;"></i>{{ ___('frontend.Home') }}</a></li>
                                <li style="margin-bottom: 12px;"><a href="{{ route('frontend.about') }}" style="color: rgba(255,255,255,0.8); text-decoration: none; transition: all 0.3s ease; display: inline-flex; align-items: center; font-size: 15px;"><i class="fas fa-chevron-right" style="font-size: 10px; margin-right: 10px; color: #FFD700;"></i>{{ ___('frontend.About') }}</a></li>
                                <li style="margin-bottom: 12px;"><a href="{{ route('frontend.news') }}" style="color: rgba(255,255,255,0.8); text-decoration: none; transition: all 0.3s ease; display: inline-flex; align-items: center; font-size: 15px;"><i class="fas fa-chevron-right" style="font-size: 10px; margin-right: 10px; color: #FFD700;"></i>{{ ___('frontend.News') }}</a></li>
                                <li style="margin-bottom: 12px;"><a href="{{ route('frontend.events') }}" style="color: rgba(255,255,255,0.8); text-decoration: none; transition: all 0.3s ease; display: inline-flex; align-items: center; font-size: 15px;"><i class="fas fa-chevron-right" style="font-size: 10px; margin-right: 10px; color: #FFD700;"></i>{{ ___('frontend.Events') }}</a></li>
                                <li style="margin-bottom: 12px;"><a href="{{ route('frontend.result') }}" style="color: rgba(255,255,255,0.8); text-decoration: none; transition: all 0.3s ease; display: inline-flex; align-items: center; font-size: 15px;"><i class="fas fa-chevron-right" style="font-size: 10px; margin-right: 10px; color: #FFD700;"></i>{{ ___('frontend.Result') }}</a></li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-xl-2 col-lg-2 col-md-6 mb-4">
                        <div class="footer_widget">
                            <div class="footer_title mb-4">
                                <h3 style="color: #FFD700; font-size: 1.3rem; font-weight: 700; position: relative; padding-bottom: 15px;">{{ ___('frontend.Pages') }}</h3>
                                <div style="width: 50px; height: 3px; background: linear-gradient(to right, #FFD700, #FFA500);"></div>
                            </div>
                            <ul class="footer_links" style="list-style: none; padding: 0; margin: 0;">
                                @foreach ($footer_pages as $page)
                                    <li style="margin-bottom: 12px;"><a href="{{ route('frontend.page',$page->slug) }}" style="color: rgba(255,255,255,0.8); text-decoration: none; transition: all 0.3s ease; display: inline-flex; align-items: center; font-size: 15px;"><i class="fas fa-chevron-right" style="font-size: 10px; margin-right: 10px; color: #FFD700;"></i>{{ @$page->defaultTranslate->name }}</a></li>
                                @endforeach
                            </ul>
                        </div>
                    </div>

                    <div class="col-xl-4 col-lg-4 col-md-6 mb-4">
                        <div class="footer_widget">
                            <div class="footer_title mb-4">
                                <h3 style="color: #FFD700; font-size: 1.3rem; font-weight: 700; position: relative; padding-bottom: 15px;">{{ ___('frontend.subscribe_to_newsletter') }}</h3>
                                <div style="width: 50px; height: 3px; background: linear-gradient(to right, #FFD700, #FFA500);"></div>
                            </div>
                            <p class="subscribe_text" style="color: rgba(255,255,255,0.8); margin-bottom: 20px; font-size: 15px;">{{ ___('frontend.join_us_and_get_weekly_inspiration') }}</p>
                            <div class="subcribe-form mb_20 theme_mailChimp2">
                                <form action="" method="get" class="subscription relative" style="position: relative;">
                                    <input name="email" class="email form-control" placeholder="{{ ___('frontend.type_email_address') }}" onfocus="this.placeholder = ''" onblur="this.placeholder = 'Type e-mail address…'" required="" type="email" style="background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2); color: #fff; padding: 15px 120px 15px 20px; border-radius: 50px; font-size: 14px; backdrop-filter: blur(10px);">
                                    <button type="submit" class="submit-btn" style="position: absolute; right: 5px; top: 5px; background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%); color: #2c3e50; border: none; padding: 10px 25px; border-radius: 50px; font-weight: 600; font-size: 14px; transition: all 0.3s ease; box-shadow: 0 5px 15px rgba(255, 215, 0, 0.3);">{{ ___('frontend.Subscribe') }}</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="copyright_area" style="padding: 25px 0; background: rgba(0,0,0,0.2); border-top: 1px solid rgba(255,255,255,0.1);">
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <div class="copy_right_text d-flex align-items-center gap_20 flex-wrap justify-content-center">
                            <p style="color: rgba(255,255,255,0.7); margin: 0; font-size: 14px;">© 2024 <span style="color: #FFD700; font-weight: 600;">Nalopa School</span>. All Rights Reserved | Designed with <i class="fas fa-heart" style="color: #FF5170;"></i> for Excellence</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </footer>
    <!-- FOOTER::END  -->

    <style>
    .footer_links a:hover {
        color: #FFD700 !important;
        padding-left: 5px;
    }
    .social__Links a:hover {
        background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%) !important;
        color: #2c3e50 !important;
        transform: translateY(-3px);
        box-shadow: 0 8px 20px rgba(255, 215, 0, 0.4) !important;
    }
    .submit-btn:hover {
        transform: scale(1.05);
        box-shadow: 0 8px 20px rgba(255, 215, 0, 0.5) !important;
    }
    </style>
