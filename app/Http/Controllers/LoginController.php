<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;

class LoginController extends Controller
{
    public function login()
    {
        return Socialite::driver('line-login')->with([
            'prompt' => 'consent',
            'bot_prompt' => 'normal',
        ])->redirect();
    }

    public function callback(Request $request)
    {
        if ($request->missing('code')) {
            Log::info('Login callback called without code parameter', [
                'request_data' => $request->all(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return redirect()->route('welcome')->with('error', 'Authorization failed. Please try logging in again.');
        }

        /**
         * @var \Laravel\Socialite\Two\User
         */
        $user = Socialite::driver('line-login')->user();

        $loginUser = User::updateOrCreate([
            'line_id' => $user->id,
        ], [
            'name' => 'User', // $user->nickname,
            'avatar' => $user->avatar,
            'access_token' => $user->token,
            'refresh_token' => $user->refreshToken,
        ]);

        auth()->login($loginUser, true);

        return to_route('dashboard');
    }

    public function logout()
    {
        auth()->logout();

        return redirect('/');
    }
}
