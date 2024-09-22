<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateUsersTableForLineLogin extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            // email カラムを nullable に変更
            $table->string('email')->nullable()->change();

            // password カラムを nullable に変更
            $table->string('password')->nullable()->change();

            // 新しいカラムを追加
            $table->string('provider')->nullable();
            $table->string('line_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            // email と password カラムを元に戻す
            $table->string('email')->nullable(false)->change();
            $table->string('password')->nullable(false)->change();

            // 追加したカラムを削除
            $table->dropColumn('provider');
            $table->dropColumn('line_id');
        });
    }
}