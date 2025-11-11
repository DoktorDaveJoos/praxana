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
        Schema::create('medical_histories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('patient_hash', 64)->index();
            $table->json('diagnoses')->nullable();
            $table->json('allergies')->nullable();
            $table->json('medications')->nullable();
            $table->json('risk_factors')->nullable();
            $table->text('past_operations')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medical_histories');
    }
};
