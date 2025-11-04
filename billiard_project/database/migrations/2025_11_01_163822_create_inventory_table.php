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
        Schema::create('inventory', function (Blueprint $table) {
        $table->foreignId('branch_id');
        $table->unsignedBigInteger('menu_id');
        $table->integer('stock_qty')->default(0); 

        $table->foreign('branch_id')
              ->references('branch_id')->on('branches')
              ->onDelete('cascade');
        $table->foreign('menu_id')
              ->references('menu_id')->on('menus')
              ->onDelete('cascade');
              
        $table->primary(['branch_id', 'menu_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory');
    }
};
