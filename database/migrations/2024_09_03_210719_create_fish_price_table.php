<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('fish_price', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');     //ここを追加
            $table->date('date');
            $table->string('fish');
            $table->string('place')->nullable();
            $table->integer('price');
            $table->string('remarks')->nullable();
            $table->string('image')->nullable();  
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fish_price');
    }
};
