<?php

/**
 * Migration: Create Workflow Bindings Table
 *
 * Purpose:
 * Defines the database schema for the `workflow_bindings` table.
 * This table maps workflow processes to Eloquent models and events for automatic triggers.
 *
 * Usage:
 * Run via the Laravel migration command:
 * php artisan migrate
 */

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
        Schema::create('workflow_bindings', function (Blueprint $table) {
            $table->id()
                ->comment('Primary key of workflow binding definition');

            $table->foreignId('process_id')
                ->comment('The workflow process to start')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('model_type')
                ->comment('FQN of the Eloquent model bound to the workflow');

            $table->string('event_name')
                ->comment('Name of the model event that triggers this workflow (e.g. created, updated)');

            $table->string('trigger_type', 20)
                ->default('auto')
                ->comment('Trigger type: auto or manual');

            $table->integer('priority')
                ->default(0)
                ->comment('Priority of the trigger for conflict resolution');

            $table->boolean('is_active')
                ->default(true)
                ->comment('Flag indicating if the binding is active');

            $table->timestamps();

            // Index for fast lookup on events
            $table->index(['model_type', 'event_name', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workflow_bindings');
    }
};
