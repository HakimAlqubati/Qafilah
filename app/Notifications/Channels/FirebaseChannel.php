<?php

namespace App\Notifications\Channels;

use Illuminate\Notifications\Notification;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification as FirebaseNotification;
use Illuminate\Support\Facades\Log;

class FirebaseChannel
{
    protected $messaging;

    public function __construct()
    {
        $credentialsPath = base_path(env('FIREBASE_CREDENTIALS', 'storage/app/firebase/firebase_credentials.json'));

        if (file_exists($credentialsPath)) {
            $factory = (new Factory)->withServiceAccount($credentialsPath);
            $this->messaging = $factory->createMessaging();
        } else {
            Log::error('Firebase credentials file not found at: ' . $credentialsPath);
        }
    }

    /**
     * Send the given notification.
     *
     * @param  mixed  $notifiable
     * @param  \Illuminate\Notifications\Notification  $notification
     * @return void
     */
    public function send($notifiable, Notification $notification)
    {
        if (!$this->messaging) {
            return;
        }

        $token = $notifiable->fcm_token;

        if (!$token) {
            return;
        }

        // Get the message from the notification class
        $data = method_exists($notification, 'toFirebase')
                    ? $notification->toFirebase($notifiable)
                    : [];

        if (empty($data)) {
            return;
        }

        // Validate data structure
        $title = $data['title'] ?? 'New Notification';
        $body = $data['body'] ?? '';
        $imageUrl = $data['image'] ?? null;
        $customData = $data['data'] ?? [];

        try {
            $firebaseNotification = FirebaseNotification::create($title, $body, $imageUrl);

            $message = CloudMessage::withTarget('token', $token)
                ->withNotification($firebaseNotification)
                ->withData($customData);

            $this->messaging->send($message);

        } catch (\Throwable $e) {
            Log::error('Firebase notification API failed: ' . $e->getMessage());
        }
    }
}
