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
        Schema::create('survey_runs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('survey_id')
                ->constrained()
                ->cascadeOnDelete();

            // Patient ID hash due to privacy reasons
            $table->string('patient_hash', 64)->index();

            $table->enum('status', ['pending', 'completed', 'aborted'])->default('pending');
            $table->timestamp('started_at')->useCurrent();
            $table->timestamp('finished_at')->nullable();

            // ID of the current step (e.g. "step_1")
            // This is used to track the progress of the survey
            $table->string('current_step_id')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('survey_runs');
    }
};
