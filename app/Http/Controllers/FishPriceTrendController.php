<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\FishPrice;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class FishPriceTrendController extends Controller
{
    public function getFishPriceTrend(Request $request)
    {
        $startDate = $request->input('start') ? Carbon::parse($request->input('start')) : Carbon::now()->subDays(30);
        $endDate = $request->input('end') ? Carbon::parse($request->input('end')) : Carbon::now();
        $fishType = $request->input('fish');
        $userId = Auth::id();

        Log::info('getFishPriceTrend called', [
            'startDate' => $startDate->toDateString(),
            'endDate' => $endDate->toDateString(),
            'fishType' => $fishType,
            'userId' => $userId
        ]);

        $query = FishPrice::where('user_id', $userId)
                            ->whereBetween('date', [$startDate, $endDate])
                            ->where('delete_flg', 0);

        if ($fishType !== 'all') {
            $query->where('fish', $fishType);
        }

        $data = $query->orderBy('date')->get();

        Log::info('Query result', ['count' => $data->count()]);

        if ($data->isEmpty()) {
            Log::warning('No data found for the given criteria');
            return response()->json(['labels' => [], 'datasets' => []]);
        }

        $groupedData = $data->groupBy('fish');
        $labels = $data->pluck('date')->unique()->values()->all();

        $datasets = [];
        foreach ($groupedData as $fish => $prices) {
            $priceData = array_fill_keys($labels, null);
            foreach ($prices as $price) {
                $priceData[$price->date] = $price->price;
            }
            $datasets[$fish] = array_values($priceData);
        }

        Log::info('Response data', [
            'labels_count' => count($labels),
            'datasets_count' => count($datasets)
        ]);

        return response()->json([
            'labels' => $labels,
            'datasets' => $datasets
        ]);
    }

    public function getAveragePrices(Request $request)
    {
        $fishType = $request->query('fish');
        $userId = Auth::id();

        if (empty($fishType)) {
            return response()->json(['error' => '魚の種類が指定されていません。'], 400);
        }

        if (!$userId) {
            return response()->json(['error' => 'ユーザーがログインしていません。'], 401);
        }

        $averagePrices = FishPrice::select(
            'fish',
            DB::raw('DATE(date) as date'),
            DB::raw('AVG(price) as average_price'),
            DB::raw('AVG(selling_price) as average_selling_price'),
            DB::raw('SUM(quantity_sold) as quantity_sold')
        )
        ->where('fish', $fishType)
        ->where('user_id', $userId)
        ->where('delete_flg', 0)
        ->groupBy('fish', DB::raw('DATE(date)'))
        ->orderBy('date')
        ->get();

        $averagePrices = $averagePrices->map(function ($item) {
            $item->average_selling_price = $item->average_selling_price ?? null;
            return $item;
        });

        return response()->json($averagePrices);
    }

    public function getFishPurchaseTotal()
    {
        $userId = Auth::id();

        $purchaseTotal = FishPrice::select('fish', DB::raw('SUM(price * quantity_sold) as total'))
            ->where('user_id', $userId)
            ->where('delete_flg', 0)
            ->groupBy('fish')
            ->get();

        $labels = $purchaseTotal->pluck('fish')->toArray();
        $data = $purchaseTotal->pluck('total')->toArray();

        return response()->json([
            'labels' => $labels,
            'data' => $data
        ]);
    }


    private function getRandomColor()
    {
        return '#' . str_pad(dechex(mt_rand(0, 0xFFFFFF)), 6, '0', STR_PAD_LEFT);
    }
}