<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveNotifiedFromFishPricesTable extends Migration
{
    public function up()
    {
        Schema::table('fish_prices', function (Blueprint $table) {
            $table->dropColumn('notified');
        });
    }

    public function down()
    {
        Schema::table('fish_prices', function (Blueprint $table) {
            $table->boolean('notified')->default(false);
        });
    }
}
