<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddSellingPriceAfterPriceToFishPricesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('fish_prices', function (Blueprint $table) {
            if (!Schema::hasColumn('fish_prices', 'selling_price')) {
                $table->integer('selling_price')->after('price')->nullable();
            } else {
                // カラムが既に存在する場合、必要な変更を加える
                $table->integer('selling_price')->nullable(false)->change();
            }
        });

        // 既存のレコードに対してデフォルト値を設定（必要な場合）
        if (Schema::hasColumn('fish_prices', 'selling_price') && Schema::hasColumn('fish_prices', 'price')) {
            DB::table('fish_prices')->whereNull('selling_price')->update(['selling_price' => DB::raw('price')]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('fish_prices', function (Blueprint $table) {
            if (Schema::hasColumn('fish_prices', 'selling_price')) {
                $table->dropColumn('selling_price');
            }
        });
    }
}