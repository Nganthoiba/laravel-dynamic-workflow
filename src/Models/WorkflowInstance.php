<?php

namespace Workflow\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class WorkflowInstance extends Model
{
    protected $primaryKey = 'id';
    protected $fillable = [

        'process_id',

        'reference_type',

        'reference_id',

        'current_step_id',

        'status',

        'started_at',

        'completed_at',

        'cancelled_at'
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'context' => 'array',
    ];

    public function process()
    {
        return $this->belongsTo(Process::class);
    }

    public function steps()
    {
        return $this->hasMany(
            WorkflowInstanceStep::class
        );
    }

    public function currentStep()
    {
        return $this->belongsTo(
            Step::class,
            'current_step_id'
        );
    }

    public function reference()
    {
        return $this->morphTo();
    }

    /**
     * Get the specific blade view path for rendering the reference object's summary.
     * Looks up the config file first, then falls back to a snake_case convention.
     */
    public function getSummaryViewAttribute(): string
    {
        $viewsMap = config('workflow.reference_views', []);

        // 1. Check Configuration Mapping (Explicit and preferred)
        if (array_key_exists($this->reference_type, $viewsMap)) {
            return $viewsMap[$this->reference_type];
        }

        // 2. Fallback to convention if not defined in config
        return 'workflow.reference.' . Str::snake(class_basename($this->reference_type));
    }
}
