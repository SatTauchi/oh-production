<?php

namespace App\Services;

use LINE\Clients\MessagingApi\Api\MessagingApiApi;
use LINE\Clients\MessagingApi\Configuration;
use LINE\Clients\MessagingApi\Model\TextMessage;
use LINE\Clients\MessagingApi\Model\PushMessageRequest;
use GuzzleHttp\Client;

class LineNotificationService
{
    private $messagingApi;

    public function __construct()
    {
        $client = new Client();
        $config = new Configuration();
        $config->setAccessToken(config('services.line.channel_token'));
        $this->messagingApi = new MessagingApiApi($client, $config);
    }

    public function sendMessage($userId, $message)
    {
        $textMessage = new TextMessage(['type' => 'text', 'text' => $message]);
        $request = new PushMessageRequest([
            'to' => $userId,
            'messages' => [$textMessage]
        ]);

        $this->messagingApi->pushMessage($request);
    }
}