<?php

namespace Workflow\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Validator;

class ConditionController extends Controller
{
    public function getFields()
    {
        $fields = config('workflow_conditions.fields', []);
        return response()->json($fields);
    }

    public function getTemplates()
    {
        $templates = config('workflow_conditions.templates', []);
        return response()->json($templates);
    }

    public function validateCondition(Request $request)
    {
        $condition = $request->input('condition_json');

        if (!is_array($condition)) {
            return response()->json(['status' => 'error', 'message' => 'Invalid condition structure'], 400);
        }

        $fieldsConfig = config('workflow_conditions.fields', []);
        $fieldsDict = [];
        foreach ($fieldsConfig as $f) {
            $fieldsDict[$f['key']] = $f;
        }

        $errors = [];
        $this->validateNode($condition, $fieldsDict, $errors);

        if (!empty($errors)) {
            return response()->json(['status' => 'error', 'errors' => $errors], 422);
        }

        return response()->json(['status' => 'success']);
    }

    protected function validateNode($node, $fieldsDict, &$errors)
    {
        if (!is_array($node)) {
            $errors[] = "Invalid condition structure.";
            return;
        }

        if (isset($node['AND'])) {
            foreach ($node['AND'] as $sub) $this->validateNode($sub, $fieldsDict, $errors);
            return;
        }

        if (isset($node['OR'])) {
            foreach ($node['OR'] as $sub) $this->validateNode($sub, $fieldsDict, $errors);
            return;
        }

        // Basic leaf node validation
        if (!isset($node['field']) || !isset($node['operator'])) {
            $errors[] = "Condition missing field or operator.";
            return;
        }

        if (!isset($fieldsDict[$node['field']])) {
            $errors[] = "Unknown field: {$node['field']}";
            return;
        }

        $fieldDef = $fieldsDict[$node['field']];
        $allowedOps = $fieldDef['operators_json'] ?? ['=', '!=', '>', '<', '>=', '<=', 'in', 'contains'];
        if (!in_array($node['operator'], $allowedOps)) {
            $errors[] = "Operator '{$node['operator']}' not allowed for field '{$node['field']}'.";
        }
    }
}
