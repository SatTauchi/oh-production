<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    DB::table('users')
        ->where('id', 4)
        ->update(['branch_id' => 1]);
}

public function down()
{
    DB::table('users')
        ->where('id', 4)
        ->update(['branch_id' => null]);
}
};

