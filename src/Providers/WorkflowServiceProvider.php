<?php

namespace Workflow\Providers;

use Illuminate\Support\ServiceProvider;

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
        $this->loadRoutesFrom(
            __DIR__ . '/../../routes/web.php'
        );

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
        | Publish Assets
        |--------------------------------------------------------------------------
        */
        $this->publishes([
            __DIR__ . '/../../public/logicflow' => public_path('vendor/logicflow'),
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
        | Publish Models
        |--------------------------------------------------------------------------
        */
        $this->publishes([
            __DIR__ . '/../Models' => app_path('Models/Workflow'),
        ], 'workflow-models');
    }
}