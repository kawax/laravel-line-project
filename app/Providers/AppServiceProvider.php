<?php

namespace App\Providers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\ServiceProvider;
use Revolution\Line\Facades\Bot;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Bot::macro('verifyWebhook', function () {
            return Http::line()->post('/v2/bot/channel/webhook/test', [
                'endpoint' => ''
            ])->json();
        });
    }
}
