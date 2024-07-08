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
        Schema::create('file_audit', function (Blueprint $table) {
            $table->id();
            $table->string('file_name');
            $table->string('reference_name');
            $table->string('file_mime_type');
            $table->unsignedBigInteger('timesheet_id');
            $table->integer('no_of_records');
            $table->decimal('file_size',10,2);
            $table->boolean('is_deleted')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('file_audit');
    }
};
