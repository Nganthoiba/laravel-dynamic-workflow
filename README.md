# Laravel Dynamic Workflow

A powerful, dynamic graph-based workflow engine and visual designer for Laravel.

This package allows you to define complex business processes visually and execute them using a robust runtime workflow engine with support for dynamic routing, conditional branching, runtime task management, role-based authorization, and workflow history tracking.

Unlike traditional hardcoded approval systems, this package enables workflows to evolve dynamically over time without modifying application source code.

## Features

- Visual Workflow Designer powered by LogicFlow
- Dynamic Graph-Based Workflow Engine
- Conditional Workflow Routing
- Nested Conditional Branching
- Runtime Task Inbox
- Role-Based Workflow Execution
- Workflow History and Audit Tracking
- Workflow Cancellation Support
- Configurable Workflow Actions
- Reusable StepAction Hooks
- Dynamic Step Addition/Removal
- Enterprise Workflow Architecture
- Vendor Agnostic User/Role Integration
- Composer Installable Laravel Package

## Why Dynamic Workflows Matter

In real-world enterprise systems, workflows are rarely fixed permanently.

Business rules continuously evolve over time.

For example, a Purchase Order approval process may initially follow this flow:

Employee
↓
Assistant Manager
↓
Sales Director
↓
Admin Approval

However, organizational policies frequently change.

At any point in time:

- a new approval authority may be added
- an existing authority may be removed
- approval chains may become shorter or longer
- conditional routing rules may change
- different departments may follow different workflows
- workflows may vary based on amount, region, category, or risk level

Example:
**If amount > 5,00,000**
→ Finance Director Approval Required

**Else**
→ Skip Finance Director

Or:
**If region = HQ**
→ Route to Central Admin

**Else**
→ Route to Regional Office

Traditional hardcoded approval systems become difficult to maintain in such scenarios because every workflow change requires:

- modifying source code
- redeploying the application
- database changes
- developer intervention

This package solves that problem by providing a fully dynamic graph-based workflow engine.

## Dynamic Workflow Modification

One of the core strengths of this package is that workflows can be modified dynamically without changing application source code.

Administrators can:

- add new workflow steps
- remove existing workflow steps
- insert intermediate approvals
- modify routing conditions
- redesign the workflow graph visually
- change role assignments
- alter conditional branching
- redesign approval hierarchies

All of these changes can be performed visually using the Workflow Designer.

Example:
A workflow can evolve from:
Employee → Manager → Admin

to:
Employee → Assistant Manager → Manager → Finance Director → Admin

without modifying the core business application.

## Conditional Workflow Intelligence

The workflow engine supports intelligent runtime routing using condition nodes.

Condition nodes dynamically determine the next workflow path based on runtime data.

Examples:

- purchase amount
- department
- region
- user role
- submission type
- risk category
- approval priority
- custom business fields

This allows highly flexible workflow automation.

Example:
**If amount > 100000**
→ Route to Director

**Else**
→ Route directly to Admin

Nested conditional workflows are also supported (e.g., Amount > 100000 → Region = HQ → Risk Level = HIGH), allowing enterprise-grade decision trees.

## Designed for Enterprise Adaptability

This package is specifically designed for environments where workflow structures are not permanently fixed.

Suitable industries include:

- Government systems
- Excise and permit systems
- ERP systems
- Banking systems
- Manufacturing systems
- Universities and institutions
- Warehouse management
- Dispatch systems
- HR approval systems
- Procurement systems
- Compliance systems

The engine enables organizations to continuously evolve business processes without rewriting application logic.

## Separation of Workflow and Business Logic

The package separates:

- workflow definition
- workflow routing
- runtime task execution
- business actions
- approval hierarchy
- UI rendering

This separation makes large systems significantly easier to maintain and extend. Business applications only define workflow actions, forms/views, and business operations, while the workflow engine dynamically controls routing, authorization, transitions, task assignment, and conditional branching.

## Runtime Workflow Flexibility

The workflow runtime engine supports:

- active workflow instances
- pending task inboxes
- workflow cancellation
- workflow history tracking
- audit trails
- runtime role authorization
- task reassignment possibilities
- conditional execution paths

This allows the package to function as both a workflow engine and a business process management (BPM) system.

## Visual Workflow Evolution

The built-in visual designer allows administrators to continuously evolve workflows as organizational requirements change. Instead of hardcoding approval chains, workflows become configurable operational assets.

This dramatically reduces maintenance overhead, release cycles, developer dependency, and operational rigidity, while improving flexibility, scalability, transparency, and auditability.

## Workflow Architecture

The package uses a graph-based runtime architecture.

### Supported node types:

- **Start Node**: Exactly one outgoing edge.
- **Step Node**: Exactly one outgoing edge. Represents an executable business task.
- **Condition Node**: Exactly two outgoing edges (TRUE and FALSE branch). May point to another condition node or a step node.
- **End Node**: No outgoing edges.

## Workflow Runtime Design

The package separates workflow runtime into two major concepts:

### 1. Current Workflow State (`workflow_instances`)

Tracks the current active step, status, runtime state, and completion/cancellation.
**Important**: `workflow_instances.current_step_id` represents the **CURRENT ACTIVE PENDING STEP**, not the last completed step.

### 2. Workflow Execution History (`workflow_instance_steps`)

Tracks executed tasks, active tasks, comments, timestamps, audit trails, and user actions.
**Important**: A workflow task is considered **OPEN** if `completed_at IS NULL`, and **COMPLETED** if `completed_at IS NOT NULL`.

## Visual Workflow Designer

The package includes an interactive drag-and-drop workflow designer powered by LogicFlow.

- draggable nodes
- resizable nodes
- directional workflow arrows
- nested conditional trees
- multiple graph directions
- runtime graph persistence
- visual workflow editing

## Installation

Install the package via Composer:

```bash
composer require nganthoiba/laravel-dynamic-workflow
```

### Publish Package Resources

```bash
php artisan vendor:publish --provider="Workflow\Providers\WorkflowServiceProvider"
```

Or you can publish specific resources using tags:
- `workflow-config`: Package configuration
- `workflow-views`: Management views (inbox, history, designer)
- `workflow-step-views`: Runtime action views (e.g., Approve and Forward)
- `workflow-assets`: JS and CSS assets

### Run Migrations

```bash
php artisan migrate
```

## Configuration

Update `config/workflow.php`:

```php
'models' => [
    'role' => \App\Models\Role::class,
    'user' => \App\Models\User::class,
],
```

## Workflow Actions (Hooks)

Workflow actions define the executable business behavior of workflow steps. Each executable step references a `workflow_action` which determines the Blade view, Action class, and runtime behavior.

### Creating Workflow Actions

Create a class implementing `Workflow\Core\Contracts\StepActionInterface`.

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

    public function execute(
        array $data,
        Model $model,
        WorkflowInstanceStep $workflowInstanceStep
    ): void {
        $model->update([
            'status' => 'approved'
        ]);

        logger()->info("Order {$model->id} approved.");
    }
}
```

### Registering Workflow Actions

1. Register actions inside `config/workflow.php`:

```php
'workflow_actions' => [
    'approve_order' => [
        'label' => 'Approve Order',
        'view'  => 'approve_order_view',
        'action' => \App\Workflow\Actions\ApproveOrderAction::class,
    ],
],
```

2. Create the corresponding blade view at `resources/views/workflow/steps/approve_order_view.blade.php`. **Every workflow step view MUST extend the package's task layout:**

```blade
@extends('vendor.workflow.task_layout')

@section('form_actions')
    <button type="submit" name="action_result" value="reject" class="btn btn-outline-danger">
        Reject
    </button>
    <button type="submit" name="action_result" value="approve" class="btn btn-primary">
        Approve & Forward
    </button>
@endsection
```

## Reference Summary Views

When a user opens a task in their inbox, they need to see the details of the business object (e.g., a Purchase Order) being processed. This package uses a dynamic view injection system for these summaries.

### 1. Create the Summary View
Create a Blade file in your application at `resources/views/workflow/reference/`. For example, for a `PurchaseOrder` model:

`resources/views/workflow/reference/purchase_order.blade.php`:
```blade
<div class="row">
    <div class="col-md-6">
        <p><strong>Order Number:</strong> {{ $model->order_number }}</p>
        <p><strong>Date:</strong> {{ $model->created_at->format('d M Y') }}</p>
    </div>
    <div class="col-md-6">
        <p><strong>Total Amount:</strong> {{ number_format($model->total_amount, 2) }}</p>
        <p><strong>Vendor:</strong> {{ $model->vendor->name }}</p>
    </div>
</div>
```

### 2. Register the Mapping
In `config/workflow.php`, map your business model class to its specific summary view:

```php
'reference_views' => [
    \App\Models\PurchaseOrder::class => 'workflow.reference.purchase_order',
],
```

The package will now automatically inject this view into the task execution layout (`task_layout.blade.php`) whenever a workflow task related to that model is being performed.

### Convention Over Configuration (Fallback)
If you do not explicitly register a mapping in `config/workflow.php`, the package will automatically look for a view using a snake_case convention of the model's class name.

**Example:**
*   **Model Class:** `App\Models\DispatchOrder`
*   **Default View Path:** `resources/views/workflow/reference/dispatch_order.blade.php`

This allows you to quickly add support for new models simply by creating the corresponding blade file in the `workflow/reference` directory.
```

## Visual Workflow Designing

1. Access the designer at `/workflow/processes`
2. Create a new process
3. Launch the visual designer
4. Add nodes (Step, Condition, Start, End)
5. Connect nodes using directional arrows
6. Configure workflow actions, authorized roles, and conditions.

## Starting a Workflow

```php
use Workflow\Models\Process;
use Workflow\Services\WorkflowInstanceService;

$process = Process::where('code', 'ORDER_APPROVAL')->first();
$order = Order::find(1);

$service = app(WorkflowInstanceService::class);
$instance = $service->start($process, $order);
```

## Workflow Inbox

The package provides a built-in runtime task inbox at `/workflow/inbox`. Users can view pending tasks, execute steps, approve/reject requests, and track history.

## Workflow Execution Flow

1. User submits a task.
2. `WorkflowController` resolves the workflow action.
3. `StepAction::validate()` executes.
4. `StepAction::execute()` executes.
5. Workflow task is marked completed.
6. Workflow engine resolves the next node and advances dynamically.

## Role-Based Workflow Authorization

Steps support role-based execution. Only authorized users with matching roles can execute a workflow task.

## Workflow Cancellation

Supports runtime cancellation with status tracking (`running`, `completed`, `cancelled`, `rejected`), closed tasks, and audit traces with reasons.

## Custom Node Handlers

Advanced users may extend `Workflow\Core\Handlers\NodeHandler` to implement custom behavior like parallel approvals or synchronization nodes.

## Package Technology Stack

- Laravel
- PHP
- LogicFlow
- Blade
- Bootstrap
- Eloquent ORM

## Future Roadmap

- parallel workflow branches
- workflow versioning
- workflow templates
- notification engine
- websocket updates
- SLA tracking
- BPMN compatibility

## Contributing

Contributions, architecture suggestions, and feature requests are welcome.

## License

MIT License
