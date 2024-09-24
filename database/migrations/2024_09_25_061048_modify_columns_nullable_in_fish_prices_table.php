<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyColumnsNullableInFishPricesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('fish_prices', function (Blueprint $table) {
            $table->date('date')->nullable()->change();
            $table->string('fish')->nullable()->change();
            $table->string('place')->nullable()->change();
            $table->decimal('price', 10, 2)->nullable()->change();
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
            $table->date('date')->nullable(false)->change();
            $table->string('fish')->nullable(false)->change();
            $table->string('place')->nullable(false)->change();
            $table->decimal('price', 10, 2)->nullable(false)->change();
        });
    }
}

