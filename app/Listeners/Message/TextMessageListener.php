<?php

namespace App\Listeners\Message;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use LINE\LINEBot\Event\MessageEvent\TextMessage;
use Revolution\Line\Facades\Bot;
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
        $response = Bot::replyText($event->getReplyToken(), $event->getText());

        Notification::route('line-notify', config('line.notify.personal_access_token'))
            ->notify(new LineNotifyTest($event->getText()));

        if (! $response->isSucceeded()) {
            logger()->error(static::class.$response->getHTTPStatus(), $response->getJSONDecodedBody());
        }
    }
}
