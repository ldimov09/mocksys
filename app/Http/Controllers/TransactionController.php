<?php

namespace App\Http\Controllers;

use App\Helpers\RefundTransactionHelper;
use App\Models\Transaction;

class TransactionController extends Controller
{
    public function index()
    {
        $transactions = Transaction::with(['sender', 'receiver'])->orderByDesc('created_at')->get();
        return view('admin.transactions.index', compact('transactions'));
    }

    public function refund(int $transactionId)
    {
        $transaction = Transaction::find($transactionId);
        $result = RefundTransactionHelper::refundTransaction($transaction);

        if(!$result['success']){
            return redirect()->back()->with('error', $result['data']);
        }

        return redirect()->back();
    }
}
