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
        Schema::create('reward_transaction', function (Blueprint $table) {
        $table->id('transact_id')->startingValue(40001);
        $table->foreignId('user_id');
        $table->enum('transact_type', ['received', 'redeemed']);
        $table->integer('pts_change');
        $table->string('transact_descrpt', 200);
        $table->dateTime('transact_date');

        $table->timestamps();

        // Foreign Key
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
        Schema::dropIfExists('reward_transaction');
    }
};
