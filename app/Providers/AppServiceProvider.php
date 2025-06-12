<?php

namespace App\Providers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\ServiceProvider;
use Revolution\Line\Contracts\WebhookHandler;
use Revolution\Line\Facades\Bot;
use Revolution\Line\Messaging\Http\Actions\WebhookLogHandler;

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
        // $this->app->singleton(WebhookHandler::class, WebhookLogHandler::class);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Bot::macro('verifyWebhook', function (): array {
            return Http::line()->post('/v2/bot/channel/webhook/test', [
                'endpoint' => '',
            ])->json();
        });

        Bot::macro('friendshipStatus', function (string $access_token): array {
            return Http::line()
                ->withToken($access_token)
                ->get('/friendship/v1/status')
                ->json();
        });
    }
}
