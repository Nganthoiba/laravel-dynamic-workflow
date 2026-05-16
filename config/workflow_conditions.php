<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Workflow Condition Fields
    |--------------------------------------------------------------------------
    |
    | This array defines all the available fields that can be used to build
    | logic conditions in the Visual Workflow Designer.
    |
    | HOW TO ADD A NEW WORKFLOW CONDITION FIELD:
    | ------------------------------------------
    | To add a new field, append an associative array to the 'fields' list below.
    |
    | Required Keys:
    | - 'key'            : (string) The unique machine-readable identifier (e.g., 'amount', 'region').
    |                      This is the exact key the engine will look for in the evaluation context.
    | - 'label'          : (string) The human-readable name displayed in the designer UI dropdown.
    | - 'type'           : (string) The input type for the UI. Supported types: 'number', 'string', 'date', 'enum'.
    | - 'operators_json' : (array)  A list of allowed operators for this field.
    |                      Available operators: '=', '!=', '>', '<', '>=', '<=', 'in', 'contains'.
    |
    | Conditional Keys (required if type is 'enum'):
    | - 'options_json'   : (array)  A list of associative arrays defining the dropdown options.
    |                      Each option must have 'value' and 'label'.
    |                      Example: [['value' => 'HQ', 'label' => 'Headquarters']]
    |
    | Example:
    | [
    |     'key' => 'department',
    |     'label' => 'Department',
    |     'type' => 'enum',
    |     'operators_json' => ['=', '!='],
    |     'options_json' => [
    |         ['value' => 'sales', 'label' => 'Sales Dept'],
    |         ['value' => 'hr', 'label' => 'Human Resources'],
    |     ]
    | ]
    |
    */

    /**
     * -----------------------------------------------------------------------------------------------------------
     * IMPORTANT NOTES: Workflow Condition Nodes
     * -----------------------------------------------------------------------------------------------------------
     * A ConditionNode is always placed after a StepNode in a workflow. For the ConditionNode to evaluate
     * logic correctly, it requires specific data (keys and values) to be available in the evaluation context.
     * 
     * Data can be provided to a ConditionNode in two ways:
     * 
     * 1. Client-Side Input (Blade Views):
     *    Data can be passed from the preceding step's Blade view as form input parameters.
     *    Example: If a step uses `resources/views/workflow/steps/approve_purchase_order.blade.php`, 
     *    the form should include an input or button with a `name` matching the 'key' defined below.
     *    A button like `<button name="action_result" value="approved">` will provide the 
     *    'action_result' key to the following ConditionNode.
     * 
     * 2. Model Attributes (Database):
     *    The ConditionNode can also evaluate fields present in the underlying Eloquent model 
     *    associated with the workflow instance.
     *    Example: If the workflow is for a `PurchaseOrder` model, any column in the `purchase_orders` 
     *    table (e.g., 'amount') can be used as a 'key' for evaluation.
     * 
     * SUMMARY:
     * Any key defined in the 'fields' array below must either be passed from the client-side 
     * form or exist as an accessible attribute on the associated database model.
     */

     /*
    @section('form_actions')
        <button type="submit" name="action_result" value="reject" class="btn btn-outline-danger px-4">
            <i class="bi bi-x-circle me-1"></i> Reject
        </button>
        <button type="submit" name="action_result" value="approve" class="btn btn-primary px-5">
            <i class="bi bi-check2-circle me-1"></i> Approve & Forward
        </button>
    @endsection
    
    */
    'fields' => [
        [
            'key' => 'action_result',
            'label' => 'Assistant Manager Approved',
            'type' => 'enum',
            'operators_json' => ['=', '!='],
            'options_json' => [
                ['value' => 'approved', 'label' => 'Approve'],
                ['value' => 'rejected', 'label' => 'Reject'],
            ],
        ],
        [
            'key' => 'action_result',
            'label' => 'Admin Approved',
            'type' => 'enum',
            'operators_json' => ['=', '!='],
            'options_json' => [
                ['value' => 'approved', 'label' => 'Approve'],
                ['value' => 'rejected', 'label' => 'Reject'],
            ],
        ],
        
        [
            'key' => 'amount',
            'label' => 'Total Amount',
            'type' => 'number',
            'operators_json' => ['=', '!=', '>', '<', '>=', '<='],
        ],
        /*
        [
            'key' => 'region',
            'label' => 'Region',
            'type' => 'enum',
            'operators_json' => ['=', '!='],
            'options_json' => [
                ['value' => 'HQ', 'label' => 'Headquarters'],
                ['value' => 'NORTH', 'label' => 'North Zone'],
                ['value' => 'SOUTH', 'label' => 'South Zone'],
            ],
        ],
        [
            'key' => 'role',
            'label' => 'User Role',
            'type' => 'enum',
            'operators_json' => ['=', '!=', 'in'],
            'options_json' => [
                ['value' => 'admin', 'label' => 'Administrator'],
                ['value' => 'manager', 'label' => 'Manager'],
                ['value' => 'supervisor', 'label' => 'Supervisor'],
            ],
        ],
        [
            'key' => 'submission_date',
            'label' => 'Submission Date',
            'type' => 'date',
            'operators_json' => ['=', '>', '<', '>=', '<='],
        ],
        [
            'key' => 'remarks',
            'label' => 'Remarks',
            'type' => 'string',
            'operators_json' => ['=', 'contains'],
        ],
        */
    ],

    'templates' => [
        // Define any predefined templates here in the future
        // e.g. 'high_value_north' => ['name' => 'High Value in North', 'condition_json' => [...]]
    ]
];
