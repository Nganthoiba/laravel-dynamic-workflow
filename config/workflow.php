<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Workflow Models Registry
    |--------------------------------------------------------------------------
    |
    | Define the allowed business models that can be bound to workflows.
    | Keys are short, friendly identifiers used in form values.
    | Values contain:
    |   - 'label': Friendly display name for the user interface dropdown.
    |   - 'class': The Fully Qualified Class Name of the model.
    |
    */
    'workflow_models' => [
        'purchase_order' => [
            'label' => 'Purchase Order',
            'class' => 'App\\Models\\PurchaseOrder',
        ],
        'dispatch' => [
            'label' => 'Dispatch',
            'class' => 'App\\Models\\Dispatch',
        ],
        'breakage_report' => [
            'label' => 'Breakage Report',
            'class' => 'App\\Models\\BreakageReport',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Workflow Events Registry
    |--------------------------------------------------------------------------
    |
    | List of model lifecycle or business events that can trigger a workflow.
    | Keys are stored in the database, and values are friendly UI labels.
    |
    */
    'workflow_events' => [
        'created'   => 'Created',
    ],

    'workflow_actions' => [

        'submit_form' => [
            'label' => 'Submit Form',
            'view' => 'submit_form',
            'action' => null
        ],

        'verify_documents' => [
            'label' => 'Verify Documents',
            'view' => 'verify_documents',
            'action' => null,
        ],

        'initiate_dispatch' => [
            'label' => 'Initiate Dispatch',
            'view' => 'initiate_dispatch',
            'action' => null,
        ],

        'initiate_purchase_order' => [
            'label' => 'Initiate Purchase Order',
            'view' => 'initiate_purchase_order',
            'action' => null,
        ],

        'initiate_shipment' => [
            'label' => 'Initiate Shipment',
            'view' => 'initiate_shipment',
            'action' => null,
        ],

        'approve_and_forward' => [
            'label' => 'Approve and Forward',
            'view' => 'approve_and_forward',
            'action' => null, // e.g. \App\Workflow\Actions\ApproveAndForwardAction::class,
        ],
    ],

    'workflow_processes' => [
        'ISSUE_PURCHASE_ORDER' => [
            'label' => 'Issue Purchase Order',
        ],
        'DISPATCH_LIQUOR' => [
            'label' => 'Dispatch Liquor',
        ],
        'BREAKAGE_REPORT' => [
            'label' => 'Breakage Report',
        ],
    ],

    // Maps business models to their specific workflow summary views
    'reference_views' => [
        // \App\Models\PurchaseOrder::class => 'workflow.reference.purchase_order',
    ],

    /**
     * Identity System Models
     * 
     * Define the models used by the host application for Users and Roles.
     * This allows the package to remain agnostic of the host's directory structure.
     */
    'models' => [
        'role' => \App\Models\Role::class,
        'user' => \App\Models\User::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Automatic Workflow Binding Config
    |--------------------------------------------------------------------------
    |
    | Configuration options for the automatic binding and trigger system.
    |
    */

    // Default model events that trigger workflows when using the Workflowable trait
    'trigger_events' => [
        'created',
    ],

    /*
    |--------------------------------------------------------------------------
    | Route Configuration
    |--------------------------------------------------------------------------
    |
    | Define configuration for the routes registered by the package.
    | - 'enabled': Whether package routes should be automatically registered.
    | - 'middleware': The middleware applied to the package routes.
    |
    */
    'routes' => [
        'enabled' => true,
        'middleware' => ['web', 'auth'],
    ],
];
