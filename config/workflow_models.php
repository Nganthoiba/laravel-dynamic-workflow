<?php

/**
 * Configuration: Workflow Models Registry
 *
 * Purpose:
 * Defines the allowed business models and trigger events that can be bound to workflows.
 * Using this registry avoids exposing raw Fully Qualified Class Names (FQCN) in the browser
 * and acts as a security boundaries to validate incoming trigger requests.
 *
 * Usage:
 * Developers should add their custom models and dynamic trigger events here:
 *
 * return [
 *     'models' => [
 *         'order' => [
 *             'label' => 'Customer Order',
 *             'class' => \App\Models\Order::class,
 *         ],
 *     ],
 *     'events' => [
 *         'created' => 'Created',
 *     ],
 * ];
 */

return [

    /*
    |--------------------------------------------------------------------------
    | Allowed Models for Workflow Bindings
    |--------------------------------------------------------------------------
    |
    | Define the Eloquent models that can be bound to workflow processes.
    | Keys are short, friendly identifiers used in form values.
    | Values contain:
    |   - 'label': Friendly display name for the user interface dropdown.
    |   - 'class': The Fully Qualified Class Name of the model.
    |
    */
    'models' => [
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
    | Allowed Trigger Events
    |--------------------------------------------------------------------------
    |
    | List of model lifecycle or business events that can trigger a workflow.
    | Keys are stored in the database, and values are friendly UI labels.
    |
    */
    'events' => [
        'created'   => 'Created',
        'updated'   => 'Updated',
        'submitted' => 'Submitted',
        'approved'  => 'Approved',
        'rejected'  => 'Rejected',
        'cancelled' => 'Cancelled',
    ],
];
