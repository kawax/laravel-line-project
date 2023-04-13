<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Revolution\Line\Notifications\LineNotifyChannel;
use Revolution\Line\Notifications\LineNotifyMessage;

class LineNotifyTest extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @param  string  $message
     * @return void
     */
    public function __construct(protected string $message)
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return [
            LineNotifyChannel::class,
        ];
    }

    public function toLineNotify(object $notifiable): LineNotifyMessage
    {
        return LineNotifyMessage::create($this->message)
            ->withSticker(1, random_int(1, 17));
    }
}
