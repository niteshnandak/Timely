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
        Schema::create('file_data', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('file_id');
            $table->string('worker_id', 7);
            $table->string('worker_name');
            $table->date('timesheet_detail_date');
            $table->string('organisation');
            $table->decimal('hourly_pay', 7, 2);
            $table->integer('hours_worked');
            $table->boolean('is_deleted')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('file_data');
    }
};
