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
        Schema::create('purchase', function (Blueprint $table) {
        $table->id('purchase_id')->startingValue(80001);
        $table->unsignedBigInteger('order_id');
        $table->foreignId('branch_id');
        $table->unsignedBigInteger('menu_id');
        $table->integer('menu_qty');
        $table->decimal('total_price',10, 2);

        // Foreign Keys
        $table->foreign('order_id')
              ->references('order_id')->on('orders')
              ->onDelete('cascade');
        $table->foreign(['menu_id', 'branch_id']) 
              ->references(['menu_id', 'branch_id'])->on('menus')
              ->onDelete('restrict');

        $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase');
    }
};
