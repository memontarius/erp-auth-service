<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $casts = [
        'type' => \App\Enums\Role::class
    ];
}
