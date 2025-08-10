<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property number $id
 * @property string $name
 * @property string $user_id
 * @property string $short_name
 * @property number $price
 * @property number $number
 * @property string $unit
 */

class Item extends Model
{
    protected $fillable = [
        "name",
        "user_id",
        "short_name",
        "price",
        "number",
        "unit",
    ];

    public function business() {
        return $this->belongsTo(User::class, 'user_id');
    }
}