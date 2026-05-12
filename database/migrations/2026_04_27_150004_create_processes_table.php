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
        Schema::create('processes', function (Blueprint $table) {

            $table->id()
                ->comment('Primary key of workflow process definition');

            $table->string('name')
                ->comment('Human-readable workflow process name');

            $table->string('code')
                ->unique()
                ->comment('Machine-readable unique workflow identifier');

            $table->text('description')
                ->nullable()
                ->comment('Optional description of workflow process');

            $table->boolean('is_active')
                ->default(true)
                ->comment('Controls whether workflow process is currently active');

            $table->json('graph_json')->nullable()
                ->comment('Stores the complete visual graph state (nodes, positions, connections) from Drawflow');    
            
            $table->json('canvas_state')->nullable()
                ->comment('Stores the canvas viewport offset and zoom level');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('processes');
    }
};
