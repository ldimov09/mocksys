<?php

namespace App\Http\Controllers;

use App\Helpers\LogHelper;
use App\Helpers\TransactionHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = request()->user();
        $logs = $user->logs();
        return view('dashboard', compact('user', 'logs'));
    }

    public function transfer(Request $request)
    {
        $data = $request->validate([
            'receiver_account' => 'required|exists:users,account_number',
            'amount' => 'required|numeric|min:0.01',
            'pin' => 'required',
        ]);
        try {

            $sender = $request->user();
            $receiver = User::where('account_number', $data["receiver_account"])->first();

            $transaction = TransactionHelper::logTransaction($sender->id, $receiver->id, $data['amount'], 'pending', 'transfer');

            // Verify PIN
            if (!Hash::check($data["pin"], $sender->password)) {
                $transaction->status = 'declined';
                $transaction->error = "Transfer Ʉ" . $data['amount'] . " to account failed due to invalid PIN.";
                $transaction->save();

                return back()->with("error", 'Invalid PIN (password).');
            }

            // Check balance
            if ($sender->balance < $data["amount"]) {
                $transaction->status = 'declined';
                $transaction->error = "Transfer Ʉ" . $data['amount'] . " to account failed due to insufficient sender balance of Ʉ" . $sender->balance;
                $transaction->save();

                return back()->with("error", 'Insufficient balance.');
            }

            // Check activity
            if (!$sender->status->isActive()) {
                $transaction->status = 'declined';
                $transaction->error = "Transfer Ʉ" . $data['amount'] . " to account failed due to inactive sender";
                $transaction->save();

                return back()->with("error", 'Your account is currently inactive.');
            }

            if (!$receiver->status->isActive()) {
                $transaction->status = 'declined';
                $transaction->error = "Transfer Ʉ" . $data['amount'] . " to account failed due to inactive receiver";
                $transaction->save();

                return back()->with("error", 'The receiver\'s account is currently inactive.');
            }

            // Do the transfer in a transaction
            DB::transaction(function () use ($sender, $receiver, $data, $transaction) {
                $sender->balance -= $data["amount"];
                $sender->save();

                $receiver->balance += $data["amount"];
                $receiver->save();

                $transaction->status = 'approved';
                $transaction->save();
            });

            return back()->with('success', 'Transfer completed successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            LogHelper::log('error', 'Unexpected error during transfer #' . $transaction->id ?? 'none' . ' processing: ' . $e->getMessage(), null, null);

            return back()->with("error", 'An unexpected error occured.');
        }
    }
}
