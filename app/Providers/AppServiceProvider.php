<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\ProfilSekolah;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Services\RaporTemplateProcessor;

// Add these imports for the audit system
use App\Observers\AuditObserver;
use App\Models\User;
use App\Models\Guru;
use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\Nilai;
use App\Models\Prestasi;
use App\Models\Absensi;
use App\Models\ReportTemplate;

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

        // Register the audit observers for various models
        $this->registerAuditObservers();

        /**
         * if(config('app.env') === 'production') {
         * URL::forceScheme('https'); }
        */      
    }

    /**
     * Register the audit observer with various models
     */
    protected function registerAuditObservers(): void
    {
        // Register observer for important models only to avoid excessive logging
        User::observe(AuditObserver::class);
        Guru::observe(AuditObserver::class);
        Siswa::observe(AuditObserver::class);
        Kelas::observe(AuditObserver::class);
        MataPelajaran::observe(AuditObserver::class);
        Nilai::observe(AuditObserver::class);
        Prestasi::observe(AuditObserver::class);
        Absensi::observe(AuditObserver::class);
        ReportTemplate::observe(AuditObserver::class);
        
        // 
    }
}