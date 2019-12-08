<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Hashids\Hashids;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(Hashids::class, function () {
            return new Hashids(config('hashids.connections.main.salt'), config('hashids.connections.main.length'));
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
