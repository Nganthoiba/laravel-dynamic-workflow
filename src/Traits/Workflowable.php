<?php

namespace Workflow\Traits;

use Illuminate\Database\Eloquent\Model;
use Workflow\Services\WorkflowTriggerService;

/**
 * Trait: Workflowable
 *
 * Purpose:
 * Provides automatic, event-driven workflow triggering capabilities to Eloquent models.
 * Registers model event listeners during the booting process of the model and triggers the WorkflowTriggerService.
 *
 * Usage:
 * 1. Import and use the trait in your Eloquent model:
 *
 *    use Workflow\Traits\Workflowable;
 *
 *    class Document extends Model
 *    {
 *        use Workflowable;
 *    }
 *
 * 2. (Optional) Define a static `workflowEvents` method on your model to customize which events trigger workflows:
 *
 *    public static function workflowEvents(): array
 *    {
 *        return ['created', 'updated', 'saved'];
 *    }
 */
trait Workflowable
{
    /**
     * Boot the Workflowable trait for the model.
     *
     * Registers model event listeners automatically.
     */
    protected static function bootWorkflowable(): void
    {
        $events = method_exists(static::class, 'workflowEvents')
            ? static::workflowEvents()
            : config('workflow.trigger_events', ['created', 'updated']);

        foreach ($events as $event) {
            static::registerModelEvent($event, function (Model $model) use ($event) {
                // Ensure model exists when not dealing with 'creating' or 'created'
                if (!$model->exists && !in_array($event, ['creating', 'created'])) {
                    return;
                }
                
                app(WorkflowTriggerService::class)->trigger($model, $event);
            });
        }
    }

    /**
     * Manually trigger workflow bindings for this model instance.
     *
     * @param string $event
     * @return void
     */
    public function triggerWorkflow(string $event): void
    {
        app(WorkflowTriggerService::class)->trigger($this, $event);
    }
}
