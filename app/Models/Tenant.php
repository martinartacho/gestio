<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tenant extends Model
{
    protected $fillable = ['slug', 'name', 'database', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
