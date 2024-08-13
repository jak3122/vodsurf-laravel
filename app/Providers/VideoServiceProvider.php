<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\VideoService;

class VideoServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(VideoService::class, function ($app) {
            return new VideoService();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
