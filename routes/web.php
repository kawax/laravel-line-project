<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\NotifySocialiteController;
use App\Http\Controllers\LoginSocialiteController;
use App\Notifications\LineNotifyTest;
use Illuminate\Support\Facades\Route;
use Revolution\Line\Facades\Bot;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('login', [LoginSocialiteController::class, 'login'])->name('login');
Route::get('callback', [LoginSocialiteController::class, 'callback']);
Route::post('logout', [LoginSocialiteController::class, 'logout'])->name('logout');


Route::get('/home', HomeController::class)->name('home');

Route::get('info', function () {
    //dump(Bot::info());
    dump(Bot::verifyWebhook());
});


Route::middleware('auth')->group(function () {
    Route::get('notify/login', [NotifySocialiteController::class, 'login']);
    Route::get('notify/callback', [NotifySocialiteController::class, 'callback']);

    Route::get('notify', function () {
        auth()->user()->notify(new LineNotifyTest('test'));
    });
});
