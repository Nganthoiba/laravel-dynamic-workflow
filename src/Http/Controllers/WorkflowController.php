<?php

namespace Workflow\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Workflow\Models\Step;
use Workflow\Models\WorkflowInstance;
use Workflow\Models\WorkflowInstanceStep;
use Workflow\Core\Factories\StepFactory;
use Workflow\Services\WorkflowInstanceService;
use Workflow\Services\StepAuthorizationService;
use Exception;

class WorkflowController extends Controller
{
    protected $workflowService;
    protected $authService;

    public function __construct(WorkflowInstanceService $workflowService, StepAuthorizationService $authService)
    {
        $this->workflowService = $workflowService;
        $this->authService = $authService;
    }

    /**
     * Display a list of open tasks assigned to the current user's roles.
     */
    public function inbox()
    {
        // Assuming host application User model has a 'roles' relationship
        $userRoleIds = Auth::user()->roles?->pluck('id')->toArray() ?? [];

        // Find steps that the current user's roles can execute
        $authorizedStepIds = Step::where('node_type', 'step')
            ->whereHas('roles', function($q) use ($userRoleIds) {
                $q->whereIn('roles.id', $userRoleIds);
            })
            ->orWhereDoesntHave('roles') // Steps with no roles are public/default
            ->pluck('id');

        $tasks = WorkflowInstanceStep::with(['workflowInstance.process', 'step', 'workflowInstance.reference'])
            ->whereIn('step_id', $authorizedStepIds)
            ->whereNull('completed_at')
            ->orderBy('entered_at', 'desc')
            ->get();

        return view('workflow::inbox', compact('tasks'));
    }

    /**
     * Display a list of tasks completed by the current user.
     */
    public function outbox()
    {
        $tasks = WorkflowInstanceStep::with(['workflowInstance.process', 'step', 'workflowInstance.reference'])
            ->where('user_id', Auth::id())
            ->whereNotNull('completed_at')
            ->orderBy('completed_at', 'desc')
            ->get();

        return view('workflow::outbox', compact('tasks'));
    }

    /**
     * Show the execution screen for a specific task.
     */
    public function showTask($id)
    {
        $task = WorkflowInstanceStep::with([
            'workflowInstance.process', 
            'step', 
            'workflowInstance.steps.step.roles', 
            'workflowInstance.steps.user'
        ])->findOrFail($id);
        $readonly = false;

        if ($task->completed_at) {
            $readonly = true;
        }

        $instance = $task->workflowInstance;
        $step = $task->step;
        $model = $instance->reference;

        // Authorization Check for non-completed tasks
        if (!$readonly) {
            $userRoleIds = Auth::user()->roles?->pluck('id')->toArray() ?? [];
            if (!$this->authService->canExecuteStep($step, $userRoleIds)) {
                abort(403, "You are not authorized to perform this task.");
            }
        }

        // Resolve View via Handler
        $handler = StepFactory::make($step);
        $view = $handler->view();

        if (!$view) {
            abort(404, "No interface defined for this workflow action: " . $step->workflow_action);
        }

        return view($view, [
            'task'     => $task,
            'instance' => $instance,
            'step'     => $step,
            'model'    => $model,
            'readonly' => $readonly,
        ]);
    }

    /**
     * Handle the submission of a workflow task.
     */
    public function handleTask(Request $request, $id)
    {
        $task = WorkflowInstanceStep::with(['workflowInstance', 'step'])->findOrFail($id);
        
        if ($task->completed_at) {
            return redirect()->route('workflow.inbox')->with('error', 'Task already completed.');
        }

        $step = $task->step;
        $instance = $task->workflowInstance;
        $model = $instance->reference;

        // 1. Authorization
        $userRoleIds = Auth::user()->roles?->pluck('id')->toArray() ?? [];
        if (!$this->authService->canExecuteStep($step, $userRoleIds)) {
            abort(403);
        }

        // 2. Resolve Handler and Action
        $handler = StepFactory::make($step);
        $action = $handler->getAction();

        // 3. Validation
        $validated = $action ? $action->validate($request->all()) : $request->all();

        try {
            // 4. Build Context (merge model data and validated input)
            $context = array_merge($model?->toArray() ?? [], $validated);

            // 5. Proceed Workflow
            $this->workflowService->proceed($instance, $context);

            return redirect()->route('workflow.inbox')->with('success', 'Task submitted successfully.');

        } catch (Exception $e) {
            return back()->withInput()->with('error', 'Workflow error: ' . $e->getMessage());
        }
    }

    public function history(int $instanceId)
    {
        $instance = WorkflowInstance::findOrFail($instanceId);
        $history = WorkflowInstanceStep::with(['step', 'user'])
            ->where('workflow_instance_id', $instance->id)
            ->whereNotNull('completed_at')
            ->orderBy('completed_at', 'asc')
            ->get();

        return view('workflow::history', compact('instance', 'history'));
    }

    public function details(int $instanceId)
    {
        $instance = WorkflowInstance::with(['process', 'currentStep', 'steps.step', 'steps.user'])
            ->findOrFail($instanceId);

        return view('workflow::details', compact('instance'));
    }

    /**
     * Cancel the workflow instance.
     */
    public function cancel(Request $request, int $instanceId)
    {
        $instance = WorkflowInstance::findOrFail($instanceId);
        
        $this->workflowService->cancel($instance, $request->get('reason'));

        return redirect()->route('workflow.inbox')->with('success', 'Workflow instance cancelled.');
    }
}
