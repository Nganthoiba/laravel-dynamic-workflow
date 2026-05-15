<?php

namespace Workflow\Services;

use Workflow\Models\Step;
use Workflow\Models\WorkflowInstance;
use Workflow\Models\WorkflowInstanceStep;
use Workflow\Core\Factories\StepFactory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Exception;

/**
 * Class WorkflowInstanceService
 * 
 * Manages the runtime lifecycle of workflow instances and tasks.
 */
class WorkflowInstanceService
{
    /**
     * Start a new workflow process for a given business model.
     * 
     * Moves past the START node and lands on the first executable step.
     */
    public function start($process, $reference, array $context = []): WorkflowInstance
    {
        return DB::transaction(function () use ($process, $reference, $context) {
            $startNode = Step::where('process_id', $process->id)
                ->where('node_type', 'start')
                ->firstOrFail();

            $instance = WorkflowInstance::create([
                'process_id'      => $process->id,
                'reference_type'  => get_class($reference),
                'reference_id'    => $reference->getKey(),
                'current_step_id' => $startNode->id,
                'status'          => 'IN_PROGRESS',
                'started_at'      => \now(),
            ]);

            if (!$instance) {
                throw new \Exception("Unable to create workflow instance for the process " . get_class($reference));
            }

            // Auto-transition past the START node to find the first real task
            $handler = StepFactory::make($startNode);
            $firstStepId = $handler->handle($context, $reference, null);

            if (!$firstStepId) {
                throw new Exception("Workflow started but no outgoing transition found from START node.");
            }

            $this->moveToStep($instance, $firstStepId, $context);

            return $instance;
        });
    }

    /**
     * Advances the workflow by completing the current open task and moving to the next.
     */
    public function proceed(WorkflowInstance $instance, array $context = [])
    {
        DB::transaction(function () use ($instance, $context) {
            // 1. Find the currently active/open task
            $openTask = WorkflowInstanceStep::where('workflow_instance_id', $instance->id)
                ->where('step_id', $instance->current_step_id)
                ->whereNull('completed_at')
                ->first();

            $currentStep = Step::findOrFail($instance->current_step_id);
            $handler = StepFactory::make($currentStep);

            // 2. Execute business logic and determine next step
            // Note: handle() will execute the StepAction if one is configured
            $nextStepId = $handler->handle($context, $instance->reference, $openTask);

            // 3. Mark current task as completed
            if ($openTask) {
                $openTask->update([
                    'completed_at' => \now(),
                    'user_id'      => Auth::id(),
                    'comment'      => $context['comment'] ?? null,
                    'action'       => $currentStep->workflow_action,
                ]);
            }

            // 4. Move to next logical step (handling conditions automatically)
            $this->moveToStep($instance, $nextStepId, $context);
        });
    }

    /**
     * Recursively (or via loop) moves the workflow forward until it hits a human STEP or END.
     */
    protected function moveToStep(WorkflowInstance $instance, ?string $stepId, array $context)
    {
        while ($stepId) {
            $step = Step::findOrFail($stepId);

            if ($step->node_type === 'step') {
                // Land on a human task
                $instance->update(['current_step_id' => $step->id]);

                // Create an OPEN task for the inbox
                WorkflowInstanceStep::create([
                    'workflow_instance_id' => $instance->id,
                    'process_id'           => $instance->process_id,
                    'step_id'              => $step->id,
                    'entered_at'           => \now(),
                ]);
                return;
            }

            if ($step->node_type === 'condition') {
                // System node: evaluate and keep moving
                $handler = StepFactory::make($step);
                $stepId = $handler->handle($context, $instance->reference, null);
                continue;
            }

            if ($step->node_type === 'end') {
                // Workflow finished
                $instance->update([
                    'current_step_id' => $step->id,
                    'status'          => 'COMPLETED',
                    'completed_at'    => \now(),
                ]);
                return;
            }

            // Safety break
            break;
        }

        // If we exit the loop and have no stepId, it's effectively completed
        if (!$stepId) {
            $instance->update([
                'status'       => 'COMPLETED',
                'completed_at' => \now(),
            ]);
        }
    }

    /**
     * Terminate the workflow instance manually.
     */
    public function cancel(WorkflowInstance $instance, ?string $reason = null)
    {
        DB::transaction(function () use ($instance, $reason) {
            // 1. Close the currently active task if any
            WorkflowInstanceStep::where('workflow_instance_id', $instance->id)
                ->whereNull('completed_at')
                ->update([
                    'completed_at' => \now(),
                    'user_id'      => Auth::id(),
                    'comment'      => $reason ?? 'Workflow cancelled by user.',
                    'action'       => 'CANCELLED'
                ]);

            // 2. Mark instance as cancelled
            $instance->update([
                'status'       => 'CANCELLED',
                'cancelled_at' => \now(),
            ]);
        });
    }
}
