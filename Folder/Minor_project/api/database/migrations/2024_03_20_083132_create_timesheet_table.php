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
        Schema::create('timesheet', function (Blueprint $table) {
            $table->id();
            $table->string('timesheet_name');
            $table->boolean('upload_type_csv')->default(false);
            $table->date('timesheet_date');
            $table->string('created_by');
            $table->boolean('is_deleted')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('timesheet');
    }
};
