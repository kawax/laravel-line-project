<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use LINE\LINEBot\MessageBuilder\TextMessageBuilder;
use Revolution\Line\Facades\Bot;

class PushController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function __invoke(Request $request)
    {
        $message = new TextMessageBuilder('PushMessage test');

        $response = Bot::pushMessage($request->user()->line_id, $message);

        if (!$response->isSucceeded()) {
            logger()->error(static::class.$response->getHTTPStatus(), $response->getJSONDecodedBody());
        }

        return back();
    }
}
