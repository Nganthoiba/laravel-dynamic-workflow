<?php

use Illuminate\Support\Facades\Route;
use Workflow\Http\Controllers\WorkflowDesignerController;
use Workflow\Http\Controllers\ConditionController;
use Workflow\Http\Controllers\WorkflowController;
use Workflow\Http\Controllers\ProcessController;
use Workflow\Http\Controllers\StepController;
use Workflow\Http\Controllers\StepTransitionController;

Route::middleware(['web', 'auth'])->group(function () {
    /** Workflow Designer */
    Route::get('/workflow-designer/load/{processId}', [WorkflowDesignerController::class, 'load'])->name('workflow-designer.load');
    Route::get('/workflow-designer/{process}', [WorkflowDesignerController::class, 'show'])->name('workflow-designer.show');
    Route::post('/workflow-designer/save', [WorkflowDesignerController::class, 'save'])->name('workflow-designer.save');
    Route::post('/workflow-designer/update', [WorkflowDesignerController::class, 'update'])->name('workflow-designer.update');
    Route::get('/workflow-designer/steps/{stepId}/has-open-tasks', [WorkflowDesignerController::class, 'hasOpenTasks'])->name('workflow-designer.steps.has-open-tasks');

    /** Workflow Conditions */
    Route::prefix('workflow-conditions')->group(function () {
        Route::get('/fields', [ConditionController::class, 'getFields'])->name('workflow-conditions.fields');
        Route::get('/templates', [ConditionController::class, 'getTemplates'])->name('workflow-conditions.templates');
        Route::post('/validate', [ConditionController::class, 'validateCondition'])->name('workflow-conditions.validate');
    });

    /** Workflow Management */
    Route::prefix('workflow')->as('workflow.')->group(function () {
        Route::resource('processes', ProcessController::class);
        Route::resource('steps', StepController::class);
        Route::resource('step-transitions', StepTransitionController::class);

        // Task Management
        Route::get('/inbox', [WorkflowController::class, 'inbox'])->name('inbox');
        Route::get('/outbox', [WorkflowController::class, 'outbox'])->name('outbox');
        Route::get('/tasks/{id}', [WorkflowController::class, 'showTask'])->name('tasks.show');
        Route::post('/tasks/{id}/handle', [WorkflowController::class, 'handleTask'])->name('tasks.handle');

        // Instance Tracking
        Route::get('/instances/{instance}/history', [WorkflowController::class, 'history'])->name('history');
        Route::get('/instances/{instance}/details', [WorkflowController::class, 'details'])->name('details');
        Route::post('/instances/{instance}/cancel', [WorkflowController::class, 'cancel'])->name('cancel');
    });
});
