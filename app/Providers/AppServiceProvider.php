<?php
namespace App\Providers;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\View\Composers\NavigationComposer;
use Inertia\Inertia;

class AppServiceProvider extends ServiceProvider
{
   /**
    * Register any application services.
    */
   public function register(): void
   {
       //
   }
   
   public function boot(): void
   {
       Model::preventLazyLoading();
       
       // Share navigation data ke view yang butuh sidebar
       // Approach hybrid: semua view kecuali yang tidak butuh
       View::composer('*', NavigationComposer::class);
       
       // Share common data untuk Inertia
       Inertia::share([
           'app' => [
               'name' => config('app.name'),
               'url' => config('app.url'),
               'logo_url' => asset('img/logo-tebu.png'),
           ]
       ]);
   }
}