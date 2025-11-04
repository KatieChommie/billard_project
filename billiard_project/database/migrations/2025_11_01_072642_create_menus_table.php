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
        Schema::create('menus', function (Blueprint $table) {
            $table->unsignedBigInteger('menu_id');
            $table->foreignId('branch_id');
            $table->string('menu_name');
            $table->enum('menu_type', ['meal', 'snack', 'drink']);
            $table->decimal('price');
            $table->string('image_path', 255);
            $table->foreign('branch_id')
                  ->references('branch_id')->on('branches')
                  ->onDelete('restrict');
            $table->primary(['menu_id', 'branch_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menus');
    }
};
