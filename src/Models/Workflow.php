<?php

namespace Edwinrtoha\Laravelboilerplate\Models;

use Edwinrtoha\Laravelboilerplate\Models\ModelStd;

class Workflow extends ModelStd
{
    public function transitions()
    {
        return $this->hasMany(WorkflowTransition::class, 'workflow_id', 'id');
    }
}
