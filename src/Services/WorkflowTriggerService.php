<?php

namespace Workflow\Services;

use Illuminate\Database\Eloquent\Model;
use Workflow\Models\WorkflowBinding;
use Workflow\Models\WorkflowInstance;

/**
 * Service: WorkflowTriggerService
 *
 * Purpose:
 * Orchestrates the evaluation and execution of workflow triggers.
 * It matches Eloquent model events against active database-driven bindings in the `workflow_bindings` table,
 * resolves conflicts by priority, and automatically starts new workflow instances if the trigger type is 'auto'.
 *
 * Usage:
 * This service is typically invoked automatically by the Workflowable trait or WorkflowObserver.
 * However, it can also be manually invoked to evaluate and trigger bindings:
 *
 * app(Workflow\Services\WorkflowTriggerService::class)->trigger($modelInstance, 'created');
 */
class WorkflowTriggerService
{
    protected WorkflowInstanceService $workflowInstanceService;

    public function __construct(WorkflowInstanceService $workflowInstanceService)
    {
        $this->workflowInstanceService = $workflowInstanceService;
    }

    /**
     * Trigger workflows for a given model event based on configured bindings.
     *
     * @param Model $model
     * @param string $eventName
     * @return void
     */
    public function trigger(Model $model, string $eventName): void
    {
        // 1. Fetch matching active bindings
        $bindings = WorkflowBinding::where('model_type', get_class($model))
            ->where('event_name', $eventName)
            ->where('is_active', true)
            ->orderBy('priority', 'desc')
            ->orderBy('id', 'asc')
            ->get();

        if ($bindings->isEmpty()) {
            return;
        }

        // 2. Resolve by priority: filter to match the highest priority bindings only
        $highestPriority = $bindings->first()->priority;
        $matchedBindings = $bindings->filter(fn($binding) => $binding->priority === $highestPriority);

        // 3. For each resolved binding, check if trigger_type is auto and start workflow
        foreach ($matchedBindings as $binding) {
            if ($binding->trigger_type === 'auto') {
                $process = $binding->process;
                if (!$process || !$process->is_active) {
                    continue;
                }

                // Avoid duplicating running instances of the same process for this model
                $alreadyExists = WorkflowInstance::where('process_id', $process->id)
                    ->where('reference_type', get_class($model))
                    ->where('reference_id', $model->getKey())
                    ->where('status', 'IN_PROGRESS')
                    ->exists();

                if (!$alreadyExists) {
                    $this->workflowInstanceService->start($process, $model);
                }
            }
        }
    }
}
