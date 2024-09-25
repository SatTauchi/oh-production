<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AdjustSellingPricePositionInFishPricesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasColumn('fish_prices', 'selling_price') && Schema::hasColumn('fish_prices', 'price')) {
            $columns = Schema::getColumnListing('fish_prices');
            $priceIndex = array_search('price', $columns);
            $sellingPriceIndex = array_search('selling_price', $columns);

            if ($sellingPriceIndex !== $priceIndex + 1) {
                // selling_priceがpriceの直後にない場合
                DB::statement('ALTER TABLE fish_prices MODIFY COLUMN selling_price INTEGER NULL AFTER price');
            }
        } elseif (!Schema::hasColumn('fish_prices', 'selling_price')) {
            // selling_price カラムが存在しない場合、新規作成
            Schema::table('fish_prices', function (Blueprint $table) {
                $table->integer('selling_price')->nullable()->after('price');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // この移行をロールバックする具体的な操作は定義していません
        // 必要に応じて、selling_priceカラムを元の位置に戻す処理を追加できます
    }
}