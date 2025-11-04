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
        Schema::create('orders', function (Blueprint $table) {
        $table->id('order_id')->startingValue(20001);
        $table->foreignId('user_id');
        $table->dateTime('order_date');
        $table->enum('order_status', ['pending', 'confirmed', 'cancelled', 'completed']);

        $table->foreign('user_id')
              ->references('user_id')->on('users')
              ->onDelete('restrict');
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
