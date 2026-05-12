<?php

namespace Workflow\Core\Handlers;

use Workflow\Models\WorkflowInstanceStep;
use Illuminate\Database\Eloquent\Model;

class EndNodeHandler extends NodeHandler
{
    /**
     * End nodes terminate the workflow by returning null.
     */
    protected function process(array $context, Model $model, ?WorkflowInstanceStep $workflowInstanceStep): ?string
    {
        // return null because there is no further step
        return null;
    }
}
