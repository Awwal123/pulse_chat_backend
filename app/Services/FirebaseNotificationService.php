<?php

namespace App\Services;

use Kreait\Firebase\Contract\Messaging;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;

class FirebaseNotificationService
{
    private Messaging $messaging;

    public function __construct()
    {
        $this->messaging = (new Factory)
            ->withServiceAccount(
                config('services.firebase.credentials')
            )
            ->createMessaging();
    }

    public function sendToToken(
        string $token,
        string $title,
        string $body,
        array $data = []
    ): void {
        $message = CloudMessage::new()
            ->withToken($token)
            ->withNotification(
                Notification::create($title, $body)
            )
            ->withData($data);

        $this->messaging->send($message);
    }
}