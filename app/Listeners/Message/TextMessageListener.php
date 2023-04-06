<?php

namespace App\Listeners\Message;

use App\Notifications\LineNotifyTest;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Notification;
use LINE\LINEBot\Event\MessageEvent\TextMessage;
use OpenAI\Laravel\Facades\OpenAI;
use Revolution\Line\Messaging\Bot;

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
    public function handle(TextMessage $event): void
    {
        $token = $event->getReplyToken();
        $text = $event->getText();

        $chat = OpenAI::chat()->create([
            'model' => 'gpt-3.5-turbo',
            'temperature' => 0.5,
            'messages' => [
                ['role' => 'user', 'content' => $text],
            ],
        ]);

        $content = Arr::get($chat, 'choices.0.message.content');

        $response = Bot::reply($token)
            ->withSender(config('app.name'))
            ->text($content);

//        Notification::route('line-notify', config('line.notify.personal_access_token'))
//            ->notify(new LineNotifyTest($text));

        if (! $response->isSucceeded()) {
            logger()->error(static::class.$response->getHTTPStatus(), $response->getJSONDecodedBody());
        }
    }
}
