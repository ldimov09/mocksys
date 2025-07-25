<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Transaction model
 * @property int $id
 * @property string $sender_id
 * @property string $receiver_id
 * @property number $amount
 * @property string $status
 * @property string $signature
 * @property string $type
 * @property string $error
 */
class Transaction extends Model
{
    protected $fillable = [
        'sender_id',
        'receiver_id',
        'amount',
        'status',
        'signature',
        'type'
    ];

    /**
     * Regenerate the expected signature from the current data.
     */
    public function generateExpectedSignature(): string
    {
        $data = [
            'sender_id'   => $this->sender_id,
            'receiver_id' => $this->receiver_id,
            'amount'      => number_format((float) $this->amount, 2, '.', ''),
            'type'        => $this->type,
        ];

        ksort($data);
        return hash('sha256', json_encode($data));
    }

    /**
     * Verify if the stored signature matches the actual data.
     */
    public function verifySignature(): bool
    {
        return hash_equals($this->signature, $this->generateExpectedSignature());
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    public function fiscalRecord()
    {
        return $this->hasOne(FiscalRecord::class);
    }
}
