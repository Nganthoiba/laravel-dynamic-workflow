<?php

namespace Workflow\Core\Contracts;

use Workflow\Models\WorkflowInstanceStep;
use Illuminate\Database\Eloquent\Model;

interface StepActionInterface
{
    public function validate(array $data): array;

    /**
     * -------------- Core business logic, what can be done with the $data ---------------
     * @param array $data
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param \Workflow\Models\WorkflowInstanceStep $workflowInstanceStep
     * 
     * This execute() will do the main task, what to do with the workflow instance according to the data provided
     * @return void
     * -----------------------------------------------------------------------------------
     */
    public function execute(array $data, Model $model, WorkflowInstanceStep $workflowInstanceStep): void;
}
