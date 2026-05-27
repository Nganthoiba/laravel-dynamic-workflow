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
        Schema::create('steps', function (Blueprint $table) {
            $table->string('id')->primary()
                ->comment('Primary key of workflow step node (UUID from visual designer)');

            $table->foreignId('process_id')
                ->constrained()
                ->cascadeOnDelete()
                ->comment('Reference to parent workflow process');

            $table->string('node_type', 50)
                ->default('step')
                ->comment('Node type: step, condition, start, end');

            $table->string('name')
                ->comment('Human-readable step name shown in UI');

            $table->string('code')
                ->comment('Machine-readable step identifier used in workflow engine');

            $table->unique(['process_id', 'code', 'deleted_at']);

            $table->text('description')
                ->nullable()
                ->comment('Optional description of step');

            $table->json('condition_json')->nullable()
                ->comment('JSON conditions attached if this is a condition node');

            $table->json('ui_json')->nullable()
                ->comment('Stores visual layout metadata like x, y coordinates');

            $table->string('workflow_action')->nullable()
                ->comment('Blade template used to render step interface');

            $table->boolean('is_start')
                ->default(false)
                ->comment('Indicates whether this step is entry point of workflow');

            $table->boolean('is_end')
                ->default(false)
                ->comment('Indicates whether this step terminates workflow');

            $table->boolean('is_active')
                ->default(true)
                ->comment('Controls whether this step is currently usable');

            $table->softDeletes();
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
