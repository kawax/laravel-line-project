<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use LINE\LINEBot\MessageBuilder\TextMessageBuilder;
use Revolution\Line\Facades\Bot;

class PushController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return RedirectResponse
     */
    public function __invoke(Request $request): RedirectResponse
    {
        $message = new TextMessageBuilder('PushMessage test');

        $response = Bot::pushMessage($request->user()->line_id, $message);

        if (! $response->isSucceeded()) {
            logger()->error(static::class.$response->getHTTPStatus(), $response->getJSONDecodedBody());
        }

        return back();
    }
}
