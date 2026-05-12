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
        Schema::create('workflow_instances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('process_id')
                ->constrained()
                ->cascadeOnDelete();
            
            // Polymorphic reference to the subject model (e.g. PurchaseOrder, Application)
            $table->string('reference_type')
                ->comment('Class name of the subject model');
            $table->unsignedBigInteger('reference_id')
                ->comment('ID of the subject model');

            $table->string('current_step_id')
                ->comment('The next pending executable step in UUID');
            
            $table->string('status', 50)
                ->default('IN_PROGRESS');
            
            $table->json('context')
                ->nullable();
            
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();

            $table->timestamps();

            $table->foreign('current_step_id')->references('id')->on('steps')->onDelete('cascade');
            
            // Index for faster polymorphic lookups
            $table->index(['reference_type', 'reference_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workflow_instances');
    }
};
