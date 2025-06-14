<?php

namespace App\Listeners\Line;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use LINE\Clients\MessagingApi\ApiException;
use LINE\Webhook\Model\MessageEvent;
use LINE\Webhook\Model\StickerMessageContent;
use LINE\Webhook\Model\TextMessageContent;
use Revolution\Line\Facades\Bot;

class MessageListener
{
    protected string $token;

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
     *
     * @throws ApiException
     */
    public function handle(MessageEvent $event): void
    {
        $message = $event->getMessage();
        $this->token = $event->getReplyToken();

        match ($message::class) {
            TextMessageContent::class => $this->text($message),
            StickerMessageContent::class => $this->sticker($message),
        };
    }

    /**
     * @throws ApiException
     */
    protected function text(TextMessageContent $message): void
    {
        Bot::reply($this->token)->text($message->getText());
    }

    /**
     * @throws ApiException
     */
    protected function sticker(StickerMessageContent $message): void
    {
        Bot::reply($this->token)->sticker(
            $message->getPackageId(),
            $message->getStickerId()
        );
    }
}
