<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FishPrice extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'date',
        'fish',
        'place',
        'price',
        'selling_price',
        'quantity_sold',
        'expiry_date', // 追加
        'remarks',
        'image_path',
        'delete_flg',   
        'expiry_confirmed', // 追加
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'date' => 'date',
        'price' => 'decimal:2',
        'expiry_date' => 'date', // 追加
        'expiry_confirmed' => 'boolean', // 追加
    ];

    /**
     * 画像のフルパスを取得
     *
     * @return string|null
     */
    public function getImageUrlAttribute()
    {
        return $this->image_path
            ? asset('storage/' . $this->image_path)
            : null;
    }

    /**
     * 価格を日本円形式でフォーマット
     *
     * @return string
     */
    public function getFormattedPriceAttribute()
    {
        return '¥' . number_format($this->price, 2);
    }

    /**
     * 特定の魚種の平均価格を取得
     *
     * @param string $fishType
     * @return float|null
     */
    public static function getAveragePriceByFish($fishType)
    {
        return self::where('fish', $fishType)->avg('price');
    }

    /**
     * 指定された日付範囲内のデータを取得
     *
     * @param string $startDate
     * @param string $endDate
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getByDateRange($startDate, $endDate)
    {
        return self::whereBetween('date', [$startDate, $endDate])
                    ->orderBy('date', 'asc')
                    ->get();
    }

    // delete_flg が 0 のレコードのみを取得するスコープ
    public function scopeActive($query)
    {
        return $query->where('delete_flg', 0);
    }

    /**
     * 消費期限が切れている商品を取得
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getExpiredItems()
    {
        return self::where('expiry_date', '<', now())
                    ->where('delete_flg', 0)
                    ->where('expiry_confirmed', false)  // 確認済みでないものだけを取得
                    ->get();
    }
}