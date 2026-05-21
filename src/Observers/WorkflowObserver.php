<?php

namespace Workflow\Observers;

use Illuminate\Database\Eloquent\Model;
use Workflow\Services\WorkflowTriggerService;

/**
 * Observer: WorkflowObserver
 *
 * Purpose:
 * Provides a decoupled alternative to the Workflowable trait.
 * Listens to Eloquent lifecycle events (created, updated, saved, deleted) and automatically routes them to the WorkflowTriggerService.
 *
 * Usage:
 * Register this observer against any Eloquent model in your application's ServiceProvider (e.g. AppServiceProvider):
 *
 * use App\Models\Order;
 * use Workflow\Observers\WorkflowObserver;
 *
 * public function boot(): void
 * {
 *     Order::observe(WorkflowObserver::class);
 * }
 */
class WorkflowObserver
{
    protected WorkflowTriggerService $triggerService;

    public function __construct(WorkflowTriggerService $triggerService)
    {
        $this->triggerService = $triggerService;
    }

    /**
     * Handle the Model "created" event.
     */
    public function created(Model $model): void
    {
        $this->triggerService->trigger($model, 'created');
    }

    /**
     * Handle the Model "updated" event.
     */
    public function updated(Model $model): void
    {
        $this->triggerService->trigger($model, 'updated');
    }

    /**
     * Handle the Model "saved" event.
     */
    public function saved(Model $model): void
    {
        $this->triggerService->trigger($model, 'saved');
    }

    /**
     * Handle the Model "deleted" event.
     */
    public function deleted(Model $model): void
    {
        $this->triggerService->trigger($model, 'deleted');
    }
}
