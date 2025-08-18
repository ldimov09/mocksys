<?php

namespace App\Repositories;

use App\Models\Transaction;
use Carbon\Carbon;

class TransactionRepository
{
    public function getByUser($userId)
    {
        $result = [];
        $result['asReceiver'] = Transaction::where('receiver_id', $userId)
            ->whereBetween('created_at', [
                Carbon::now()->subDays(30),
                Carbon::now()
            ])
            ->get();
        $result['asSender'] = Transaction::where('sender_id', $userId)
            ->whereBetween('created_at', [
                Carbon::now()->subDays(30),
                Carbon::now()
            ])
            ->get();
        return $result;
    }
}