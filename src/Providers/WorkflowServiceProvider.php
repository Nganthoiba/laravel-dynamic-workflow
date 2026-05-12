<?php

namespace Workflow\Providers;

use Illuminate\Support\ServiceProvider;

class WorkflowServiceProvider extends ServiceProvider
{
     /*
    |--------------------------------------------------------------------------
    | Merge Config
    |--------------------------------------------------------------------------
    */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../../config/workflow.php', 'workflow');
        $this->mergeConfigFrom(__DIR__ . '/../../config/workflow_conditions.php', 'workflow-conditions');
    }

    public function boot(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Load Migrations
        |--------------------------------------------------------------------------
        */
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');

        /*
        |--------------------------------------------------------------------------
        | Load Routes
        |--------------------------------------------------------------------------
        */
        $this->loadRoutesFrom(__DIR__ . '/../../routes/web.php');

        /*
        |--------------------------------------------------------------------------
        | Load Views
        |--------------------------------------------------------------------------
        */
        // Views will be accessed via view('workflow::inbox')
        $this->loadViewsFrom(__DIR__ . '/../../resources/views', 'workflow');

        $this->publishes([
            __DIR__ . '/../../config/workflow.php' => config_path('workflow.php'),
        ], 'workflow-config');

        $this->publishes([
            __DIR__ . '/../../resources/views' => resource_path('views/vendor/workflow'),
        ], 'workflow-views');

        $this->publishes([
            __DIR__ . '/../../public/logicflow' => public_path('vendor/logicflow'),
        ], 'workflow-assets');
    }
}
