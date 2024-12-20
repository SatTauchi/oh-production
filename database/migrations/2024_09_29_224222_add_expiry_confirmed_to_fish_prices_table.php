<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('fish_prices', function (Blueprint $table) {
            $table->boolean('expiry_confirmed')->default(false);
        });
    }

    public function down()
    {
        Schema::table('fish_prices', function (Blueprint $table) {
            $table->dropColumn('expiry_confirmed');
        });
    }
};