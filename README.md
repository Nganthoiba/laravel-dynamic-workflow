# Laravel Dynamic Workflow

A powerful, dynamic graph-based workflow engine and visual designer for Laravel. This package allows you to define complex business processes using a visual interface and execute them with a robust, event-driven engine.

## Features

- **Visual Workflow Designer**: Integrated drag-and-drop designer powered by LogicFlow.
- **Dynamic Graph Engine**: Support for branching, conditions, and custom node handlers.
- **Customizable Actions (Hooks)**: Define business logic as `StepAction` classes that trigger during transitions.
- **Role-Based Routing**: Assign workflow steps to specific user roles.
- **Condition Support**: Dynamic branching based on data-driven conditions.
- **Vendor Agnostic**: easily integrates with your existing User and Role models.

## Installation

Install the package via composer:

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

Update `config/workflow.php` to map your application's identity models:

```php
'models' => [
    'role' => \App\Models\Role::class,
    'user' => \App\Models\User::class,
],
```

## Usage Guide

### 1. Defining Workflow Actions (Hooks)

Hooks in this package are implemented as `StepAction` classes. These classes contain the business logic that should execute when a workflow reaches or leaves a specific step.

Create a class that implements `Workflow\Core\Contracts\StepActionInterface`:

```php
namespace App\Workflow\Actions;

use Workflow\Core\Contracts\StepActionInterface;
use Workflow\Models\WorkflowInstanceStep;
use Illuminate\Database\Eloquent\Model;

class ApproveOrderAction implements StepActionInterface
{
    public function validate(array $data): array
    {
        return validator($data, [
            'remarks' => 'nullable|string|max:500',
        ])->validate();
    }

    public function execute(array $data, Model $model, WorkflowInstanceStep $workflowInstanceStep): void
    {
        // $model is your business model (e.g., Order)
        $model->update(['status' => 'approved']);
        
        // Log the action or send notifications
        logger()->info("Order {$model->id} approved by user.");
    }
}
```

### 2. Registering Actions

Register your actions in `config/workflow.php` so they appear in the Designer:

```php
'workflow_actions' => [
    'approve_order' => [
        'label' => 'Approve Order',
        'view'  => 'approve_order_view', // Blade view for the task UI
        'action' => \App\Workflow\Actions\ApproveOrderAction::class,
    ],
],
```

### 3. Visual Designing

1. Access the designer at `/workflow/processes`.
2. Create a new process.
3. Use the **Launch Designer** button to open the visual editor.
4. Drag nodes onto the canvas:
    - **Step Nodes**: Assign them a "Workflow Action" (hook) and "Authorized Roles".
    - **Condition Nodes**: Define branching logic based on the data context.
5. Connect nodes using arrows to define the flow.

### 4. Starting a Workflow

To start a workflow for a specific business model instance:

```php
use Workflow\Models\Process;
use Workflow\Services\WorkflowInstanceService;

$process = Process::where('code', 'ORDER_APPROVAL')->first();
$order = Order::find(1);

$service = app(WorkflowInstanceService::class);
$instance = $service->initialize($process, $order);
```

### 5. Handling Tasks (The Inbox)

The package provides a built-in Inbox UI at `/workflow/inbox`. 

When a user submits a task through the UI:
1. The `WorkflowController` resolves the `StepAction` associated with the current step.
2. It runs the `validate()` method.
3. If valid, it runs the `execute()` method (the "hook").
4. Finally, the engine moves the workflow to the next step based on the graph definition.

## Customization

### Custom Node Handlers
If you need custom routing logic (e.g., waiting for multiple parallel approvals), you can extend `Workflow\Core\Handlers\NodeHandler` and register it in the `StepFactory`.

### UI Layout
The package views extend `workflow::task_layout`. You can publish and customize this layout to match your application's theme.

## License

The MIT License (MIT).
