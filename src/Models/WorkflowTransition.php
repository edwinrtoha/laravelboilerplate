<?php

namespace Edwinrtoha\Laravelboilerplate\Models;

use Edwinrtoha\Laravelboilerplate\Models\ModelStd;

class WorkflowTransition extends ModelStd
{
    public function fromState()
    {
        return $this->hasOne(WorkflowState::class, 'id', 'from_state_id');
    }

    public function toState()
    {
        return $this->hasOne(WorkflowState::class, 'id', 'to_state_id');
    }
}
