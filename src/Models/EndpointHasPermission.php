<?php

namespace Edwinrtoha\Laravelboilerplate\Models;

use Edwinrtoha\Laravelboilerplate\Models\ModelStd;
use Spatie\Permission\Models\Permission;

class EndpointHasPermission extends ModelStd
{
    public function permission()
    {
        return $this->hasOne(Permission::class, 'id', 'permission_id');
    }
}
