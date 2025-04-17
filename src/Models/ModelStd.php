<?php
namespace Edwinrtoha\Laravelboilerplate\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModelStd extends Model
{
    use HasFactory;
    use HasUuids;

    protected $casts = [
        'id' => 'string'
    ];

    protected $hidden = [
        'laravel_through_key'
    ];

    protected $guarded = [];
}
