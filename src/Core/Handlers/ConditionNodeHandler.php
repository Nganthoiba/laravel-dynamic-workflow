<?php

namespace Workflow\Core\Handlers;

use Workflow\Models\WorkflowInstanceStep;
use Workflow\Core\Handlers\NodeHandler;
use Illuminate\Database\Eloquent\Model;

class ConditionNodeHandler extends NodeHandler
{
    /**
     * Overridden to prevent action execution for condition nodes.
     */
    protected function executeAction(array $context, Model $model, ?WorkflowInstanceStep $workflowInstanceStep): void
    {
        // Condition nodes do not execute actions
    }

    /**
     * Evaluates the node condition and routes to the TRUE or FALSE branch.
     */
    protected function process(array $context, Model $model, ?WorkflowInstanceStep $workflowInstanceStep): ?string
    {
        $result = $this->evaluate($this->model->condition_json, $context);

        $branch = $result ? 'TRUE' : 'FALSE';

        // Outgoing edges
        $edge = $this->edges()
            ->where('branch_type', $branch)
            ->first();


        //return $edge?->to_step_id;
        if (!$edge) {
            return null;
        }

        //What if the to_step_id is again another conditional node
        $nextStep = $edge->toStep;
        if ($nextStep->node_type === 'condition') {
            return $this->process($context, $nextStep, $workflowInstanceStep);
        }

        return $edge?->to_step_id;
    }

    protected function evaluate(?array $cond, array $ctx): bool
    {
        if (!$cond || empty($cond)) return false;

        // Handle AND logic
        if (isset($cond['AND']) && is_array($cond['AND'])) {
            foreach ($cond['AND'] as $sub) {
                if (!$this->evaluate($sub, $ctx)) return false;
            }
            return true;
        }

        // Handle OR logic
        if (isset($cond['OR']) && is_array($cond['OR'])) {
            foreach ($cond['OR'] as $sub) {
                if ($this->evaluate($sub, $ctx)) return true;
            }
            return false;
        }

        // Base case: single condition row
        $field = $cond['field'] ?? null;
        $op    = $cond['operator'] ?? '=';
        $value = $cond['value'] ?? null;

        $actual = $ctx[$field] ?? null;

        return match ($op) {
            '>'  => $actual >  $value,
            '<'  => $actual <  $value,
            '='  => $actual == $value,
            '!=' => $actual != $value,
            '>=' => $actual >= $value,
            '<=' => $actual <= $value,
            'in' => is_array($value) ? in_array($actual, $value) : ($actual == $value),
            'contains' => str_contains((string)$actual, (string)$value),
            default => false,
        };
    }
}
