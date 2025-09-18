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
        Schema::create('responses', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('survey_run_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignUuid('step_id')
                ->constrained()
                ->cascadeOnDelete();

            // e.g. "text", "number", "date", "boolean"
            $table->string('type');

            // free text or number, array
            $table->json('value')->nullable();

            // determines if specific step is skipped
            $table->boolean('is_skipped')->default(false);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('responses');
    }
};
