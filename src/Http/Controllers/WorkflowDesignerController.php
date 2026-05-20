<?php

namespace Workflow\Http\Controllers;

use Workflow\Models\Process;
use Workflow\Models\Step;
use Workflow\Models\StepTransition;
use Workflow\Models\WorkflowInstanceStep;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class WorkflowDesignerController extends Controller
{
    /**
     * Show the designer view.
     */
    public function show(Process $process)
    {
        // Fetch roles using the configured model
        $roleModel = config('workflow.models.role');
        $roles = class_exists($roleModel) ? $roleModel::all() : collect([]);
        
        $workflowActions = config('workflow.workflow_actions', []);
        $title = 'Workflow Designer';
        
        return view(
            'workflow::designer',
            compact('process', 'roles', 'title', 'workflowActions')
        );
    }

    /**
     * Load existing workflow graph.
     */
    public function load(int $processId)
    {
        $process = Process::with(['steps.roles', 'steps.outgoingEdges'])->findOrFail($processId);

        $nodes = $process->steps->whereNull('deleted_at')->map(function ($step) {
            return [
                'id' => $step->id,
                'name' => $step->name,
                'node_type' => $step->node_type,
                'code' => $step->code,
                'description' => $step->description,
                'condition_json' => $step->condition_json,
                'workflow_action' => $step->workflow_action,
                'is_start' => $step->is_start,
                'is_end' => $step->is_end,
                'ui_json' => $step->ui_json,
                'roles' => $step->roles->pluck('id')->toArray(),
            ];
        });

        $edges = [];
        foreach ($process->steps->whereNull('deleted_at') as $step) {
            foreach ($step->outgoingEdges as $transition) {
                $edges[] = [
                    'id' => $transition->id,
                    'from' => $transition->from_step_id,
                    'to' => $transition->to_step_id,
                    'label' => $transition->action_label,
                    'branch_type' => $transition->branch_type,
                    'is_default' => $transition->is_default,
                ];
            }
        }

        return response()->json([
            'process_id' => $process->id,
            'canvas_state' => $process->canvas_state,
            'graph_json' => $process->graph_json,
            'nodes' => $nodes,
            'edges' => $edges,
        ]);
    }

    /**
     * Check if a step has open tasks.
     */
    public function hasOpenTasks(string $stepId)
    {
        $hasOpenTasks = WorkflowInstanceStep::where('step_id', $stepId)
            ->whereNull('completed_at')
            ->exists();

        return response()->json([
            'has_open_tasks' => $hasOpenTasks
        ]);
    }

    /**
     * Save/Update workflow graph.
     */
    public function save(Request $request)
    {
        $data = $request->validate([
            'process_id' => 'required|exists:processes,id',
            'nodes' => 'required|array',
            'edges' => 'required|array',
            'drawflow_json' => 'nullable|array',
            'canvas_state' => 'nullable|array',
        ]);

        try {
            return DB::transaction(function () use ($data) {
                $processId = $data['process_id'];
                $nodes = $data['nodes'];
                $edges = $data['edges'];
                $drawflowJson = $data['drawflow_json'] ?? null;
                $canvasState = $data['canvas_state'] ?? null;

                // Update Process with graph state and canvas state
                $process = Process::findOrFail($processId);
                $processUpdates = [];
                if ($drawflowJson !== null) {
                    $processUpdates['graph_json'] = $drawflowJson;
                }
                if ($canvasState !== null) {
                    $processUpdates['canvas_state'] = $canvasState;
                }
                if (!empty($processUpdates)) {
                    $process->update($processUpdates);
                }

                $presentStepIds = [];
                
                // 1. Process Nodes (Steps)
                foreach ($nodes as $node) {
                    $stepId = $node['id'];
                    $stepData = [
                        'id' => $stepId,
                        'process_id' => $processId,
                        'node_type' => $node['node_type'] ?? 'step',
                        'name' => $node['name'] ?? '',
                        'code' => $node['code'] ?? Str::slug($node['name'] ?? 'step-' . microtime(true)),
                        'description' => $node['description'] ?? null,
                        'condition_json' => $node['condition_json'] ?? null,
                        'workflow_action' => $node['workflow_action'] ?? null,
                        'is_start' => filter_var($node['is_start'] ?? false, FILTER_VALIDATE_BOOLEAN),
                        'is_end' => filter_var($node['is_end'] ?? false, FILTER_VALIDATE_BOOLEAN),
                        'ui_json' => $node['ui_json'] ?? null,
                    ];

                    $step = Step::updateOrCreate(['id' => $stepId], $stepData);
                    $presentStepIds[] = $step->id;

                    // Sync Roles
                    if (isset($node['roles']) && is_array($node['roles'])) {
                        $step->roles()->sync($node['roles']);
                    }
                }

                //Before deleting steps, check if there are steps whose tasks are opening
                // if 
                // To be deleted steps
                $unused_step_ids = Step::where('process_id', $processId)
                    ->whereNotIn('id', $presentStepIds)
                    ->pluck('id')
                    ->toArray();

                $hasOpenTasks = WorkflowInstanceStep::where('process_id', $processId)
                    ->whereIn('step_id', $unused_step_ids)
                    ->whereNull('completed_at')
                    ->exists();

                if($hasOpenTasks) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Cannot delete steps that have open tasks'
                    ], 422);
                }   

                // Delete steps that are no longer in the graph for this process
                Step::where('process_id', $processId)
                    ->whereNotIn('id', $presentStepIds)
                    ->delete();

                // 2. Process Edges (Transitions)
                StepTransition::where('process_id', $processId)->delete();

                foreach ($edges as $edge) {
                    $fromStepId = $edge['from'] ?? null;
                    $toStepId = $edge['to'] ?? null;

                    if ($fromStepId && $toStepId) {
                        StepTransition::create([
                            'id' => $edge['id'],
                            'process_id' => $processId,
                            'from_step_id' => $fromStepId,
                            'to_step_id' => $toStepId,
                            'branch_type' => $edge['branch_type'] ?? 'DEFAULT',
                            'is_default' => filter_var($edge['is_default'] ?? false, FILTER_VALIDATE_BOOLEAN),
                            'action_label' => $edge['label'] ?? 'Forward',
                            'is_active' => true,
                        ]);
                    }
                }

                // 3. Validation
                $this->validateWorkflowStructure($processId);

                return response()->json([
                    'status' => 'success',
                    'message' => 'Workflow graph saved successfully'
                ]);
            });
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Alias for save to support update endpoint.
     */
    public function update(Request $request)
    {
        return $this->save($request);
    }

    /**
     * Validate workflow business rules.
     */
    protected function validateWorkflowStructure($processId)
    {
        // Rule 1: Exactly one start step
        $startStepsCount = Step::where('process_id', $processId)->where('is_start', true)->count();
        if ($startStepsCount !== 1) {
            throw new \Exception("The workflow must have exactly one start step. Current count: {$startStepsCount}");
        }

        // Rule 2: At most one default transition per step
        $stepsWithMultipleDefaults = StepTransition::where('process_id', $processId)
            ->where('is_default', true)
            ->select('from_step_id', DB::raw('count(*) as total'))
            ->groupBy('from_step_id')
            ->having(DB::raw('count(*)'), '>', 1)
            ->get();

        if ($stepsWithMultipleDefaults->isNotEmpty()) {
            throw new \Exception("A step cannot have more than one default transition.");
        }
    }
}
