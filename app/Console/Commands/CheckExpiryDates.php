<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\FishPrice;
use App\Services\LineNotificationService;
use Carbon\Carbon;

class CheckExpiryDates extends Command
{
    protected $signature = 'check:expiry-dates';
    protected $description = 'Check for expired items and send LINE notifications';

    public function handle(LineNotificationService $lineService)
    {
        $today = Carbon::today();
        $expiredItems = FishPrice::where('expiry_date', '<=', $today)
                                 ->where('expiry_confirmed', false)
                                 ->with('user')
                                 ->get();

        foreach ($expiredItems as $item) {
            $message = "消費期限切れの未確認商品があります：\n"
                     . "魚種: {$item->fish}\n"
                     . "消費期限: {$item->expiry_date->format('Y-m-d')}\n"
                     . "数量: {$item->quantity_sold}kg";

            try {
                $lineService->sendMessage($item->user->line_id, $message);
                $this->info("Notification sent for item ID: {$item->id}");
            } catch (\Exception $e) {
                $this->error("Failed to send notification for item ID: {$item->id}. Error: {$e->getMessage()}");
            }
        }

        $this->info('Expiry date check completed.');
    }
}