<?php

namespace App\Providers;

use App\Models\WebsiteSetup\Page;
use App\Models\SystemNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Pagination\Paginator;
use Modules\MainApp\Entities\School;
use Modules\MainApp\Entities\Package;
use App\Models\WebsiteSetup\Subscribe;
use Illuminate\Support\ServiceProvider;
use App\Models\WebsiteSetup\PageSections;
use Modules\MainApp\Entities\Subscription;
use Modules\MainApp\Enums\PackagePaymentType;
use Stancl\Tenancy\Events\TenancyBootstrapped;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {

        // $this->app['events']->listen(TenancyBootstrapped::class, function ($event) {
            // view()->composer('*', function ($view) {

            //     try {

            //         $subscriber = Subscribe::count();
            //         $sections   = PageSections::with('upload')->get();

            //         $sectionArr = [];
            //         foreach($sections as $section){
            //             $sectionArr[$section->key]   = $section;
            //         }

            //         $view->with([
            //             'sections'   => $sectionArr,
            //             'subscriber' => $subscriber,
            //         ]);
            //     } catch (\Exception $e) {
            //         $view->with([
            //             'sections'   => [],
            //             'subscriber' => 0,
            //         ]);
            //     }
            // });
        // });

    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {

        if(env('APP_SAAS')):

            $this->app['events']->listen(TenancyBootstrapped::class, function ($event) {
                view()->composer('*',  function ($view) {

                        try {

                        $subscriber = Subscribe::count();
                        $sections   = PageSections::with('upload')->get();

                        $sectionArr = [];
                        foreach($sections as $section){
                            $sectionArr[$section->key]   = $section;
                        }

                        $view->with([
                            'sections'   => $sectionArr,
                            'subscriber' => $subscriber,
                        ]);
                    } catch (\Exception $e) {
                        $view->with([
                            'sections'   => [],
                            'subscriber' => 0,
                        ]);
                    }



                });

            });
        else:
            view()->composer('*',  function ($view) {

                try {

                $subscriber = Subscribe::count();
                $sections   = PageSections::with('upload')->get();

                $sectionArr = [];
                foreach($sections as $section){
                    $sectionArr[$section->key]   = $section;
                }

                $view->with([
                    'sections'   => $sectionArr,
                    'subscriber' => $subscriber,
                ]);
            } catch (\Exception $e) {
                $view->with([
                    'sections'   => [],
                    'subscriber' => 0,
                ]);
            }
        });

        view()->composer(['backend.partials.header','parent-panel.partials.header'],  function ($view) {

                try {
                    $notifications = SystemNotification::myNotification();
                    $view->with([
                        'notifications'   => $notifications
                    ]);
                }
                catch (\Exception $e) {
                    $view->with([
                        'notifications'   => []
                    ]);
                }
        });

        view()->composer(['frontend.partials.footer-content'],  function ($view) {
            try {
                $footer_pages = Page::where('menu_show','footer')->get(['id','name','slug']);
                $view->with([
                    'footer_pages'   => $footer_pages
                ]);
            }
            catch (\Exception $e) {
                $view->with([
                    'footer_pages'   => []
                ]);
            }
        });

        view()->composer(['frontend.partials.menu'],  function ($view) {
            try {
                $footer_pages = Page::where('menu_show','header')->get(['id','name','slug']);
                $view->with([
                    'header_pages'   => $footer_pages
                ]);
            }
            catch (\Exception $e) {
                $view->with([
                    'header_pages'   => []
                ]);
            }
        });


        endif;

        if(env('APP_HTTPS')==true){
            URL::forceScheme('https');
        }
        Paginator::useBootstrap();
    }
}
