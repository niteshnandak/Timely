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
        Schema::create('invoice_detail', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('timesheet_id');
            $table->unsignedBigInteger('invoice_id');
            // $table->foreign('timesheet_id')->references('id')->on('timesheet')->onDelete('cascade');
            $table->string('worker_id', 7);
            $table->string('worker_name');
            $table->date('invoice_date');
            $table->decimal('total_amount', 10, 2);
            $table->string('organisation');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_detail');
    }
};
