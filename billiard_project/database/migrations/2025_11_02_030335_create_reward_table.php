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
        Schema::create('reward', function (Blueprint $table) {
        $table->id('reward_id')->startingValue(30001);
        $table->foreignId('user_id');
        $table->string('reward_descrpt', 50);
        $table->enum('reward_type', ['birthday', 'points']);
        $table->integer('reward_value');
        $table->enum('reward_discount', ['baht', 'percent']);
        $table->enum('reward_status', ['active', 'used', 'expired']);
        $table->date('issued_date');
        $table->date('expired_date');

        $table->timestamps();

        $table->foreign('user_id')
              ->references('user_id')->on('users')
              ->onDelete('cascade');
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reward');
    }
};
