<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\ProfilSekolah;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log; // Tambahkan import untuk Log
use App\Services\RaporTemplateProcessor;

// Tambahkan import untuk RaporTemplateProcessor jika dibutuhkan
// use App\Services\RaporTemplateProcessor;

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
        try {
            // Create directories if they don't exist
            if (!file_exists(public_path('storage'))) {
                app('files')->makeDirectory(public_path('storage'), 0755, true);
            }
            if (!file_exists(storage_path('app/public'))) {
                app('files')->makeDirectory(storage_path('app/public'), 0755, true);
            }
            
            // Hanya jalankan storage:link jika belum ada
            if (!file_exists(public_path('storage'))) {
                Artisan::call('storage:link');
            }
            
            // Buat direktori previews jika belum ada
            if (!Storage::exists('public/previews')) {
                Storage::makeDirectory('public/previews');
            }
        } catch (\Exception $e) {
            // Log the error but don't crash the application
            Log::warning('Unable to create storage symlink: ' . $e->getMessage());
        }
        
        View::composer('*', function ($view) {
            $schoolProfile = ProfilSekolah::first();
            $view->with('schoolProfile', $schoolProfile);
        });

        /**
         * if(config('app.env') === 'production') {
         * URL::forceScheme('https'); }
        */      
        // Kode duplikat telah dihapus dari sini
    }
}

        /*
        View::composer('*', function ($view) {
            $schoolProfile = ProfilSekolah::first();
            $view->with('schoolProfile', $schoolProfile);
        });

        /**
         * if(config('app.env') === 'production') {
         * URL::forceScheme('https'); }
            
        if (!file_exists(public_path('storage'))) {
            Artisan::call('storage:link');
        }
        if (!Storage::exists('public/previews')) {
            Storage::makeDirectory('public/previews');
        }
    }
}
*/