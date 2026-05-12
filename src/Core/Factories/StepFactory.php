<?php

namespace Workflow\Core\Factories;

use Workflow\Models\Step;
use Workflow\Core\Contracts\NodeHandlerInterface;
use Workflow\Core\Handlers\StepNodeHandler;
use Workflow\Core\Handlers\ConditionNodeHandler;
use Workflow\Core\Handlers\StartNodeHandler;
use Workflow\Core\Handlers\EndNodeHandler;
use Exception;

class StepFactory
{
    public static function make(Step $step): NodeHandlerInterface
    {
        return match ($step->node_type) {

            'step'      => new StepNodeHandler($step),

            'condition' => new ConditionNodeHandler($step),

            'start'     => new StartNodeHandler($step),

            'end'       => new EndNodeHandler($step),

            default     => throw new Exception("Unknown node type: {$step->node_type}")
        };
    }
}
