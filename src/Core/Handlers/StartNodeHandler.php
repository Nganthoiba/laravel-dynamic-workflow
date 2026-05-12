<?php

namespace Workflow\Core\Handlers;

use Workflow\Models\WorkflowInstanceStep;
use Illuminate\Database\Eloquent\Model;

class StartNodeHandler extends NodeHandler
{
    protected function process(array $context, Model $model, ?WorkflowInstanceStep $workflowInstanceStep): ?string
    {
        $edge = $this->edges()
            ->where('branch_type', 'DEFAULT')
            ->first();

        return $edge?->to_step_id;
    }
}
