<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifySellingPriceInFishPricesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('fish_prices', function (Blueprint $table) {
            if (Schema::hasColumn('fish_prices', 'selling_price')) {
                // selling_priceカラムが存在する場合、NULL可能に変更し、位置を移動
                $table->integer('selling_price')->nullable()->change();
                $table->integer('selling_price')->after('price')->change();
            } else {
                // selling_priceカラムが存在しない場合、新規作成
                $table->integer('selling_price')->nullable()->after('price');
            }
        });
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
                // カラムの位置を元に戻す（この操作は実際には効果がない可能性があります）
                $table->integer('selling_price')->change();
            }
        });
    }
}