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

## ⚡ Why Dynamic Workflows?

In real-world enterprise systems, business rules are rarely fixed. Organizational policies evolve, approval authorities change, and routing rules become more complex over time.

For example, a **Purchase Order** process might evolve from a simple `Employee → Manager` flow to a multi-stage `Employee → Assistant Manager → Manager → Finance Director → Admin` chain. Traditional hardcoded systems require code changes and redeployments for every such evolution.

This package enables administrators to:

- **Insert/Remove** intermediate approval steps.
- **Modify** routing conditions dynamically.
- **Redesign** approval hierarchies visually.
- **Adapt** processes without touching the application source code.

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

#### Field Schema Details

| Property         | Required | Description                                                              |
| :--------------- | :------- | :----------------------------------------------------------------------- |
| `operators_json` | **Yes**  | List of allowed operators (e.g., `=`, `!=`, `>`, `<`, `in`, `contains`). |
| `options_json`   | Optional | Required if type is `enum`. Array of `{value, label}` objects.           |

#### Example: Adding a Department Condition

```php
[
    'key' => 'department',
    'label' => 'Department',
    'type' => 'enum',
    'operators_json' => ['=', '!='],
    'options_json' => [
        ['value' => 'sales', 'label' => 'Sales Dept'],
        ['value' => 'hr', 'label' => 'Human Resources'],
    ],
],
```

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

### 🧠 Condition Intelligence

The engine supports intelligent runtime routing using **Condition Nodes**. These nodes evaluate logic based on two primary data sources:

- **Blade Views (Form Inputs)**: Data passed from the task form (e.g., `<button name="action_result" value="approved">`).
- **Model Attributes (Database)**: Fields directly from the associated Eloquent model (e.g., `total_amount`).

> [!TIP]
> **Why `action_result` Matters**: Even if not used in a condition, passing `action_result` (e.g., `approve`, `reject`, `revert`) ensures that the precise decision is recorded in the task history audit trail.

---

## 🎨 Visual Designer

Manage your workflows through an interactive UI at `/workflow/processes`.

### 1. Designing the Flow

Click **Designer** on any process to launch the canvas.

- **Drag & Drop**: Pull nodes from the sidebar onto the grid.
- **Connect**: Drag from the blue anchor points to create transitions.
- **Configure**: Click any node to open the properties panel (set names, actions, or roles).

### 🧰 Designer Toolbox

- 🟢 **Start Node**: Entry point of the workflow process.
- 🔵 **Step Node**: Executable business task assigned to roles.
- 🟡 **Condition Node**: Performs logical evaluation for branching.
- 🔴 **End Node**: Termination point.

### 🎮 Canvas Controls

- **Resize Nodes**: Select a node and use the handles to scale its boundaries.
- **Context Menu**: Hover over a node to reveal **Edit** and **Delete** shortcuts.
- **Zoom & Pan**: Use the header controls to scale the viewport.

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

## ⚡ Workflow Actions (Hooks)

Workflow actions define executable business behavior. Each step in the visual designer references a `workflow_action` hook which determines the Blade form view and its backend execution class.

### 1. Registering Actions

Define your actions in `config/workflow.php`. The `view` refers to the Blade file used for the task form, and `action` refers to the class that handles the submitted data.

```php
'workflow_actions' => [
    // Custom business actions
    'initiate_purchase_order' => [
        'label' => 'Initiate Purchase Order',
        'view' => 'initiate_purchase_order',
        'action' => \App\Workflow\Actions\InitiatePurchaseOrderAction::class,
    ],

    // Common utility actions
    'approve_and_forward' => [
        'label' => 'Approve and Forward',
        'view' => 'approve_and_forward',
        'action' => \App\Workflow\Actions\ApproveAndForwardAction::class,
    ],
    'approve' => [
        'label' => 'Approve',
        'view' => 'approve',
        'action' => \App\Workflow\Actions\ApproveAction::class,
    ],
],
```

Note: the **action** can be null if there is nothing to do at the backend.

### 2. Implementation

#### A. The Action Class

Action classes must implement `Workflow\Core\Contracts\StepActionInterface`. The `execute` method receives the submitted form data, the target model, and the task step.

```php
class ApproveOrderAction implements StepActionInterface {
    public function validate(array $data): array {
        return validator($data, [
            'remarks' => 'nullable|string|max:500',
            'action_result' => 'nullable|string',
        ])->validate();
    }

    public function execute(array $data, Model $model, WorkflowInstanceStep $step): void {
        // Logic to update your model based on the user's decision
        $model->update([
            'status' => ($data['action_result'] == 'reject' ? 'rejected' : 'approved'),
            'remark' => $data['remarks'] ?? null,
        ]);
    }
}
```

#### B. The Blade View

Every task form **must** extend the package's layout. Data passed from these fields will be automatically handled by the action class.

```blade
@extends('vendor.workflow.task_layout')

@section('form_fields')
    <div class="mb-3">
        <label class="form-label">Remarks</label>
        <textarea name="remarks" class="form-control"></textarea>
    </div>
@endsection

@section('form_actions')
    <button type="submit" name="action_result" value="rejected" class="btn btn-danger">Reject</button>
    <button type="submit" name="action_result" value="REVERT" class="btn btn-warning">Revert</button>
    <button type="submit" name="action_result" value="approved" class="btn btn-primary">Approve & Forward</button>
@endsection
```

---

## 🛠️ Technical Reference

### Database Schema

The package uses several tables to store definitions and execution data:

- `processes`: Stores high-level definitions (name, unique code, and the LogicFlow graph JSON).
- `steps`: Individual nodes within a workflow (start, end, task steps, and condition gates).
- `step_transitions`: The directed edges (arrows) connecting steps, including logical branch types (True/False).
- `workflow_instances`: Tracks the lifecycle of an active execution and its polymorphic link to a business model.
- `workflow_instance_steps`: The full audit trail of executed tasks, actions taken, and user comments.
- `workflow_bindings`: Mapping of Eloquent events to automatic workflow triggers.

### Model Requirements

Any model used in a workflow **must** have a `status` column. This is used by the engine to track and display badge states.

### Summary Views

When a user opens a task, they see a summary of the business object (e.g., a Purchase Order). You can explicitly map models to their specific summary views in `config/workflow.php`:

```php
'reference_views' => [
    \App\Models\PurchaseOrder::class => 'workflow.reference.purchase_order',
],
```

If no explicit mapping is provided, the engine falls back to a **default convention** based on the model's snake_case class name (e.g., `App\Models\PurchaseOrder` looks for `resources/views/workflow/reference/purchase_order.blade.php`).

### Advanced: Send Back (REVERT)

Enable automatic backtracking by adding a button with `value="REVERT"`. Unlike explicit backward arrows, the **REVERT** action is a built-in engine mechanism:

1. **Scan History**: The engine scans the `workflow_instance_steps` for the most recent completed human task.
2. **Re-open Task**: It closes the current task and creates a new pending task for that previous performer.
3. **Audit Trail**: The action is recorded as `REVERT` in the audit logs.

> [!NOTE]
> If you are on the first step of a workflow, the "Send Back" action will be ignored as there is no previous state to return to.

---

### 🏛️ Separation of Concerns

This package is designed to separate **Workflow Orchestration** from **Business Logic**:

- **Workflow Engine**: Manages routing, authorization, state transitions, and audit trails.
- **Application Logic**: Defines the business models, task views (Blade), and executable actions (Hooks).

This separation allows your business processes to evolve and scale without cluttering your core application code with complex state machine logic.

## 🚀 Future Roadmap

- [ ] Workflow versioning & templates.
- [ ] Separate APIs for use in frontend like ReactJs

## 🤝 Contributing & License

Contributions are welcome!
