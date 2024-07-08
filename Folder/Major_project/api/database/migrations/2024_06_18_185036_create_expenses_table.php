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
        Schema::create('expenses', function (Blueprint $table) {
            $table->id('expense_id');
            $table->unsignedBigInteger('people_id');
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('organisation_id');
            $table->integer('amount');
            $table->unsignedBigInteger('expense_type_id');
            $table->date('expense_date');
            $table->enum('status', ['draft', 'approved', 'processed'])->default('draft');
            $table->boolean('is_deleted')->default(false);
            $table->timestamps();
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
