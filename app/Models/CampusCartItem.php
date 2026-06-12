<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CampusCartItem extends Model
{
    protected $table    = 'campus_cart_items';
    protected $fillable = ['cart_id', 'course_id', 'price'];
    protected $casts    = ['price' => 'decimal:2'];

    public function cart(): BelongsTo
    {
        return $this->belongsTo(CampusCart::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(CampusCourse::class);
    }
}
