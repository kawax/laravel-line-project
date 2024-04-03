<?php

use App\Http\Controllers\LoginController;
use App\Http\Controllers\NotifyController;
use App\Http\Controllers\PushController;
use Illuminate\Support\Facades\Route;
use Revolution\Line\Facades\Bot;

Route::get('/', function () {
    return view('welcome');
});

Route::get('login', [LoginController::class, 'login'])->name('login');
Route::get('callback', [LoginController::class, 'callback']);
Route::post('logout', [LoginController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('notify/login', [NotifyController::class, 'login'])
        ->name('notify.login');

    Route::get('notify/callback', [NotifyController::class, 'callback']);

    Route::get('notify', [NotifyController::class, 'send'])
        ->name('notify.send');

    Route::get('push', PushController::class)
        ->name('push');

    Route::get('info', function () {
        dump(Bot::getBotInfo());
        dump(Bot::verifyWebhook());

        dump(Bot::friendshipStatus(auth()->user()->access_token));
    });
});

Route::view('/dashboard', 'dashboard')
    ->middleware(['auth'])
    ->name('dashboard');
