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
        Schema::create('payment', function (Blueprint $table) {
        $table->id('pay_id')->startingValue(60001);
        $table->unsignedBigInteger('order_id');
        $table->unsignedBigInteger('reward_id')->nullable();
        $table->decimal('total_amount', 10, 2)->default(0.00);
        $table->decimal('discount_amount', 10, 2)->default(0.00)->nullable();
        $table->decimal('final_amount', 10, 2)->default(0.00);
        $table->enum('pay_method', ['QR', 'cash'])->nullable(); //วิธีจ่ายขึ้นทีหลัง เพราะทุกการกดจองก็เป็น 1 ออเดอร์แล้ว
        $table->enum('pay_status', ['pending', 'paid', 'failed'])->default('pending'); //สถานะเริ่มต้นเป็น pending ไว้ก่อน เพราะยังไม่รู้ว่าการชำระเงินจะล่ม/สำเร็จมั้ย

        $table->timestamps();

        $table->foreign('order_id')
              ->references('order_id')->on('orders')
              ->onDelete('cascade');
        $table->foreign('reward_id')
              ->references('reward_id')->on('reward')
              ->onDelete('set null');
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment');
    }
};
