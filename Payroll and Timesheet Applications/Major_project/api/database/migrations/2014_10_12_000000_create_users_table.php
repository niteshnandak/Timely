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
            $table->id('user_id');
            $table->string('firstname');
            $table->string('surname');
            $table->string('username')->unique();
            $table->string('email')->unique();
            $table->string('role');
            $table->boolean('active_status')->default(false);
            $table->date('last_active');
            $table->timestamp('token');
            $table->string('password');
            $table->integer('organisation_id');
            $table->rememberToken();
            $table->timestamps();
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
