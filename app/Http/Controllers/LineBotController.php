<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use LINE\Clients\MessagingApi\Api\MessagingApiApi;
use LINE\Clients\MessagingApi\Model\TextMessage;
use LINE\Clients\MessagingApi\Model\ReplyMessageRequest;
use LINE\Webhook\Model\MessageEvent;
use LINE\Webhook\Model\TextMessageContent;
use LINE\Webhook\Model\ImageMessageContent;
use LINE\Parser\EventRequestParser;
use LINE\Webhook\Model\UserSource;
use App\Models\User;
use App\Models\FishPrice;
use App\Models\UserState;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class LineBotController extends Controller
{
    public function reply(Request $request)
    {
        $channelSecret = config('services.line.messenger_secret');
        $channelToken = config('services.line.channel_token');

        $httpRequestBody = $request->getContent();

        $hash = hash_hmac('sha256', $httpRequestBody, $channelSecret, true);
        $signature = base64_encode($hash);
        if ($signature !== $request->header('X-Line-Signature')) {
            Log::error('Signature verification failed');
            return response('Unauthorized', 401);
        }

        $client = new Client();
        $config = new \LINE\Clients\MessagingApi\Configuration();
        $config->setAccessToken($channelToken);

        $messagingApi = new MessagingApiApi(client: $client, config: $config);

        try {
            $parsedEvents = EventRequestParser::parseEventRequest($httpRequestBody, $channelSecret, $signature);

            foreach ($parsedEvents->getEvents() as $event) {
                if (!($event instanceof MessageEvent)) continue;

                $eventMessage = $event->getMessage();
                $source = $event->getSource();
                $userId = null;

                if ($source instanceof UserSource) {
                    $userId = $source->getUserId();
                }

                if (!$userId) {
                    throw new Exception("Unable to get user ID from the event source.");
                }

                $user = User::firstOrCreate(
                    ['line_id' => $userId],
                    ['provider' => 'line', 'name' => 'User', 'email' => null, 'password' => null]
                );

                // ユーザー状態を取得または作成
                $userState = UserState::firstOrCreate(['user_id' => $user->id], ['state' => 'awaiting_image']);

                // 状態に基づいて処理を分岐
                switch ($userState->state) {
                    case 'awaiting_image':
                        if ($eventMessage instanceof ImageMessageContent) {
                            $responseMessage = $this->handleImageUpload($messagingApi, $event, $user);
                            $userState->update(['state' => 'awaiting_date']);
                            return $this->sendReply($event, $responseMessage . "\n次に日付を入力します。");
                        } else {
                            return $this->sendReply($event, "画像をアップロードしましょう！");
                        }

                    case 'awaiting_date':
                        if ($eventMessage instanceof TextMessageContent && $this->isValidDate($eventMessage->getText())) {
                            $this->saveData($user, 'date', $eventMessage->getText());
                            $userState->update(['state' => 'awaiting_fish']);
                            return $this->sendReply($event, "日付が入力されました！\n次に魚の種類を入力します。");
                        } else {
                            return $this->sendReply($event, "正しい日付を入力してください（例：2024-09-25）。");
                        }

                    case 'awaiting_fish':
                        if ($eventMessage instanceof TextMessageContent) {
                            $this->saveData($user, 'fish', $eventMessage->getText());
                            $userState->update(['state' => 'awaiting_place']);
                            return $this->sendReply($event, "魚の種類が入力されました！\n次に産地を入力します。");
                        } else {
                            return $this->sendReply($event, "魚の種類を入力してください。");
                        }

                    case 'awaiting_place':
                        if ($eventMessage instanceof TextMessageContent) {
                            $this->saveData($user, 'place', $eventMessage->getText());
                            $userState->update(['state' => 'awaiting_price']);
                            return $this->sendReply($event, "産地が入力されました！\n次に仕入単価（円/kg）を入力します。");
                        } else {
                            return $this->sendReply($event, "産地を入力してください。");
                        }

                    case 'awaiting_price':
                        if ($eventMessage instanceof TextMessageContent && is_numeric($eventMessage->getText())) {
                            $this->saveData($user, 'price', $eventMessage->getText());
                            $userState->update(['state' => 'awaiting_selling_price']);
                            return $this->sendReply($event, "仕入単価（円/kg）が入力されました！\n次に販売単価（円/kg）を入力します。");
                        } else {
                            return $this->sendReply($event, "仕入単価（円/kg）を数値で入力してください。");
                        }

                    case 'awaiting_selling_price':
                        if ($eventMessage instanceof TextMessageContent && is_numeric($eventMessage->getText())) {
                            $this->saveData($user, 'selling_price', $eventMessage->getText());
                            $userState->update(['state' => 'awaiting_quantity_sold']);
                            return $this->sendReply($event, "販売単価（円/kg）が入力されました！\n次に数量（/kg）を入力します。");
                        } else {
                            return $this->sendReply($event, "販売単価（円/kg）を数値で入力してください。");
                        }

                    case 'awaiting_quantity_sold':
                        if ($eventMessage instanceof TextMessageContent && is_numeric($eventMessage->getText())) {
                            $this->saveData($user, 'quantity_sold', $eventMessage->getText());
                            $userState->update(['state' => 'awaiting_remarks']);
                            return $this->sendReply($event, "数量（/kg）が入力されました！\n次に消費期限を入力します。");
                        } else {
                            return $this->sendReply($event, "数量（/kg）を数値で入力してください。");
                        }

                    case 'awaiting_expiry_date':
                        if ($eventMessage instanceof TextMessageContent && $this->isValidDate($eventMessage->getText())) {
                            $this->saveData($user, 'expiry_date', $eventMessage->getText());
                            $userState->update(['state' => 'awaiting_remarks']);
                            return $this->sendReply($event, "消費期限が入力されました！\n次にメモを入力します。");
                        } else {
                            return $this->sendReply($event, "正しい消費期限を入力してください（例：2024-09-25）。");
                        }

                    case 'awaiting_remarks':
                        if ($eventMessage instanceof TextMessageContent) {
                            $this->saveData($user, 'remarks', $eventMessage->getText());
                            // 状態をリセットして、新しいデータ入力ができるようにする
                            $userState->update(['state' => 'awaiting_image']);
                            return $this->sendReply($event, "お疲れ様です。すべての入力が完了しました！\n新しいデータを入力するには、再度画像をアップロードしてください。");
                        } else {
                            return $this->sendReply($event, "メモを入力してください。");
                        }

                    case 'complete':
                        // 入力完了後、再度入力を可能にするために状態をリセット
                        $userState->update(['state' => 'awaiting_image']);
                        return $this->sendReply($event, "新しいデータを入力する場合、画像をアップロードしてください。");
                }
            }

            return response('OK', 200);

        } catch (Exception $e) {
            Log::error('Error: ' . $e->getMessage());
            return response('Error', 500);
        }
    }

    private function sendReply($event, $message)
    {
        $replyToken = $event->getReplyToken();
        $channelToken = config('services.line.channel_token');  // 環境変数からトークンを取得

        $replyMessage = new TextMessage([
            'type' => 'text',
            'text' => $message,
        ]);

        $request = new ReplyMessageRequest([
            'replyToken' => $replyToken,
            'messages' => [$replyMessage],
        ]);

        // Guzzle HTTP クライアントを使ってリクエストを送信
        $httpClient = new \GuzzleHttp\Client();
        $response = $httpClient->post('https://api.line.me/v2/bot/message/reply', [
            'headers' => [
                'Authorization' => 'Bearer ' . $channelToken,  // Authorization ヘッダーを設定
            ],
            'json' => $request,  // リクエストデータを JSON 形式で送信
        ]);

        if ($response->getStatusCode() !== 200) {
            throw new Exception('Failed to send reply message. Status code: ' . $response->getStatusCode());
        }
    }

    private function saveData($user, $field, $value)
    {
        // 最新の FishPrice レコードを取得して更新
        $fishPrice = FishPrice::where('user_id', $user->id)
                              ->whereNotNull('image_path')
                              ->whereNull($field)
                              ->orderBy('created_at', 'desc')
                              ->first();

        if ($fishPrice) {
            $fishPrice->update([$field => $value]);
        }
    }

    private function isValidDate($text)
    {
        return (bool)strtotime($text); // 簡易的に日付フォーマットが正しいかを確認
    }

    private function handleImageUpload(MessagingApiApi $messagingApi, MessageEvent $event, User $user)
    {
        $messageId = $event->getMessage()->getId();
        $channelToken = config('services.line.channel_token'); // トークンを取得

        try {
            // Guzzleクライアントを使ってメッセージコンテンツを取得
            $client = new \GuzzleHttp\Client();
            $response = $client->get("https://api-data.line.me/v2/bot/message/$messageId/content", [
                'headers' => [
                    'Authorization' => "Bearer $channelToken",
                ],
            ]);

            $imageContent = $response->getBody()->getContents();

            Log::info('Image content retrieved successfully');
            
            // 画像を一時ファイルとして保存
            $tempPath = tempnam(sys_get_temp_dir(), 'LINE_');
            file_put_contents($tempPath, $imageContent);

            Log::info('Image content saved temporarily at: ' . $tempPath);
            
            // 画像ファイルの検証
            $tempFile = new \Illuminate\Http\UploadedFile(
                $tempPath,
                'line_image.jpg',
                'image/jpeg',
                null,
                true
            );

            // バリデーション
            $validator = \Illuminate\Support\Facades\Validator::make(
                ['image' => $tempFile],
                ['image' => 'required|image|max:2048']
            );

            if ($validator->fails()) {
                unlink($tempPath);
                return "画像のアップロードに失敗しました。サイズは2MB以下である必要があります。";
            }

            // 画像を保存
            $path = Storage::disk('public')->putFile('fish_images', $tempFile);

            Log::info('Image uploaded successfully at path: ' . $path);

            // 一時ファイルを削除
            unlink($tempPath);

            // FishPriceモデルに画像情報を保存
            $fishPrice = new FishPrice([
                'user_id' => $user->id,
                'image_path' => $path,
            ]);
            $fishPrice->save();

            return "画像が正常にアップロードされました。";
        } catch (\Exception $e) {
            Log::error('Error retrieving image content: ' . $e->getMessage());
            return "画像のアップロードに失敗しました。";
        }
    }
}
