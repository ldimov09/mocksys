<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Fiscal record model
 * @property int $id
 * @property string $company_id
 * @property string $business_id
 * @property string $transaction_id
 * @property number $total
 * @property array $items
 * @property string $transaction_signature
 * @property string $status
 * @property string $fiscal_signature
 * @property string $error
 * @property string $store_name
 */
class FiscalRecord extends Model
{
    protected $fillable = [
        'business_id',
        'transaction_id',
        'company_id',
        'total',
        'items',
        'transaction_signature',
        'status',
        'fiscal_signature',
        'error',
        'store_name'
    ];

    protected $casts = [
        'items' => 'array'
    ];

    /**
     * Regenerate the expected signature from the current data.
     */
    public function generateExpectedSignature(): string
    {
        $data = [
            'business_id' => $this->business_id,
            'transaction_id' => $this->transaction_id ?? "",
            'company_id' => $this->company_id ?? "",
            'total' => $this->total,
            'items' => $this->items,
            'transaction_signature' => $this->transaction_signature ?? "",
            'store_name' => $this->store_name ?? "",
        ];

        ksort($data);
        return hash('sha256', json_encode($data));
    }

    /**
     * Verify if the stored signature matches the actual data.
     */
    public function verifySignature(): bool
    {
        return hash_equals($this->fiscal_signature, $this->generateExpectedSignature());
    }

    /**
     * Generate signature and set it in place. Requires a $record->save() afterwards.
     */
    public function storeSignature(): bool
    {
        $this->fiscal_signature = $this->generateExpectedSignature();
        return true;
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(User::class, 'business_id');
    }

        public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
    }
}
