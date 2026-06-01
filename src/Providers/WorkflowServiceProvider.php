<?php

namespace Workflow\Providers;

use Illuminate\Support\ServiceProvider;
use Workflow\Listeners\WorkflowModelEventListener;
use Illuminate\Support\Facades\Event;

use function config_path;
use function resource_path;
use function public_path;
use function database_path;

class WorkflowServiceProvider extends ServiceProvider
{
    /**
     * Register services and merge package configuration.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/workflow.php',
            'workflow'
        );

        $this->mergeConfigFrom(
            __DIR__ . '/../../config/workflow_conditions.php',
            'workflow_conditions'
        );

        $this->app->singleton(\Workflow\Services\WorkflowBindingRegistry::class, function ($app) {
            return new \Workflow\Services\WorkflowBindingRegistry();
        });

        $this->app->singleton(\Workflow\Services\WorkflowTriggerService::class, function ($app) {
            return new \Workflow\Services\WorkflowTriggerService(
                $app->make(\Workflow\Services\WorkflowInstanceService::class)
            );
        });
    }

    /**
     * Bootstrap package services.
     */
    public function boot(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Load Migrations
        |--------------------------------------------------------------------------
        */
        $this->loadMigrationsFrom(
            __DIR__ . '/../../database/migrations'
        );

        /*
        |--------------------------------------------------------------------------
        | Load Routes
        |--------------------------------------------------------------------------
        */
        if (config('workflow.routes.enabled', true)) {
            $this->loadRoutesFrom(
                __DIR__ . '/../../routes/web.php'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Load Views
        |--------------------------------------------------------------------------
        |
        | Package views will be referenced using:
        | view('workflow::inbox')
        | view('workflow::steps.submit_form')
        */
        $this->loadViewsFrom(
            __DIR__ . '/../../resources/views',
            'workflow'
        );

        /*
        |--------------------------------------------------------------------------
        | Publish Config Files
        |--------------------------------------------------------------------------
        */
        $this->publishes([
            __DIR__ . '/../../config/workflow.php' => config_path('workflow.php'),
        ], 'workflow-config');



        /*
        |--------------------------------------------------------------------------
        | Publish Workflow Conditions Config
        |--------------------------------------------------------------------------
        */
        $this->publishes([
            __DIR__ . '/../../config/workflow_conditions.php'
            => config_path('workflow_conditions.php'),
        ], 'workflow-conditions-config');

        /*
        |--------------------------------------------------------------------------
        | Publish Views
        |--------------------------------------------------------------------------
        */
        $this->publishes([
            __DIR__ . '/../../resources/views' => resource_path('views/vendor/workflow'),
        ], 'workflow-views');

        /*
        |--------------------------------------------------------------------------
        | Publish Workflow Step Views (Runtime Actions)
        |--------------------------------------------------------------------------
        */
        $this->publishes([
            __DIR__ . '/../../resources/views/workflow/steps'
            => resource_path('views/workflow/steps'),
        ], 'workflow-step-views');

        /*
        |--------------------------------------------------------------------------
        | Publish Assets
        |--------------------------------------------------------------------------
        */
        $this->publishes([
            __DIR__ . '/../../public/vendor' => public_path('vendor'),
        ], 'workflow-assets');

        /*
        |--------------------------------------------------------------------------
        | Publish Migrations
        |--------------------------------------------------------------------------
        */
        $this->publishes([
            __DIR__ . '/../../database/migrations' => database_path('migrations'),
        ], 'workflow-migrations');

        /*
        |--------------------------------------------------------------------------
        | Global Eloquent Workflow Event Listeners
        |--------------------------------------------------------------------------
        |
        | Automatically listen to model events globally
        | so any Eloquent model can participate in
        | automatic workflow triggering.
        |
        */
        Event::listen('eloquent.created: *', [WorkflowModelEventListener::class, 'handle']);
        // Event::listen('eloquent.updated: *', [WorkflowModelEventListener::class, 'handle']);
        // Event::listen('eloquent.deleted: *', [WorkflowModelEventListener::class, 'handle']);
        // Event::listen('eloquent.saved: *', [WorkflowModelEventListener::class, 'handle']);
        // Event::listen('eloquent.restored: *', [WorkflowModelEventListener::class, 'handle']);
    }
}
