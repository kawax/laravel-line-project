<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\SocialiteController;
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

Route::get('login', [SocialiteController::class, 'login'])->name('login');
Route::get('callback', [SocialiteController::class, 'callback']);
Route::post('logout', [SocialiteController::class, 'logout'])->name('logout');

Route::get('/home', HomeController::class)->name('home');

Route::get('info', function () {
    //dump(Bot::info());
    dump(Bot::verifyWebhook());
});
