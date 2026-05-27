<?php

namespace Workflow\Listeners;

use Illuminate\Database\Eloquent\Model;
use Workflow\Services\WorkflowTriggerService;
use Workflow\Traits\Workflowable;
use Illuminate\Support\Str;

class WorkflowModelEventListener
{
    protected static $workflowableCache = [];
    public function handle(string $eventName, array $payload): void
    {
        $model = $payload[0] ?? null;

        if (! $model instanceof Model) {
            return;
        }

        if ($this->modelUsesWorkflowable($model)) {
            return;
        }

        app(WorkflowTriggerService::class)->trigger($model, $this->normalizeEvent($eventName));
    }

    protected function normalizeEvent(string $eventName): string
    {
        // Convert "eloquent.created: App\Models\Order" -> "created"
        //if (preg_match('/eloquent\.(\w+):\s/', $eventName, $m)) {
        if (preg_match('/eloquent\.(\w+):/', $eventName, $m)) {
            return $m[1];
        }

        return $eventName;
    }
    /*
    protected function modelUsesWorkflowable($model): bool
    {
        return in_array(
            Workflowable::class,
            \class_uses_recursive($model),
            true
        );
    }
    */

    protected function modelUsesWorkflowable($model): bool
    {
        $class = get_class($model);
        return self::$workflowableCache[$class] ??= in_array(
            Workflowable::class,
            \class_uses_recursive($model),
            true
        );
    }
}
