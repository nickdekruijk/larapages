<?php

namespace NickDeKruijk\LaraPages;

use Illuminate\Support\ServiceProvider;

class LaraPagesServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadViewsFrom(__DIR__.'/views', 'laraPages');
        $this->publishes([
            __DIR__.'/config.php' => config_path('larapages.php'),
            __DIR__.'/js' => public_path('vendor/larapages/js'),
            __DIR__.'/css' => public_path('vendor/larapages/css'),
            __DIR__.'/../../../tinymce/tinymce/' => public_path('vendor/tinymce'),
        ], 'public');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        if (!$this->app->routesAreCached()) {
            require __DIR__.'/routes.php';
        }
       # $this->app->make('NickDeKruijk\LaraPages\LaraPagesController');
    }
}
