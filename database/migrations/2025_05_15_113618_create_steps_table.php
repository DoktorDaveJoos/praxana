<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('steps', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('survey_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->integer('order');

            // Title of the step (e.g. "Allgemeine Fragen")
            $table->string('title')->nullable();
            // Content of the step (e.g. "Wie alt sind Sie?")
            $table->text('content')->nullable();
            // Type of the step (e.g. "question", "info", "input", "scale")
            $table->string('step_type');

            // Only present if step_type is "question"
            // Type of the question (e.g. "single_choice", "multiple_choice", "text", "number", "date")
            $table->string('question_type')->nullable();

            // Only present if step_type is "question"
            // e.g. { min: 0, max: 100, optional: true } -> for question_type "scale" or "number"
            $table->json('options')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('steps');
    }
};
