<?php

namespace Benwilkins\FCM;

use GuzzleHttp\Client;
use Illuminate\Notifications\Notification;

/**
 * Class FCMChannel
 * @package Benwilkins\FCM
 */
class FCMChannel
{
    /**
     * @const The API URL for Firebase
     */
    const API_URI = 'https://fcm.googleapis.com/fcm/send';

    /**
     * @var Client
     */
    private $client;

    /**
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * @param mixed $notifiable
     * @param Notification $notification
     */
    public function send($notifiable, Notification $notification)
    {
        /** @var FCMMessage $message */
        $message = $notification->toFCM($notifiable);

        if (is_null($message->getTo())) {
            if (!$to = $notifiable->routeNotificationFor('fcm')) {
                return;
            }

            $message->to($to);
        }

        $this->client->post(self::API_URI, [
            'headers' => [
                'Authorization' => 'key=' . $this->getApiKey(),
                'Content-Type'  => 'application/json',
            ],
            'body' => $message->formatData(),
        ]);

    }

    /**
     * @return string
     */
    private function getApiKey()
    {
        return config('services.fcm.key');
    }
}