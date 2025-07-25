<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Summary of Company
 * @property int $id
 * @property int $account_id
 * @property string $manager_name
 * @property string $name
 * @property string $number
 * @property string $address
 * @property string $legal_form
 */
class Company extends Model
{
    protected $fillable = [
        'account_id',
        'manager_name',
        'name',
        'number',
        'address',
        'legal_form'
    ];

    public function account(){
        return $this->belongsTo(User::class, 'account_id');
    }
}
