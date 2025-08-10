<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Nonce extends Model
{
    protected $fillable = [
        'token',
        'purpose',
        'expires_at',
        'used',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'used' => 'boolean',
    ];
}
