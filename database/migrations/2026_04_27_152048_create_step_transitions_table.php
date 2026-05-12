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
        Schema::create('step_transitions', function (Blueprint $table) {
            $table->string('id')->primary()
                ->comment('Primary key of transition edge (UUID from visual designer)');

            $table->foreignId('process_id')
                ->constrained()
                ->cascadeOnDelete()
                ->comment('Reference to workflow process');

            $table->string('from_step_id')
                ->comment('Source step from which transition starts');

            $table->string('to_step_id')
                ->comment('Destination step to which transition leads');

            $table->string('branch_type', 20)
                ->default('DEFAULT')
                ->comment('Type of branch: TRUE, FALSE, or DEFAULT');

            $table->boolean('is_default')
                ->default(false)
                ->comment('Fallback transition executed when no condition matches');

            $table->string('action_label')
                ->comment('Button label shown in UI for triggering this transition');

            $table->boolean('is_active')
                ->default(true)
                ->comment('Controls whether transition is currently usable');

            // $table->softDeletes();
            $table->timestamps();

            $table->foreign('from_step_id')->references('id')->on('steps')->onDelete('cascade');
            $table->foreign('to_step_id')->references('id')->on('steps')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('step_transitions');
    }
};
