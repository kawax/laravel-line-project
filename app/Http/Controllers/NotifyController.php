<?php

namespace App\Http\Controllers;

use App\Notifications\LineNotifyTest;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;

class NotifyController extends Controller
{
    public function login()
    {
        return Socialite::driver('line-notify')->redirect();
    }

    public function callback(Request $request)
    {
        if ($request->missing('code')) {
            dd($request);
        }

        /**
         * @var \Laravel\Socialite\Two\User
         */
        $user = Socialite::driver('line-notify')->user();

        $request->user()
            ->fill([
                'notify_token' => $user->token,
            ])->save();

        return to_route('dashboard');
    }

    /**
     * @param  Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function send(Request $request)
    {
        $request->user()->notify(new LineNotifyTest('OK'));

        return back();
    }
}
