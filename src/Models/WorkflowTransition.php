<?php

namespace Edwinrtoha\Laravelboilerplate\Models;

use Edwinrtoha\Laravelboilerplate\Models\ModelStd;
use Edwinrtoha\Laravelboilerplate\Models\Permission;

class WorkflowTransition extends ModelStd
{
    public function workflow()
    {
        return $this->hasOne(Workflow::class, 'id', 'workflow_id');
    }

    public function permissions()
    {
        // many-to-many via workflow_transition_permissions
        return $this->belongsToMany(
            Permission::class,
            'workflow_transition_permissions',
            'workflow_transition_id', // FK on pivot to this model
            'permission_id'           // FK on pivot to Permission
        )->withTimestamps()          // since your pivot has timestamps
        ->withPivot('uuid')         // optional if you need the pivot UUID
        ->as('link');              // optional alias for the pivot relation
    }
    public function fromState()
    {
        return $this->hasOne(WorkflowState::class, 'id', 'from_state_id');
    }

    public function toState()
    {
        return $this->hasOne(WorkflowState::class, 'id', 'to_state_id');
    }
}
