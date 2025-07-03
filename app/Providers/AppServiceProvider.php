<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\View\Composers\NavigationComposer;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::preventLazyLoading();
        
        // Share navigation data ke view yang butuh sidebar
        // Approach hybrid: semua view kecuali yang tidak butuh
        View::composer('*', NavigationComposer::class);
        
        // Kalau mau exclude specific views (optional):
        /*
        View::composer([
            'home',
            'dashboard.*',
            'input.*',
            'master.*',
            'report.*',
            'process.*',
            'aplikasi.*',
            'notifications.*'
        ], NavigationComposer::class);
        */
        
        // Atau kalau mau semua view (hati-hati performance):
        // View::composer('*', NavigationComposer::class);
    }
}