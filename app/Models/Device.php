<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $user_id
 * @property string $device_name
 * @property string $device_address
 * @property string $description
 * @property int $number
 * @property string $device_key
 * @property string $status
 * @property string $last_ip
 * @property string $last_seen
 */
class Device extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'device_name',
        'device_address',
        'description',
        'number',
        'device_key',
        'status',
        'last_ip',
        'last_seen',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
