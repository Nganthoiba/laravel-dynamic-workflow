<?php

namespace Workflow\Core\Handlers;

use Workflow\Models\Step;
use Workflow\Models\WorkflowInstanceStep;
use Workflow\Core\Contracts\StepActionInterface;
use Illuminate\Support\Collection;
use Workflow\Core\Contracts\NodeHandlerInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\View;
use Exception;

/**
 * Base NodeHandler implementation.
 * 
 * This class follows the Template Method pattern to standardize the execution flow
 * across different workflow node types while allowing specialized routing logic.
 */
abstract class NodeHandler implements NodeHandlerInterface
{
    protected Step $model;

    public function __construct(Step $step)
    {
        $this->model = $step;
    }

    public function id(): string
    {
        return $this->model->id;
    }

    public function name(): string
    {
        return $this->model->name;
    }

    public function type(): string
    {
        return $this->model->node_type;
    }

    /**
     * Resolves the blade view associated with the workflow_action.
     */
    public function view(): ?string
    {
        $config_workflow_actions = config('workflow.workflow_actions', []);

        $actionConfig = $config_workflow_actions[$this->model->workflow_action] ?? null;

        if (is_null($actionConfig)) {
            return null;
        }

        $viewName = 'workflow.steps.' . $actionConfig['view'];

        if (!View::exists($viewName)) {
            throw new Exception("View file not found: " . $viewName);
        }

        return $viewName;
    }

    public function edges(): Collection
    {
        return $this->model
            ->outgoingEdges()
            ->get();
    }

    protected function getEdge(string $branch): ?object
    {
        return $this->edges()
            ->where('branch_type', $branch)
            ->first();
    }

    /**
     * Resolves the StepAction class from config and returns an instance.
     */
    public function getAction(): ?StepActionInterface
    {
        $actionKey = $this->model->workflow_action;
        if (!$actionKey) {
            return null;
        }

        $actionClass = config("workflow.workflow_actions.{$actionKey}.action", null);
        if ($actionClass === null) {
            return null;
        }

        if (!class_exists($actionClass)) {
            throw new Exception("Invalid action class: $actionClass");
        }

        if (!is_subclass_of($actionClass, StepActionInterface::class)) {
            throw new Exception("Action class must implement StepActionInterface: $actionClass");
        }

        // Use Laravel service container for instantiation
        return app($actionClass);
    }

    /**
     * Entry point for node execution. 
     * Implements the Template Method pattern: executes action first, then processes routing.
     */
    public function handle(array $context, Model $model, ?WorkflowInstanceStep $workflowInstanceStep): ?string
    {
        $this->executeAction($context, $model, $workflowInstanceStep);

        return $this->process($context, $model, $workflowInstanceStep);
    }

    /**
     * Executes the StepActionInterface associated with this node if it exists.
     */
    protected function executeAction(array $context, Model $model, ?WorkflowInstanceStep $workflowInstanceStep): void
    {
        $action = $this->getAction();
        if ($action && $workflowInstanceStep) {
            $action->execute($context, $model, $workflowInstanceStep);
        }
    }

    /**
     * Node-specific logic to determine the next step in the workflow.
     */
    abstract protected function process(array $context, Model $model, ?WorkflowInstanceStep $workflowInstanceStep): ?string;
}
