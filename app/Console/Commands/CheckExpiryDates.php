<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\FishPrice;
use App\Models\User;
use App\Services\LineNotificationService;
use Carbon\Carbon;

class CheckExpiryDates extends Command
{
    protected $signature = 'check:expiry-dates';
    protected $description = 'Check for expired items and send LINE notifications for each user';

    public function handle(LineNotificationService $lineService)
    {
        $today = Carbon::today();
        
        // ユーザーごとに処理を行う
        User::whereNotNull('line_id')->chunk(100, function ($users) use ($today, $lineService) {
            foreach ($users as $user) {
                $expiredItems = FishPrice::where('user_id', $user->id)
                                         ->where('expiry_date', '<=', $today)
                                         ->where('expiry_confirmed', false)
                                         ->get();

                if ($expiredItems->isNotEmpty()) {
                    $message = "消費期限切れの未確認商品があります：\n\n";
                    foreach ($expiredItems as $item) {
                        $message .= "魚種: {$item->fish}\n"
                                 . "消費期限: {$item->expiry_date->format('Y-m-d')}\n"
                                 . "数量: {$item->quantity_sold}kg\n\n";
                    }

                    try {
                        $lineService->sendMessage($user->line_id, $message);
                        $this->info("Notification sent to user ID: {$user->id}");
                    } catch (\Exception $e) {
                        $this->error("Failed to send notification to user ID: {$user->id}. Error: {$e->getMessage()}");
                    }
                }
            }
        });

        $this->info('Expiry date check completed.');
    }
}