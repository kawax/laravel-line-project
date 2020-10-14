<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Revolution\Line\Notifications\LineNotifyChannel;
use Revolution\Line\Notifications\LineNotifyMessage;

class LineNotifyTest extends Notification
{
    use Queueable;

    protected $message;

    /**
     * Create a new notification instance.
     *
     * @param  string  $message
     *
     * @return void
     */
    public function __construct($message)
    {
        $this->message = $message;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return [
            LineNotifyChannel::class
        ];
    }

    public function toLineNotify($notifiable)
    {
        return LineNotifyMessage::create($this->message);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
