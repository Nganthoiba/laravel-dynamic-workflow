<?php

namespace Workflow\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Model: WorkflowBinding
 *
 * Purpose:
 * Represents a database-driven workflow binding that maps a process to an Eloquent model and event.
 * This enables automated workflow triggers without hardcoding process details in application logic.
 *
 * Usage:
 * WorkflowBinding::create([
 *     'process_id'   => $processId,
 *     'model_type'   => 'App\Models\Order',
 *     'event_name'   => 'created', // 'created', 'updated', etc.
 *     'trigger_type' => 'auto',
 *     'priority'     => 0,
 *     'is_active'    => true,
 * ]);
 */
class WorkflowBinding extends Model
{
    protected $table = 'workflow_bindings';

    protected $fillable = [
        'process_id',
        'model_type',
        'event_name',
        'trigger_type',
        'priority',
        'is_active',
    ];

    protected $casts = [
        'process_id' => 'integer',
        'priority' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Get the process associated with the workflow binding.
     */
    public function process()
    {
        return $this->belongsTo(Process::class);
    }
}
