<?php

namespace App\Listeners\Message;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use LINE\LINEBot\Event\MessageEvent\StickerMessage;
use Revolution\Line\Facades\Bot;

class StickerMessageListener
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
     * @param  StickerMessage  $event
     * @return void
     */
    public function handle(StickerMessage $event)
    {
        $token = $event->getReplyToken();
        $packageId = $event->getPackageId();
        $stickerId = $event->getStickerId();

        Bot::reply($token)->sticker($packageId, $stickerId);
        $response = Bot::reply($token)->text("packageId : $packageId / stickerId : $stickerId");

        if (!$response->isSucceeded()) {
            logger()->error(static::class.$response->getHTTPStatus(), $response->getJSONDecodedBody());
        }
    }
}
