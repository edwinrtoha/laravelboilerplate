<?php

namespace Edwinrtoha\Laravelboilerplate\Models;

use BezhanSalleh\FilamentShield\Facades\FilamentShield;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Permission\Models\Permission as SpatiePermission;

class Permission extends SpatiePermission
{
    use HasFactory;
    use HasUuids;
    protected $primaryKey = 'uuid';
    var $appends = ['resource'];
    var $permissions_resource = [];

    public function __construct()
    {
        $this->permissions_resource = FilamentShield::getAllResourcePermissions();
        $result = [];

        foreach ($this->permissions_resource as $key => $value) {
            // pecah berdasarkan " - "
            [$resource, $label] = array_map('trim', explode('-', $value, 2));

            $result[] = [
                'permission' => $key,
                'resource'   => $resource,
                'label'      => $label,
            ];
        }
        $this->permissions_resource = $result;

        parent::__construct();
    }
    public function getResourceAttribute()
    {
        return collect($this->permissions_resource)->where('permission', $this->name)->first()['resource'] ?? '';
    }
}