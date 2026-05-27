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

The workflow engine supports intelligent runtime routing using **Condition Nodes**. These nodes dynamically determine the next workflow path based on runtime data.

### How Conditions Work

A `ConditionNode` is always placed after a `StepNode`. For it to evaluate logic correctly, it requires specific data (keys and values) to be available in the evaluation context. Data can be provided in two ways:

1.  **Client-Side Input (Blade Views):** Data passed from the preceding step's Blade view as form input parameters. For example, a button like `<button name="action_result" value="approved">` will provide the `action_result` key to the following ConditionNode.
2.  **Model Attributes (Database):** The engine can evaluate fields directly from the Eloquent model associated with the workflow. For instance, an `amount` column in the `purchase_orders` table can be used as an evaluation key.

### Configuration

Available condition fields are defined in `config/workflow_conditions.php`. Any key used in your workflow design must either be passed from the client-side form or exist as an attribute on the associated database model.

For example, if `action_result` is used as a key in `workflow_conditions.php`, it must be passed from the client-side form as an input parameter. This is because `action_result` is not an attribute of the business model itself; it is a custom parameter passed directly from the client form to the `ConditionNode`.

> [!IMPORTANT]
> **Why `action_result` is Compulsory:**
> Even if `action_result` is not explicitly used as a condition in `workflow_conditions.php`, developers **must** ensure this parameter is sent from the client-side form (for example, through button submission like `name="action_result"`).
>
> When completing a step, the workflow engine (specifically inside `WorkflowInstanceService`) uses it to record the exact user action in the execution history:
>
> ```php
> 'action' => $context['action_result'] ?? $currentStep->workflow_action,
> ```
>
> If `action_result` is omitted, the engine falls back to the step's default registered `workflow_action` code. Passing a distinct action value (e.g., `approve`, `reject`, or `send_back`) ensures that the precise decision made by the user is recorded in the task history audit trail and allows custom step actions (like `ApproveOrderAction`) to execute conditional business logic based on that selection.

### Adding a New Condition Field

To make a new field available for conditional routing in the visual workflow designer, append its configuration array to the `fields` list within `config/workflow_conditions.php`.

#### Field Configuration Schema

| Key              | Type     | Required                   | Description                                                                                                                                                           |
| :--------------- | :------- | :------------------------- | :-------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `key`            | `string` | **Yes**                    | The unique machine-readable identifier (e.g., `amount`, `region`) that the engine looks for in the evaluation context (from client form parameters or model columns). |
| `label`          | `string` | **Yes**                    | The human-readable name displayed in the visual designer's condition builder dropdown.                                                                                |
| `type`           | `string` | **Yes**                    | The input value type for the UI. Supported types: `number`, `string`, `date`, `enum`.                                                                                 |
| `operators_json` | `array`  | **Yes**                    | List of allowed logical operators. Supported: `=`, `!=`, `>`, `<`, `>=`, `<=`, `in`, `contains`.                                                                      |
| `options_json`   | `array`  | Required if type is `enum` | An array of option objects defining the dropdown values. Each option must contain `value` and `label`.                                                                |

#### Example: Adding a Department Condition

To register a new `department` condition field, update `config/workflow_conditions.php` as follows:

```php
'fields' => [
    // ... existing condition fields

    [
        'key' => 'department',
        'label' => 'Department',
        'type' => 'enum',
        'operators_json' => ['=', '!='],
        'options_json' => [
            ['value' => 'sales', 'label' => 'Sales Dept'],
            ['value' => 'hr', 'label' => 'Human Resources'],
            ['value' => 'engineering', 'label' => 'Engineering'],
        ],
    ],
],
```

Once declared, this field will instantly populate the visual editor's condition node dropdown, enabling custom business flows (e.g. `If department == sales` → Route to Sales Director).

### Examples

- **Purchase Amount:** `If amount > 100,000` → Route to Director.
- **Action Result:** `If action_result == 'rejected'` → Route to Correction Step.
- **Region/Department:** `If region == 'HQ'` → Route to Central Admin.

Nested conditional workflows are also supported (e.g., `Amount > 100,000` → `Region == 'HQ'`), allowing for complex, enterprise-grade decision trees.

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

### Roles Table Requirements

The workflow engine requires a `roles` table to manage authorization. By default, the package includes a migration that ensures this table exists with the following fields:

- `role_name` (string, unique): Machine-readable identifier.
- `display_name` (string): Human-readable name.
- `role_description` (text, nullable): Optional description.

If you already have a `roles` table in your application, the provided migration will automatically detect and add any missing fields.

### Database Schema

The package uses several tables to store workflow definitions and runtime execution data.

#### 1. `processes`

Stores the high-level workflow definitions.

- `name`: Human-readable name (e.g., "Purchase Order Approval").
- `code`: Unique machine-readable identifier (e.g., "PO_APPROVAL").
- `graph_json`: Complete visual graph state from the designer.
- `canvas_state`: UI state of the designer canvas (zoom, offsets).

#### 2. `steps`

Stores individual nodes within a workflow.

- `id`: Primary key (String/UUID from the visual designer).
- `process_id`: Reference to the parent process.
- `node_type`: `start`, `end`, `step`, or `condition`.
- `name`: Label displayed in the UI.
- `code`: Machine-readable identifier for the step.
- `condition_json`: Logical rules for condition nodes, i.e if node_type == "condition", then this field will be used to evaluate the condition.
- `workflow_action`: Registered action hook for step execution.
- `is_start`, `is_end`: Flags for entry and exit points.

#### 3. `step_transitions`

Stores the directed edges between steps.

- `id`: Primary key (UUID from the visual designer).
- `from_step_id`, `to_step_id`: Source and destination steps.
- `branch_type`: `TRUE`, `FALSE`, or `DEFAULT`.
- `action_label`: Text displayed on the transition line.
- `is_default`: Fallback flag for conditional branching.

#### 4. `workflow_instances`

Tracks the lifecycle of an active workflow execution.

- `process_id`: Associated workflow definition.
- `reference_type`, `reference_id`: Polymorphic link to the business model (e.g., "App\Models\Order").
- `current_step_id`: The next pending executable step.
- `status`: Current state (`IN_PROGRESS`, `COMPLETED`, `CANCELLED`, `REJECTED`).
- `context`: Runtime JSON payload for conditions and actions.

#### 5. `workflow_instance_steps`

The audit trail of all executed tasks.

- `workflow_instance_id`: Parent execution instance.
- `step_id`: The step that was performed.
- `user_id`: The user who executed the task.
- `action`: The specific action taken (e.g., "approve", "reject").
- `comment`: Optional user remarks.
- `entered_at`, `completed_at`: Timestamps for task duration tracking.

#### 6. `workflow_bindings`

Maps Eloquent events to automatic workflow triggers.

- `process_id`: Workflow to launch.
- `model_type`, `event_name`: Trigger combination (e.g., "App\Models\Order" + "created").
- `priority`: Execution order if multiple bindings match.

### Run Migrations

```bash
php artisan migrate
```

## Configuration

Update `config/workflow.php`:

```php
// Define the allowed business models that can be bound to automatic triggers
'workflow_models' => [
    'purchase_order' => [
        'label' => 'Purchase Order',
        'class' => \App\Models\PurchaseOrder::class,
    ],
    'dispatch' => [
        'label' => 'Dispatch',
        'class' => \App\Models\Dispatch::class,
    ],
],

// List the supported model events for automatic triggering
'workflow_events' => [
    'created'   => 'Created',
    'updated'   => 'Updated',
    'submitted' => 'Submitted',
],

// Define the identity system models used by the host application for Users and Roles
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
            'action_result' => 'nullable|string',
        ])->validate();
    }

    public function execute(
        array $data,
        Model $model,
        WorkflowInstanceStep $workflowInstanceStep
    ): void {

        /**
         * Perform your business logic by getting the required parameters from * the $data.
        */

        if($data['action_result'] == 'reject') {
            $model->update([
                'status' => 'rejected',
                'remark' => $data['remarks'],
            ]);
        } else {
            $model->update([
                'status' => 'approved',
                'remark' => $data['remarks'],
            ]);
        }

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

2. Create the corresponding Blade view at `resources/views/workflow/steps/approve_order_view.blade.php`. **Every workflow step view MUST extend the package's task layout.** You can include any number of custom fields within the `form_fields` section, as shown below:

```blade
@extends('vendor.workflow.task_layout')

@section('form_fields')
    <div class="mb-3">
        <label for="input_field1" class="form-label">Input 1</label>
        <input type="text" name="input_field1" id="input_field1" class="form-control">
    </div>
    <div class="mb-3">
        <label for="input_field2" class="form-label">Input 2</label>
        <input type="text" name="input_field2" id="input_field2" class="form-control">
    </div>
@endsection

@section('form_actions')
    <button type="submit" name="action_result" value="rejected" class="btn btn-outline-danger">
        Reject
    </button>
    <button type="submit" name="action_result" value="approved" class="btn btn-primary">
        Approve & Forward
    </button>
@endsection
```

The parameters passed in these fields will be handled at the **action** class that implements **StepActionInterface** for example in our case is **ApproveOrderAction** as defined earlier in the Workflow Actions (Hooks) section. The variable $data contains all the form data.

## Reference Model Requirements

> [!IMPORTANT]
> **Compulsory Status Field:**
> In the `workflow_instances` database table (represented by the `WorkflowInstance` model), there is a field called `reference_type` which can store the class name of any Eloquent model (e.g., `App\Models\PurchaseOrder`) that refers to a record in your application's database.
>
> The database table associated with **any** Eloquent model used as a `reference_type` **must compulsorily have a `status` column/field**.
>
> The dynamic workflow runtime engine, the inbox/outbox task management lists, and the main task auditing layouts (`task_layout.blade.php`) heavily rely on this reference table's `status` field to correctly track, validate, and visually render color-coded badge states (such as `Approved`, `Completed`, `Rejected`, or `Cancelled`) across the application workflow lifecycle.

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

- **Model Class:** `App\Models\DispatchOrder`
- **Default View Path:** `resources/views/workflow/reference/dispatch_order.blade.php`

This allows you to quickly add support for new models simply by creating the corresponding blade file in the `workflow/reference` directory.

## Process Creation & Visual Workflow Designing

The package provides a complete administrative UI to define workflow metadata and visually design execution pipelines without editing code.

---

### 1. Managing & Creating Workflow Processes

Navigate to `/workflow/processes` to access the **Workflow Management Dashboard**. Here, all processes are listed with their metadata, unique codes, active/inactive statuses, and shortcuts to edit properties or launch the visual canvas.

#### Creating a New Process

1. Click **Create New Process** to open the creation form (`/workflow/processes/create`).
2. **Process Name**: A descriptive name for the process (e.g., `Purchase Order Approval`).
3. **Machine Code**: A unique uppercase identifier (e.g. `PURCHASE_ORDER_APPROVAL`) used programmatically to launch workflows. The form automatically converts the name to a clean snake-case slug in real-time, but you can manually override it.
4. **Description**: Define the business scope and purpose of the process.
5. **Activate Process (Toggle)**: Choose whether to activate the process. Inactive processes cannot be used to start new workflow instances.

---

### 2. Designing Workflows Graphically on the Canvas

Click the blue **Designer** button beside any process in the list to open the visual editing canvas (`/workflow/designer/{id}`).

The canvas is powered by an interactive **LogicFlow** engine that enables full graph layout customization.

#### 🧰 Toolbox (Left Sidebar Node Types)

Drag and drop any of the four standard shapes from the left sidebar toolbox onto the canvas:

- 🟢 **Start Node** (Green Ellipse): Marks the entry point of the workflow pipeline. Every process must have exactly one.
- 🔵 **Step Node** (Blue Split-Block Rectangle): Represents an executable task.
  - **Header**: Displays the Step Name (up to 40 characters).
  - **Body**: Dynamically displays the list of **Assigned Roles** (up to 45 characters).
- 🟡 **Condition Node** (Yellow Diamond): Performs logical evaluation to branch routing paths dynamically.
- 🔴 **End Node** (Red Ellipse): Marks a final termination point of the workflow process.

#### 🎮 Interactive Canvas Controls

- **Reposition Nodes**: Click and hold a node to drag it anywhere on the grid.
- **Resize Nodes**: Single-click any node to select it. A blue dotted boundary will appear with 8 square drag-handles. Click and drag any handle to scale the node boundaries.
- **Connect Nodes (Transitions)**: Hover your mouse over any node to reveal **blue anchor circles**. Click and drag from an anchor circle to any other node to draw a directional arrow (transition edge).
- **Context Hover Menu**: Hovering over a node reveals a floating tooltip with quick actions:
  - 📝 **Edit**: Opens the properties panel.
  - 🗑️ **Delete**: Instantly removes the node or connection.
- **Zoom Controls**: Use the subtract (`-`), add (`+`), or aspect-ratio buttons in the header to scale the canvas viewport.

---

### 3. Configuring Properties (Offcanvas Sidebar)

Single-click any node or transition arrow to slide-open the **Properties Offcanvas Panel** from the right.

#### 📝 Step Node Properties

- **Step Name**: The human-readable name of the step.
- **Machine Code**: The unique slug used to handle specific step transitions.
- **Description**: Description of the operational tasks expected at this step.
- **Workflow Action**: Select a registered workflow step hook (from `config/workflow.php`). This binds the step to a specific Blade form view and its backend `StepActionInterface` execution class.
- **Roles Authorization**: A checklist of role requirements. Only users carrying a checked role are authorized to view and execute this task from their Inbox.

#### 🔗 Transition Line (Edge) Properties

- **Action Label**: Text displayed directly on the transition line (e.g. `Approve`, `Reject`, `Submit`).
- **Branch Type**:
  - `Default`: Standard progressive path.
  - `True Branch` / `False Branch`: The specific branches originating from a **Condition Node**.
- **Default Fallback (Toggle)**: Mark as fallback transition route if other conditional paths are not matched.

#### ⚡ Condition Node Logic Builder

For **Condition Nodes**, click the **Edit Logic** button in the properties panel to open the modal-based **Condition Logic Builder**:

1. Click **Add Rule** to define evaluation statements.
2. Select a registered field key (from `config/workflow_conditions.php`).
3. Choose a logical operator (e.g., `is exactly`, `is not`, `>`, `<`, `is one of`, `contains`).
4. Enter the matching value (displays a dropdown for `enum` keys and standard text/number/date inputs for others).
5. Chain multiple rules using standard `AND` logic to build robust multi-variable gates.

---

### 4. Saving Your Workflow

Once your graph design is complete:

1. Ensure the layout connects all nodes logically from **Start** to **End**.
2. Click **Save Workflow** in the top-right header.
3. This persists the graph coordinates, step parameters, and transition pathways to the database (`steps` and `step_transitions` tables), making your changes live instantly for all new instances.

---

### 5. Binding Workflows to Model Events (UI)

Instead of hardcoding triggers in your application logic, the package provides an **Event Triggers** dashboard (`/workflow-bindings`) where administrators can visually map Eloquent model events to specific workflow processes.

#### Creating a New Binding

1. Navigate to `/workflow-bindings` and click **Create Binding** (or equivalent button).
2. **Target Process**: Select the active workflow process you want to trigger (e.g., `Purchase Order Approval`).
3. **Business Model**: Select the entity model (e.g., `Purchase Order`). _Models are populated from the `workflow_models` configuration in `config/workflow.php`._
4. **Trigger Event**: Select the event that should trigger the workflow (e.g., `created`, `updated`, `submitted`). _Events are populated from `workflow_events` configuration._
5. **Priority Level**: Assign a numerical priority. If multiple active bindings match the same model and event, the engine will execute the one with the highest priority first.
6. **Status**: Toggle whether the binding is active.

#### Managing Existing Bindings

- **Toggle Status**: Use the quick toggle button on the list to activate or deactivate triggers instantly.
- **Edit/Delete**: Update binding rules or remove obsolete triggers without touching the application code.

By combining the visual designer with UI-driven bindings, non-technical administrators can control the complete lifecycle—from how a workflow executes to exactly when it starts.

## Starting a Workflow

### Manual Mode

You can start a workflow manually from your application code by calling the `WorkflowInstanceService` directly:

```php
use Workflow\Models\Process;
use Workflow\Services\WorkflowInstanceService;

$process = Process::where('code', 'ORDER_APPROVAL')->first();
$order = Order::find(1);

$service = app(WorkflowInstanceService::class);
$instance = $service->start($process, $order);

// If you are inside a controller, get context from request
$context = $request->all(); // Or just an empty array if there is nothing to pass
$service->proceed($instance, $context);
```

### Automatic Binding Mode

The package also supports dynamic, database-driven automatic workflow triggers bound to Eloquent model events. When configured, workflows start automatically when the model fires events (such as `created`, `updated`, `saved`) without needing to write per-process manual code.

#### 1. Database Configuration (`workflow_bindings` table)

Database bindings are registered in the `workflow_bindings` table:

- `process_id`: The workflow process (`processes.id`) to execute.
- `model_type`: Fully-qualified class name of the target Eloquent model (e.g. `App\Models\PurchaseOrder`).
- `event_name`: The model event that fires the trigger (e.g. `created`, `updated`).
- `priority`: Conflict resolution priority. If multiple bindings match the model event, the highest priority is resolved first.
- `is_active`: Controls whether this binding is enabled.

#### 2. Model Event Integration

To listen to model events automatically, you can use the **Trait-based** approach or the **Observer-based** approach:

##### A. Trait-Based Approach

Add the `Workflow\Traits\Workflowable` trait to your Eloquent model:

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Workflow\Traits\Workflowable;

class PurchaseOrder extends Model
{
    use Workflowable;
}
```

By default, this registers listeners for the `created` and `updated` events. You can customize the events to monitor on a per-model basis by defining a static `workflowEvents` method:

```php
public static function workflowEvents(): array
{
    return ['created', 'saved', 'submitted'];
}
```

##### B. Observer-Based Approach

Alternatively, you can register the package's reusable observer `Workflow\Observers\WorkflowObserver` in your application's Service Provider:

```php
use App\Models\PurchaseOrder;
use Workflow\Observers\WorkflowObserver;

class AppServiceProvider extends ServiceProvider
{
    public function boot()
    {
        PurchaseOrder::observe(WorkflowObserver::class);
    }
}
```

#### 3. Manual / Custom Event Dispatching

If you need to fire custom, non-standard workflow triggers from application controller/logic, you can manually trigger events on your model:

```php
// If using the Workflowable trait:
$order->triggerWorkflow('submitted');

// Or using the WorkflowTriggerService directly:
app(Workflow\Services\WorkflowTriggerService::class)->trigger($order, 'submitted');
```

## Workflow Inbox

The package provides a built-in runtime task inbox at `/workflow/inbox`. Users can view pending tasks, execute steps, approve/reject requests, and track history.

## Workflow Outbox

The package also provides a built-in outbox at `/workflow/outbox`. Users can view tasks they have previously completed, along with the action taken and completion timestamp. Clicking "View" on an outbox task allows users to see the task details in a read-only mode.

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

Supports runtime cancellation with status tracking (`running`, `completed`, `cancelled`, `rejected`), closed tasks, and audit trails with reasons.

## Send Back (Automatic Reverse)

The package supports an automatic "Send Back" feature that allows a higher authority to return a task to the immediately preceding human performer for review or modification. This does **not** require drawing explicit backward arrows in the visual designer.

### How to use Send Back

To enable "Send Back" in any of your step action Blade views, simply add a button with `name="action_result"` and `value="REVERT"`:

```blade
<button type="submit" name="action_result" value="REVERT" class="btn btn-warning">
    Send Back for Correction
</button>
```

When this button is clicked:

1. The engine automatically scans the execution history of the workflow instance.
2. It identifies the most recent completed step performed by a human.
3. It closes the current task and creates a new pending task for that previous step.
4. The audit trail records the action as `SENT_BACK`.

> [!NOTE]
> If you are on the very first step of a workflow, the "Send Back" button will not work as there is no previous step to return to.

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
