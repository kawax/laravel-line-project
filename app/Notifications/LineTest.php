<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Revolution\Line\Notifications\LineChannel;
use Revolution\Line\Notifications\LineMessage;

class LineTest extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
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
            LineChannel::class,
        ];
    }

    public function toLine(object $notifiable): LineMessage
    {
        return LineMessage::create()
            ->text($this->message)
            ->sticker(446, random_int(1988, 2027));
    }

    /**
     * Get the message content (for testing purposes).
     */
    public function getMessage(): string
    {
        return $this->message;
    }
}
