<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\UserStatus;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * User model
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $password
 * @property string $role
 * @property string $account_number
 * @property number $balance
 * @property string $transaction_key
 * @property bool $transaction_key_enabled
 * @property string $fiscal_key
 * @property bool $fiscal_key_enabled
 * @property bool $keys_locked_by_admin
 * @property UserStatus $status
 */
class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        "role",
        "account_number",
        "balance",
        "status",
        "transaction_key",
        "transaction_key_enabled",
        "fiscal_key",
        "fiscal_key_enabled",
        "keys_locked_by_admin",
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'status' => UserStatus::class,
        ];
    }

    public function logs()
    {
        return $this->hasMany(\App\Models\Log::class);
    }

    public function transactions()
    {
        return \App\Models\Transaction::where(function ($query) {
            $query->where('sender_id', $this->id)
                ->orWhere('receiver_id', $this->id);
        });
    }

    public function generateKeys()
    {
        $this->transaction_key = Str::uuid();
        $this->fiscal_key = Str::uuid();
        $this->transaction_key_enabled = true;
        $this->fiscal_key_enabled = true;
        $this->save();
    }

    public function resetTransactionKey()
    {
        if (!$this->keys_locked_by_admin) {
            $this->transaction_key = Str::uuid();
            $this->transaction_key_enabled = true;
            $this->save();
        }
    }

    public function resetFiscalKey()
    {
        if (!$this->keys_locked_by_admin) {
            $this->fiscal_key = Str::uuid();
            $this->fiscal_key_enabled = true;
            $this->save();
        }
    }
}
