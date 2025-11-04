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
        Schema::create('branches', function (Blueprint $table) {
            $table->id('branch_id')->startingValue(101);
            $table->string('branch_name', 100)->unique();
            $table->string('branch_info', 200)->nullable();
            $table->string('branch_phone', 15)->unique();
            $table->string('branch_address', 200);
            $table->string('image_path', 255);
            $table->time('time_open');
            $table->time('time_close');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('branches');
    }
};
