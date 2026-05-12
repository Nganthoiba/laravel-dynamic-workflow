<?php

namespace Workflow\Core\Contracts;

use Workflow\Models\Step;
use Workflow\Models\WorkflowInstanceStep;
use Workflow\Core\Contracts\StepActionInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

interface NodeHandlerInterface
{
    /**
     * Constructor must accept Step model
     */
    public function __construct(Step $step);

    /**
     * Node identity
     */
    public function id(): string;

    public function name(): string;

    public function type(): string;

    /**
     * Blade view (only for step nodes)
     */
    public function view(): ?string;

    /**
     * Outgoing edges
     */
    public function edges(): Collection;

    /**
     * Core execution logic
     * Returns next step ID or null if workflow ends
     * 
     * @param array $context Facts/data for routing and logic
     * @param \Illuminate\Database\Eloquent\Model $model The business model instance
     * @param WorkflowInstanceStep|null $workflowInstanceStep Current task record
     * @return int|null Next step ID or null
     */
    public function handle(array $context, Model $model, ?WorkflowInstanceStep $workflowInstanceStep): ?string;

    /**
     * ----------------------------------------------------------------------------------
     * Get action associated with the step
     * 
     * @return StepActionInterface
     * 
     */
    public function getAction(): ?StepActionInterface;
}
