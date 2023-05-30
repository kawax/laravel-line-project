<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use LINE\Clients\MessagingApi\Model\PushMessageRequest;
use LINE\Clients\MessagingApi\Model\TextMessage;
use LINE\Constants\MessageType;
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
        $message = new TextMessage(['text' => 'PushMessage test', 'type' => MessageType::TEXT]);

        $push = new PushMessageRequest([
            'to' => $request->user()->line_id,
            'messages' => [$message],
        ]);

        Bot::pushMessage($push);

        return back();
    }
}
