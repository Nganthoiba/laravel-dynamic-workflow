<?php

namespace Workflow\Services;

/**
 * Service: WorkflowBindingRegistry
 *
 * Purpose:
 * Serves as the central registry and helper service for managing allowed models and events.
 * It translates between internal Fully Qualified Class Names (FQCN) and friendly labels,
 * ensuring security and standardizing allowed model triggers across the application.
 *
 * Usage:
 * Inject via dependency injection or resolve using the Laravel container:
 *
 * $registry = app(Workflow\Services\WorkflowBindingRegistry::class);
 * $models = $registry->getModels(); // ['purchase_order' => 'Purchase Order']
 * $label = $registry->getModelLabel(\App\Models\PurchaseOrder::class); // 'Purchase Order'
 * $class = $registry->getModelClass('purchase_order'); // 'App\Models\PurchaseOrder'
 */
class WorkflowBindingRegistry
{
    /**
     * Get all registered models mapped as [key => label].
     *
     * @return array
     */
    public function getModels(): array
    {
        $models = config('workflow.workflow_models', []);
        $result = [];

        foreach ($models as $key => $config) {
            $result[$key] = $config['label'] ?? $key;
        }

        return $result;
    }

    /**
     * Get all registered trigger events mapped as [key => label].
     *
     * @return array
     */
    public function getEvents(): array
    {
        return config('workflow.workflow_events', [
            'created'   => 'Created',
        ]);
    }

    /**
     * Resolve a friendly label for an Eloquent model FQCN.
     *
     * @param string $class
     * @return string
     */
    public function getModelLabel(string $class): string
    {
        $models = config('workflow.workflow_models', []);

        foreach ($models as $config) {
            if (isset($config['class']) && ltrim($config['class'], '\\') === ltrim($class, '\\')) {
                return $config['label'];
            }
        }

        return class_basename($class);
    }

    /**
     * Resolve a friendly label for a trigger event key.
     *
     * @param string $event
     * @return string
     */
    public function getEventLabel(string $event): string
    {
        $events = $this->getEvents();

        return $events[$event] ?? ucfirst($event);
    }

    /**
     * Get the model FQCN class name by its registry key.
     *
     * @param string $key
     * @return string|null
     */
    public function getModelClass(string $key): ?string
    {
        return config("workflow.workflow_models.{$key}.class");
    }

    /**
     * Get the registry key for a given model FQCN.
     *
     * @param string $class
     * @return string|null
     */
    public function getModelKey(string $class): ?string
    {
        $models = config('workflow.workflow_models', []);

        foreach ($models as $key => $config) {
            if (isset($config['class']) && ltrim($config['class'], '\\') === ltrim($class, '\\')) {
                return $key;
            }
        }

        return null;
    }

    /**
     * Check if a given model key/class is allowed in the registry.
     *
     * @param string $value Key or FQCN class name
     * @return bool
     */
    public function isModelAllowed(string $value): bool
    {
        $models = config('workflow.workflow_models', []);

        if (array_key_exists($value, $models)) {
            return true;
        }

        foreach ($models as $config) {
            if (isset($config['class']) && ltrim($config['class'], '\\') === ltrim($value, '\\')) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if an event name is allowed.
     *
     * @param string $event
     * @return bool
     */
    public function isEventAllowed(string $event): bool
    {
        return array_key_exists($event, $this->getEvents());
    }
}
