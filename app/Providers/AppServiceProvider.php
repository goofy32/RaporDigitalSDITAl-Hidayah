<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\ProfilSekolah;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;



class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(RaporTemplateProcessor::class, function ($app) {
            return new RaporTemplateProcessor();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot()
    {
        View::composer('*', function ($view) {
            $schoolProfile = ProfilSekolah::first();
            $view->with('schoolProfile', $schoolProfile);
        });

        /**
         * if(config('app.env') === 'production') {
         * URL::forceScheme('https'); }
        */      
        if (!file_exists(public_path('storage'))) {
            Artisan::call('storage:link');
        }
        if (!Storage::exists('public/previews')) {
            Storage::makeDirectory('public/previews');
        }
    }
}
