<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class StreamerServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton('streamers', function ($app) {
            return $app['config']->get('streamers');
        });
    }

    public function boot()
    {
        //
    }

    public function provides()
    {
        return ['streamers'];
    }
}
