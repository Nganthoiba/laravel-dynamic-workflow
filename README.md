# Laravel Dynamic Workflow

A powerful, dynamic graph-based workflow engine and visual designer for Laravel. This package allows you to define complex business processes using a visual interface and execute them with a robust, event-driven engine.

## Features

- **Visual Workflow Designer**: Integrated drag-and-drop designer powered by LogicFlow.
- **Dynamic Graph Engine**: Support for branching, conditions, and custom node handlers.
- **Customizable Actions**: Define business logic as `StepAction` classes.
- **Role-Based Routing**: Assign workflow steps to specific user roles.
- **Condition Support**: Dynamic branching based on data-driven conditions.
- **UUID Identifiers**: Uses UUIDs for steps and transitions to support seamless migrations and visual design.
- **Vendor Agnostic**: Easily integrates with your existing User and Role models.

## Installation

You can install the package via composer:

```bash
composer require workflow/laravel-dynamic-workflow
```

Publish the configuration, assets, and views:

```bash
php artisan vendor:publish --provider="Workflow\Providers\WorkflowServiceProvider"
```

Run the migrations:

```bash
php artisan migrate
```

## Configuration

The package configuration is located at `config/workflow.php`. You should update the `models` section to match your application's User and Role models:

```php
'models' => [
    'role' => \App\Models\Role::class,
    'user' => \App\Models\User::class,
],
```

You can also define your custom workflow actions and processes in this file.

## Usage

### Defining a Workflow

1. Navigate to the workflow designer route (e.g., `/workflow-designer/{process_id}`).
2. Create or edit a process.
3. Drag and drop nodes (Start, Step, Condition, End) and connect them.
4. Configure node properties like name, code, role assignments, and associated actions.
5. Save the workflow.

### Executing a Workflow

To start a workflow instance:

```php
use Workflow\Models\Process;
use Workflow\Services\WorkflowEngine;

$process = Process::where('code', 'YOUR_PROCESS_CODE')->first();
$engine = app(WorkflowEngine::class);
$instance = $engine->start($process, $referenceModel);
```

To move to the next step:

```php
$engine->transit($instance, $transitionId, $data);
```

## Custom Node Handlers

You can extend the workflow logic by creating custom node handlers. The package comes with default handlers for:
- `StartNodeHandler`
- `StepNodeHandler`
- `ConditionNodeHandler`
- `EndNodeHandler`

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
