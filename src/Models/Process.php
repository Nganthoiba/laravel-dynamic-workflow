<?php

namespace Workflow\Models;

use Illuminate\Database\Eloquent\Model;

class Process extends Model
{
    protected $fillable = [
        'name',
        'code',
        'description',
        'is_active',
        'graph_json',
        'canvas_state',
    ];

    protected $casts = [
        'graph_json' => 'array',
        'canvas_state' => 'array',
        'is_active' => 'boolean',
    ];

    public function steps()
    {
        return $this->hasMany(Step::class);
    }

    public function workflowInstances()
    {
        return $this->hasMany(
            WorkflowInstance::class
        );
    }
}
