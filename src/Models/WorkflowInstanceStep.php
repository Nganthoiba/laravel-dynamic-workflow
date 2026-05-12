<?php

namespace Workflow\Models;

use Illuminate\Database\Eloquent\Model;

class WorkflowInstanceStep extends Model
{
    protected $fillable = [
        'workflow_instance_id',
        'process_id',
        'step_id',
        'user_id',
        'action',
        'comment',
        'entered_at',
        'completed_at'
    ];

    protected $casts = [
        'entered_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function workflowInstance()
    {
        return $this->belongsTo(
            WorkflowInstance::class
        );
    }

    public function step()
    {
        return $this->belongsTo(Step::class);
    }

    public function user()
    {
        return $this->belongsTo(config('workflow.models.user'));
    }
}
