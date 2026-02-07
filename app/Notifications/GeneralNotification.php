<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Notifications\Channels\FirebaseChannel;

class GeneralNotification extends Notification
{
    use Queueable;

    public $title;
    public $body;
    public $data;
    public $image;

    /**
     * Create a new notification instance.
     */
    public function __construct($title, $body, $data = [], $image = null)
    {
        $this->title = $title;
        $this->body = $body;
        $this->data = $data;
        $this->image = $image;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', FirebaseChannel::class];
    }

    /**
     * Get the Firebase representation of the notification.
     */
    public function toFirebase(object $notifiable): array
    {
        return [
            'title' => $this->title,
            'body' => $this->body,
            'image' => $this->image,
            'data' => $this->data,
        ];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => $this->title,
            'body' => $this->body,
            'image' => $this->image,
            'data' => $this->data,
        ];
    }
}
