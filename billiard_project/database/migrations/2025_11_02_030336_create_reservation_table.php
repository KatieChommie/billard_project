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
        Schema::create('reservation', function (Blueprint $table) {
        $table->id('reserve_id')->startingValue(70001);
        $table->unsignedBigInteger('order_id');
        $table->unsignedBigInteger('table_id');
        $table->dateTime('start_time');
        $table->dateTime('end_time');
        $table->enum('reserve_status', ['confirmed', 'cancelled', 'completed']);

        $table->timestamps();

        $table->foreign('order_id')
              ->references('order_id')->on('orders')
              ->onDelete('cascade');
        $table->foreign('table_id')
              ->references('table_id')->on('tables')
              ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservation');
    }
};
