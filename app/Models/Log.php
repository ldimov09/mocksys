<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Log model
 * @property string $user_id
 * @property string $type
 * @property string $description
 * @property string $ip_address
 * @property string $related_user_id
 */
class Log extends Model
{
    protected $fillable = ['user_id', 'related_user_id', 'type', 'description', 'ip_address'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function related_user()
    {
        return $this->belongsTo(User::class);
    }
}
