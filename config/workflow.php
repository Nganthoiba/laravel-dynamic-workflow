<?php

return [

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
        'updated',
    ],
];
