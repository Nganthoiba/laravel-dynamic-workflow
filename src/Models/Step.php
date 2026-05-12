<?php

namespace Workflow\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Step extends Model
{
    use SoftDeletes;

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'process_id',
        'node_type',
        'name',
        'code',
        'description',
        'condition_json',
        'ui_json',
        'workflow_action',
        'is_start',
        'is_end',
        'is_active',
    ];

    protected $casts = [
        'ui_json' => 'array',
        'condition_json' => 'array',
        'is_start' => 'boolean',
        'is_end' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function process()
    {
        return $this->belongsTo(Process::class);
    }

    public function outgoingEdges()
    {
        return $this->hasMany(StepTransition::class, 'from_step_id');
    }

    public function incomingEdges()
    {
        return $this->hasMany(
            StepTransition::class,
            'to_step_id'
        );
    }

    public function roles()
    {
        return $this->belongsToMany(
            config('workflow.models.role'),
            'step_role_mappings',
            'step_id',
            'role_id'
        );
    }



    public function scopeStart($q)
    {
        return $q->where('node_type', 'start');
    }
    public function scopeStep($q)
    {
        return $q->where('node_type', 'step');
    }
    public function scopeCondition($q)
    {
        return $q->where('node_type', 'condition');
    }
    public function scopeEnd($q)
    {
        return $q->where('node_type', 'end');
    }
}
