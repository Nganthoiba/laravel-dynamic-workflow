<?php

namespace Workflow\Models;

use Illuminate\Database\Eloquent\Model;
// use Illuminate\Database\Eloquent\SoftDeletes;

class StepTransition extends Model
{
    //use SoftDeletes;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'process_id',
        'from_step_id',
        'to_step_id',
        'branch_type',
        'is_default',
        'action_label',
        'is_active',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'is_active' => 'boolean'
    ];

    public function process()
    {
        return $this->belongsTo(Process::class);
    }

    public function fromStep()
    {
        return $this->belongsTo(Step::class, 'from_step_id');
    }

    public function toStep()
    {
        return $this->belongsTo(Step::class, 'to_step_id');
    }
}
