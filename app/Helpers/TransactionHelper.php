<?php

namespace App\Helpers;

use App\Models\Transaction;

class TransactionHelper
{
    /**
     * Log a transaction to the database.
     *
     * @param int $senderId
     * @param int $receiverId
     * @param float $amount
     * @param string $status
     * @param string|null $signature
     * @param string $type
     * @return Transaction
     */
    public static function logTransaction($senderId, $receiverId, $amount, $status = 'pending', $type = 'transfer')
    {
        $signature = self::generateSignature([
            'sender_id'   => $senderId,
            'receiver_id' => $receiverId,
            'amount'      => number_format((float) $amount, 2, '.', ''),
            'type'        => $type,
        ]);

        return Transaction::create([
            'sender_id'   => $senderId,
            'receiver_id' => $receiverId,
            'amount'      => $amount,
            'status'      => $status,
            'signature'   => $signature,
            'type'        => $type,
        ]);
    }

    /**
     * Generate a SHA-256 signature from the transaction data.
     *
     * @param array $data
     * @return string
     */
    protected static function generateSignature(array $data): string
    {
        ksort($data); // Ensure consistent key order
        $payload = json_encode($data);
        return hash('sha256', $payload);
    }
}
