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
use LINE\Webhook\Model\ImageMessageContent;
use LINE\Parser\EventRequestParser;
use LINE\Webhook\Model\UserSource;
use LINE\Webhook\Model\GroupSource;
use LINE\Webhook\Model\RoomSource;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
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
        if ($signature !== $request->header('X-Line-Signature')) {
            Log::error('Signature verification failed');
            return response('Unauthorized', 401);
        }

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
                $source = $event->getSource();
                $userId = null;
                $displayName = null;

                if ($source instanceof UserSource) {
                    $userId = $source->getUserId();
                    try {
                        $profile = $messagingApi->getProfile($userId);
                        $displayName = $profile->getDisplayName();
                    } catch (ApiException $e) {
                        Log::error('Error retrieving profile from LINE API: ' . $e->getMessage());
                        $displayName = 'Unknown User';
                    }
                } elseif ($source instanceof GroupSource) {
                    $userId = $source->getGroupId();
                    $displayName = 'Group User';
                } elseif ($source instanceof RoomSource) {
                    $userId = $source->getRoomId();
                    $displayName = 'Room User';
                }

                if (!$userId) {
                    throw new Exception("Unable to get user/group/room ID from the event source.");
                }

                $user = User::firstOrCreate(
                    ['line_id' => $userId],
                    [
                        'provider' => 'line',
                        'name' => $displayName,
                        'email' => null,
                        'password' => null,
                    ]
                );

                $responseMessage = '';

                if ($eventMessage instanceof TextMessageContent) {
                    $eventMessageText = $eventMessage->getText();
                    $responseMessage = $this->processTextMessage($user, $eventMessageText);
                } elseif ($eventMessage instanceof ImageMessageContent) {
                    $responseMessage = $this->handleImageUpload($messagingApi, $event, $user);
                } else {
                    $responseMessage = "未対応のメッセージタイプです。";
                }

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

            return "画像が正常にアップロードされました。\n以下の形式で詳細情報を入力してください：\n日付,魚種,場所,価格,販売価格,販売数量,備考";
        } catch (\Exception $e) {
            Log::error('Error retrieving image content: ' . $e->getMessage());
            return "画像のアップロードに失敗しました。";
        }
    }

    private function processTextMessage(User $user, string $message)
    {
        $parts = explode(',', $message);
        if (count($parts) !== 7) {
            return "正しい形式で入力してください：\n日付,魚種,場所,価格,販売価格,販売数量,備考";
        }

        $date = trim($parts[0]);
        $fish = trim($parts[1]);
        $place = trim($parts[2]);
        $price = trim($parts[3]);
        $sellingPrice = trim($parts[4]);
        $quantitySold = trim($parts[5]);
        $remarks = trim($parts[6]);

        // 入力値のバリデーション
        $validator = \Illuminate\Support\Facades\Validator::make(
            [
                'date' => $date,
                'fish' => $fish,
                'place' => $place,
                'price' => $price,
                'selling_price' => $sellingPrice,
                'quantity_sold' => $quantitySold,
                'remarks' => $remarks,
            ],
            [
                'date' => 'required|date',
                'fish' => 'required|string',
                'place' => 'required|string',
                'price' => 'required|numeric|min:0',
                'selling_price' => 'nullable|numeric|min:0',
                'quantity_sold' => 'nullable|integer|min:0',
                'remarks' => 'nullable|string|max:200',
            ]
        );

        if ($validator->fails()) {
            return "入力内容に誤りがあります。もう一度確認してください。";
        }

        // 最後にアップロードされた画像を持つFishPriceレコードを取得
        $fishPrice = FishPrice::where('user_id', $user->id)
                              ->whereNotNull('image_path')
                              ->whereNull('fish')
                              ->orderBy('created_at', 'desc')
                              ->first();

        if (!$fishPrice) {
            return "先に画像をアップロードしてください。";
        }

        // FishPriceレコードを更新
        $fishPrice->update([
            'date' => $date,
            'fish' => $fish,
            'place' => $place,
            'price' => $price,
            'selling_price' => $sellingPrice ?: null,
            'quantity_sold' => $quantitySold ?: null,
            'remarks' => $remarks,
        ]);

        return "データが正常に保存されました。\n日付: {$date}\n魚種: {$fish}\n場所: {$place}\n価格: {$price}円\n販売価格: {$sellingPrice}円\n販売数量: {$quantitySold}\n備考: {$remarks}";
    }
}
