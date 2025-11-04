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
        Schema::create('users', function (Blueprint $table) {
        $table->id('user_id');                                              // คอลัมน์ที่ 1
        $table->string('first_name');                                       // คอลัมน์ที่ 2
        $table->string('last_name');                                        // คอลัมน์ที่ 3
        $table->string('email')->unique()->nullable();                      // คอลัมน์ที่ 4
        $table->timestamp('email_verified_at')->nullable();                 // คอลัมน์ที่ 5
        $table->string('password');                                         // คอลัมน์ที่ 6
        $table->rememberToken();                                                    // คอลัมน์ที่ 7
        $table->string('username', 30)->unique();                   // คอลัมน์ที่ 8
        $table->string('phone_number', 10)->unique();               // คอลัมน์ที่ 9
        $table->date('date_of_birth');                                      // คอลัมน์ที่ 10
        $table->integer('loyalty_points')->default(100);             // คอลัมน์ที่ 11

        $table->timestamps();                                                       // คอลัมน์ที่ 12 (created_at) & 13 (updated_at)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
