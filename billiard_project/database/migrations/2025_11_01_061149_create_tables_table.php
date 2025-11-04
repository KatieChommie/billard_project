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
        Schema::create('tables', function (Blueprint $table) {
            $table->unsignedBigInteger('table_id')->primary();
            $table->foreignId('branch_id');
            $table->integer('table_number');
            $table->enum('table_status', ['available', 'reserved', 'unavailable']);
            $table->foreign('branch_id')
                  ->references('branch_id')->on('branches')
                  ->onDelete('restrict');
            $table->unique(['branch_id', 'table_number']);
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tables');
    }
};
