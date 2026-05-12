<?php
namespace Workflow\Services;

use Workflow\Models\Step;

class StepAuthorizationService {
    /**
     * Check if given role(s) can execute a step
     *
     * @param Step $step
     * @param int|array $roleIds
     * @return bool
     */
    public function canExecuteStep(Step $step, $roleIds): bool
    {
        // Normalize to array
        $roleIds = is_array($roleIds) ? $roleIds : [$roleIds];

        // If no roles assigned to step → allow all (configurable rule)
        if ($step->roles->isEmpty()) {
            return true;
        }

        return $step->roles
            ->pluck('id')
            ->intersect($roleIds)
            ->isNotEmpty();
    }
}
