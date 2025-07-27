<?php

namespace App\Helpers;

use App\Models\Transaction;
use Exception;

class RefundTransactionHelper
{
    public static function refundTransaction($transaction)
    {
        try {
            $sender = $transaction->sender;
            $receiver = $transaction->receiver;

            if(!$transaction){
                throw new Exception('Transaction does not exist!');
            }
            if(!$sender){
                throw new Exception('Sender does not exist!');
            }
            if(!$receiver){
                throw new Exception('Receiver does not exist!');
            }

            $sender->balance += $transaction->amount;
            $receiver->balance -= $transaction->amount;
            $transaction->status = 'refunded';

            $sender->save();
            $receiver->save();
            $transaction->save();

            return [
                'success' => true,
                'data' => null,
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'data' => $e->getMessage(),
            ];
        }
    }
}
