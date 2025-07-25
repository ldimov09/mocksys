<?php

namespace App\Helpers;

use App\Models\Transaction;

class RefundTransactionHelper
{
    public static function refundTransaction($transaction){
        $sender = $transaction->sender;
        $receiver = $transaction->receiver;

        $sender->balance += $transaction->amount;
        $receiver->balance -= $transaction->amount;
        $transaction->status = 'refunded';

        $sender->save();
        $receiver->save();
        $transaction->save();
    }
}