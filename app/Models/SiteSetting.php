<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteSetting extends Model
{
    protected $fillable = ['tenant_id', 'key', 'value'];

    protected $casts = [
        'value' => 'json',
    ];
}
