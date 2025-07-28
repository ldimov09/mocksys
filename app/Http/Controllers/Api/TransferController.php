<?php

namespace App\Http\Controllers\Api;

use App\Helpers\LogHelper;
use App\Helpers\TransactionHelper;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class TransferController extends Controller
{
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

                return response()->json([
                    'success' => false,
                    'data' => 'Invalid PIN (password).',
                ], status: 400);
            }

            if (0 >= $data["amount"]) {
                $transaction->status = 'declined';
                $transaction->error = "Transfer Ʉ" . $data['amount'] . " to account failed due to non-positive amount.";
                $transaction->save();

                return response()->json([
                    'success' => false,
                    'data' => "The amount has to be a positive number!",
                ], status: 400);
            }

            // Check balance
            if ($sender->balance < $data["amount"]) {
                $transaction->status = 'declined';
                $transaction->error = "Transfer Ʉ" . $data['amount'] . " to account failed due to insufficient sender balance of Ʉ" . $sender->balance;
                $transaction->save();

                return response()->json([
                    'success' => false,
                    'data' => "Insufficient balance!",
                ], status: 400);
            }

            // Check activity
            if (!$sender->status->isActive()) {
                $transaction->status = 'declined';
                $transaction->error = "Transfer Ʉ" . $data['amount'] . " to account failed due to inactive sender";
                $transaction->save();

                return response()->json([
                    'success' => false,
                    'data' => "Your account is currently inactive!",
                ], 403);
            }

            if (!$receiver->status->isActive()) {
                $transaction->status = 'declined';
                $transaction->error = "Transfer Ʉ" . $data['amount'] . " to account failed due to inactive receiver";
                $transaction->save();

                return response()->json([
                    'success' => false,
                    'data' => "The receiver's account is currently inactive!",
                ], 400);
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

            return response()->json([
                'success' => true,
                'data' => "Transfer successful!",
                'balance' => $sender->balance,
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            LogHelper::log('error', 'Unexpected error during transfer #' . ($transaction->id ?? 'none') . ' processing: ' . $e->getMessage(), null, null);

            return response()->json([
                'success' => false,
                'data' => "Unexpected error!",
            ], 500);
        }
    }
}
