<?php

namespace App\Http\Controllers;

use App\Models\Transaction;

class TransactionController extends Controller
{
    public function index()
    {
        $transactions = Transaction::with(['sender', 'receiver'])->orderByDesc('created_at')->get();
        return view('admin.transactions.index', compact('transactions'));
    }
}
