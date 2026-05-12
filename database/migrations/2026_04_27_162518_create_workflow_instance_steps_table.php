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
        Schema::create('workflow_instance_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workflow_instance_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('process_id')
                ->nullable()
                ->constrained()
                ->cascadeOnDelete();
            $table->string('step_id')
                ->comment('Step UUID');
            $table->foreignId('user_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();
            $table->timestamp('entered_at')
                ->nullable();
            $table->timestamp('completed_at')
                ->nullable();
            $table->string('action')
                ->nullable()
                ->comment('This indicates what action has been performed in this particular step.');
            $table->text('comment')
                ->nullable();
            $table->json('input_data')
                ->nullable();
            $table->timestamps();

            $table->foreign('step_id')->references('id')->on('steps')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workflow_instance_steps');
    }
};
