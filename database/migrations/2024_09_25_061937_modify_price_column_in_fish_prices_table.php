<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyPriceColumnInFishPricesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('fish_prices', function (Blueprint $table) {
            // price カラムを int(11) に変更
            $table->integer('price')->change();
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
            // price カラムを元の decimal(10, 2) に戻す
            $table->decimal('price', 10, 2)->change();
        });
    }
}
