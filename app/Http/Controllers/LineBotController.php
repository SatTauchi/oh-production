<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use LINE\Clients\MessagingApi\Configuration;
use LINE\Clients\MessagingApi\Api\MessagingApiApi;
use LINE\Clients\MessagingApi\Model\TextMessage;
use LINE\Clients\MessagingApi\Model\ReplyMessageRequest;
use LINE\Clients\MessagingApi\ApiException;
use LINE\Webhook\Model\MessageEvent;
use LINE\Webhook\Model\TextMessageContent;
use LINE\Parser\EventRequestParser;
use LINE\Webhook\Model\UserSource;
use LINE\Webhook\Model\GroupSource;
use LINE\Webhook\Model\RoomSource;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\FishPrice;
use Exception;
use Carbon\Carbon;

class LineBotController extends Controller
{
    public function reply(Request $request)
    {
        $channelSecret = config('services.line.messenger_secret');
        $channelToken = config('services.line.channel_token');

        $httpRequestBody = $request->getContent();

        $hash = hash_hmac('sha256', $httpRequestBody, $channelSecret, true);
        $signature = base64_encode($hash);
        if ($signature !== $request->header('X-Line-Signature')) return;

        $client = new Client();
        $config = new Configuration();
        $config->setAccessToken($channelToken);

        $messagingApi = new MessagingApiApi(
            client: $client,
            config: $config,
        );

        try {
            $parsedEvents = EventRequestParser::parseEventRequest($httpRequestBody, $channelSecret, $signature);

            foreach ($parsedEvents->getEvents() as $event) {
                if (!($event instanceof MessageEvent)) continue;

                $eventMessage = $event->getMessage();
                if (!($eventMessage instanceof TextMessageContent)) continue;

                $eventMessageText = $eventMessage->getText();
                
                $source = $event->getSource();
                $userId = null;

                if ($source instanceof UserSource) {
                    $userId = $source->getUserId();
                } elseif ($source instanceof GroupSource) {
                    $userId = $source->getGroupId();
                } elseif ($source instanceof RoomSource) {
                    $userId = $source->getRoomId();
                }

                if (!$userId) {
                    throw new Exception("Unable to get user/group/room ID from the event source.");
                }

                // ユーザー情報を取得
                $user = User::where('line_id', $userId)->first();
                if (!$user) {
                    throw new Exception("User not found for LINE ID: $userId");
                }

                // メッセージを処理してデータベースに保存
                $responseMessage = $this->processMessageAndSaveToDatabase($user, $eventMessageText);

                $message = new TextMessage([
                    'type' => 'text',
                    'text' => $responseMessage,
                ]);

                $request = new ReplyMessageRequest([
                    'replyToken' => $event->getReplyToken(),
                    'messages' => [$message],
                ]);

                $response = $messagingApi->replyMessageWithHttpInfo($request);

                $responseBody = $response[0];
                $responseStatusCode = $response[1];
                if ($responseStatusCode != 200) {
                    throw new Exception($responseBody);
                }
            }

            return response('OK', 200);

        } catch (ApiException $e) {
            Log::error('LINE API Error: ' . $e->getCode() . ':' . $e->getResponseBody());
            return response('Error', 500);
        } catch (Exception $e) {
            Log::error('Error: ' . $e->getMessage());
            return response('Error', 500);
        }
    }

    private function processMessageAndSaveToDatabase(User $user, string $message)
    {
        $parts = explode(',', $message);
        
        // 入力形式: 日付,魚種,場所,価格,販売価格,販売数量,備考
        if (count($parts) < 7) {
            return "正しい形式で入力してください: 日付,魚種,場所,価格,販売価格,販売数量,備考";
        }

        try {
            $fishPrice = new FishPrice([
                'user_id' => $user->id,
                'date' => trim($parts[0]),
                'fish' => trim($parts[1]),
                'place' => trim($parts[2]),
                'price' => trim($parts[3]),
                'selling_price' => trim($parts[4]),
                'quantity_sold' => trim($parts[5]),
                'remarks' => trim($parts[6]),
            ]);
            $fishPrice->save();

            return "データを保存しました:\n" . 
                   "日付: {$fishPrice->date}\n" .
                   "魚種: {$fishPrice->fish}\n" .
                   "場所: {$fishPrice->place}\n" .
                   "価格: {$fishPrice->formatted_price}\n" .
                   "販売価格: {$fishPrice->selling_price}\n" .
                   "販売数量: {$fishPrice->quantity_sold}\n" .
                   "備考: {$fishPrice->remarks}";
        } catch (Exception $e) {
            Log::error('データ保存エラー: ' . $e->getMessage());
            return "データの保存に失敗しました。正しい形式で入力されているか確認してください。";
        }
    }
}