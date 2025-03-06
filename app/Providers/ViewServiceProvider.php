<?php

namespace App\Providers;

use Illuminate\View\ViewServiceProvider as ServiceProvider;

class ViewServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        parent::register();
        
        $this->app->singleton('view.finder', function ($app) {
            $paths = $app['config']['view.paths'];
            
            // Add an absolute path to ensure views are found
            $paths[] = base_path('resources/views');
            
            return new \Illuminate\View\FileViewFinder($app->make('files'), $paths);
        });
    }
}