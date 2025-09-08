<?php
namespace Edwinrtoha\Laravelboilerplate\Traits;

use Edwinrtoha\Laravelboilerplate\Models\WorkflowHistory;
use Edwinrtoha\Laravelboilerplate\Models\WorkflowTransition;

trait ModelHasWorkflow
{
    protected array $workflowAppends = ['next_states', 'last_state'];

    public function workflow_histories()
    {
        return $this->morphMany(WorkflowHistory::class, 'model', 'model_type', 'model_id');
    }

    public function getNextStatesAttribute()
    {
        $workflow = $this->workflow_histories()->latest()->first();
        $session_permission = auth()->user()->getAllPermissions()->pluck('id');
        if ($workflow) {
            $workflow = WorkflowTransition::with(['toState'])->where('workflow_id', $workflow->workflow_id)->where('from_state_id', $workflow->state_id);
        } else {
            $workflow = WorkflowTransition::with(['toState', 'workflow'])->whereNull('from_state_id')->whereHas('workflow', function ($query) {
                $query->where('model_type', get_class($this));
            });
        }
        $workflow = $workflow->with(['permissions'])->whereHas('permissions', function ($query) use($session_permission) {
            $query->whereIn('permissions.uuid', $session_permission);
        });
        return $workflow->get()->pluck('toState');
    }

    public function getLastStateAttribute()
    {
        $workflow = $this->workflow_histories()->latest()->first();
        if ($workflow) {
            return $workflow->state;
        }
        return null;
    }
}
