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
    'fields' => [
        [
            'key' => 'assistant_manager_approved',
            'label' => 'Assistant Manager Approved',
            'type' => 'enum',
            'operators_json' => ['=', '!='],
            'options_json' => [
                ['value' => '1', 'label' => 'Yes'],
                ['value' => '0', 'label' => 'No'],
            ],
        ],
        [
            'key' => 'admin_approved',
            'label' => 'Admin Approved',
            'type' => 'enum',
            'operators_json' => ['=', '!='],
            'options_json' => [
                ['value' => '1', 'label' => 'Yes'],
                ['value' => '0', 'label' => 'No'],
            ],
        ],
        /*
        [
            'key' => 'amount',
            'label' => 'Total Amount',
            'type' => 'number',
            'operators_json' => ['=', '!=', '>', '<', '>=', '<='],
        ],
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
