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
        Schema::create('choices', function (Blueprint $table) {
            $table->id();

            $table->foreignId('step_id')
                ->constrained()
                ->cascadeOnDelete();

            // eg. ("Yes", "No")
            $table->string('label');
            // eg. ("yes", "no", "45", "2023-05-15")
            $table->string('value');

            // If a specific choice skips to a specific step, this field is used
            // to determine the next step.
            // If null, the next step is determined by the order of the steps.
            $table->integer('optional_next_step')->nullable();

            $table->integer('order')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('choices');
    }
};
