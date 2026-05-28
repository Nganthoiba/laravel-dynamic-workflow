# Laravel Dynamic Workflow

A powerful, dynamic graph-based workflow engine and visual designer for Laravel.

This package enables you to design complex business processes visually and execute them with a robust runtime engine. It supports dynamic routing, conditional branching, role-based authorization, and full audit tracking—all without modifying application source code.

## ✨ Features

- **Visual Designer**: Drag-and-drop workflow builder powered by LogicFlow.
- **Dynamic Routing**: Modify approval chains and logic on the fly.
- **Conditional Branching**: Intelligent runtime routing based on data or user input.
- **Task Management**: Built-in Inbox/Outbox for user tasks.
- **Role-Based**: Seamless integration with your existing role/permission system.
- **Audit Trails**: Complete history of every action and decision.
- **Enterprise Ready**: Designed for adaptability in complex regulatory environments.

---

## 🚀 Quick Start

Get a workflow up and running in minutes.

### 1. Install & Setup

```bash
composer require nganthoiba/laravel-dynamic-workflow
php artisan vendor:publish --provider="Workflow\Providers\WorkflowServiceProvider"
php artisan migrate
```

### 2. Register Your Models

Define which models can trigger workflows in `config/workflow.php`:

```php
'workflow_models' => [
    'purchase_order' => [
        'label' => 'Purchase Order',
        'class' => \App\Models\PurchaseOrder::class,
    ],
],
```

### 3. Design & Bind

1. Go to `/workflow/processes` and create your first process.
2. Use the **Designer** to draw your workflow (Start → Step → End).
3. Go to `/workflow-bindings` to map your model (e.g., `PurchaseOrder`) and event (e.g., `created`) to the process.

**That's it!** Creating a `PurchaseOrder` will now automatically launch your workflow.

---

## ⚙️ Configuration & Logic

The package uses two main configuration files to manage workflow behavior.

### 1. General Settings (`config/workflow.php`)

Define your business models, roles, and supported events.

```php
'workflow_models' => [
    'purchase_order' => [
        'label' => 'Purchase Order',
        'class' => \App\Models\PurchaseOrder::class,
    ],
],

'models' => [
    'role' => \App\Models\Role::class,
    'user' => \App\Models\User::class,
],
```

### 2. Condition Fields (`config/workflow_conditions.php`)

Define the fields available for logical branching in the designer.

| Key     | Type     | Description                                           |
| :------ | :------- | :---------------------------------------------------- |
| `key`   | `string` | The identifier (e.g., `amount`) the engine looks for. |
| `label` | `string` | The human-readable name shown in the designer.        |
| `type`  | `string` | `number`, `string`, `date`, or `enum`.                |

> [!IMPORTANT]
> **Data Sources**: Logic can evaluate both **Database Attributes** (like `order_amount`) and **Form Inputs** (like `action_result`). The `action_result` parameter is specifically used to record user decisions.

---

## 🧩 Core Concepts

The workflow engine operates on a **Graph-Based Architecture**.

### Node Types

- **Start Node**: The entry point. Every process must have exactly one.
- **Step Node**: A business task assigned to a role.
- **Condition Node**: Logical branching ($True/False$).
- **End Node**: The termination point.

### State Management

1. **Instances (`workflow_instances`)**: Tracks the current active step and overall status (IN_PROGRESS, COMPLETED, etc.).
2. **History (`workflow_instance_steps`)**: A full audit trail of every performed task, including who did it and when.

---

## 🎨 Visual Designer

Manage your workflows through an interactive UI at `/workflow/processes`.

### 1. Designing the Flow

Click **Designer** on any process to launch the canvas.

- **Drag & Drop**: Pull nodes from the sidebar onto the grid.
- **Connect**: Drag from the blue anchor points to create transitions.
- **Configure**: Click any node to open the properties panel (set names, actions, or roles).

### 2. Event Triggers (UI Binding)

Map your models to workflows without code at `/workflow-bindings`.

- **Target**: The workflow process to launch.
- **Trigger**: The Eloquent event (defaults to `created`).
- **Priority**: Execution order if multiple bindings exist.

---

## 📥 Task Management

### Inbox (`/workflow/inbox`)

- View and execute pending tasks.
- Only users with authorized roles can see specific tasks.

### Outbox (`/workflow/outbox`)

- Track your completed tasks and audit your decisions.

---

## 🔄 Starting a Workflow

### Automatic Mode (Recommended)

By default, the package globally listens for the **created** event on all registered models. If you register a binding in the UI (`/workflow-bindings`), the engine will automatically start the workflow when a model is created.

### Manual Mode

Start workflows programmatically:

```php
$service = app(WorkflowInstanceService::class);
$instance = $service->start($process, $order);
$service->proceed($instance, $request->all());
```

---

## Workflow Actions (Hooks)

Workflow actions define executable business behavior. Each step references a `workflow_action` hook.

### 1. Create the Action Class

Implement `Workflow\Core\Contracts\StepActionInterface`:

```php
class ApproveOrderAction implements StepActionInterface {
    public function validate(array $data): array {
        return validator($data, ['remarks' => 'nullable|string'])->validate();
    }

    public function execute(array $data, Model $model, WorkflowInstanceStep $step): void {
        $model->update(['status' => ($data['action_result'] == 'reject' ? 'rejected' : 'approved')]);
    }
}
```

### 2. Create the View

Blade views must extend `@extends('vendor.workflow.task_layout')`. Use `@section('form_fields')` for inputs.

---

## 🛠️ Technical Reference

### Database Schema

The package uses several tables to store definitions and execution data:

- `processes`: Workflow definitions and graph JSON.
- `steps`: Individual nodes and logical rules.
- `step_transitions`: Directed edges between steps.
- `workflow_instances`: Lifecycle of an active execution.
- `workflow_instance_steps`: Full audit trail of executed tasks.
- `workflow_bindings`: Mapping of Eloquent events (defaults to `created`) to workflows.

### Model Requirements

Any model used in a workflow **must** have a `status` column. This is used by the engine to track and display badge states.

### Summary Views

When a user opens a task, they see a summary of the business object. Create a view at `resources/views/workflow/reference/model_name.blade.php`.

### Advanced: Send Back (REVERT)

Enable automatic backtracking by adding a button with `value="REVERT"`. The engine will return the task to the previous human performer.

## 🚀 Future Roadmap

- [ ] Parallel workflow branches.
- [ ] Workflow versioning & templates.
- [ ] WebSocket-powered live task updates.
- [ ] SLA and performance tracking.

## 🤝 Contributing & License

Contributions are welcome! This package is licensed under the **MIT License**.
