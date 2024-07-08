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
        Schema::table('invoice_detail', function (Blueprint $table) {
            //
            $table->unsignedBigInteger('timesheet_detail_id')->nullable()->before('invoice_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoice_detail', function (Blueprint $table) {
            //
            $table->dropColumn('timesheet_detail_id');
        });
    }
};
