<?php

namespace App\Listeners\Message;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use LINE\LINEBot\Event\MessageEvent\TextMessage;
use Revolution\Line\Messaging\Bot;
use Illuminate\Support\Facades\Notification;
use App\Notifications\LineNotifyTest;

class TextMessageListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  TextMessage  $event
     * @return void
     */
    public function handle(TextMessage $event)
    {
        $token = $event->getReplyToken();
        $text = $event->getText();

        $response = Bot::reply($token)
            ->withSender(config('app.name'))
            ->text(class_basename(static::class), $text);

        Notification::route('line-notify', config('line.notify.personal_access_token'))
            ->notify(new LineNotifyTest($text));

        if (!$response->isSucceeded()) {
            logger()->error(static::class.$response->getHTTPStatus(), $response->getJSONDecodedBody());
        }
    }
}
