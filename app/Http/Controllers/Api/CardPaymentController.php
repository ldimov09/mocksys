<?php

// app/Http/Controllers/Api/CardPaymentController.php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Enums\UserStatus;
use App\Helpers\LogHelper;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Helpers\TransactionHelper;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class CardPaymentController extends Controller
{
    public function process(Request $request)
    {
        try {
            $validated = $request->validate([
                'sender_account_number' => 'required|string|exists:users,account_number',
                'receiver_account_number' => 'required|string|exists:users,account_number',
                'amount' => 'required|numeric|min:0.01',
                'transaction_key' => 'required|string',
                'sender_pin' => 'required|string'
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'short_error' => 'Invalid input data.',
                'error' => $e->errors(),
            ], 400);
        }

        DB::beginTransaction();

        try {
            $sender = User::where('account_number', $validated['sender_account_number'])->first();
            $receiver = User::where('account_number', $validated['receiver_account_number'])->first();

            // Save the transaction as pending
            $transaction = TransactionHelper::logTransaction($sender->id, $receiver->id, $validated['amount'], 'pending', 'card_payment');

            // Check if receiver has a matching transaction key and it's enabled
            if (
                $receiver->transaction_key !== $validated['transaction_key'] ||
                !$receiver->transaction_key_enabled
            ) {
                $transaction->status = 'declined';
                $transaction->error = 'Transaction key is disabled or invalid.';
                $transaction->save();
                DB::commit();
                return response()->json([
                    'success' => false,
                    'short_error' => 'Key issue.',
                    'error' => 'Transaction key is disabled or invalid.'
                ], 403);
            }

            // Check if receiver or sender is inactive
            if (!$receiver->status->isActive()) {
                $transaction->status = 'declined';
                $transaction->error = 'Receiver account is inactive.';
                $transaction->save();
                DB::commit();
                return response()->json([
                    'success' => false,
                    'short_error' => 'User inactive.',
                    'error' => 'Receiver account is inactive.'
                ], 403);
            }

            if (!$sender->status->isActive()) {
                $transaction->status = 'declined';
                $transaction->error = 'Sender account is inactive.';
                $transaction->save();
                DB::commit();
                return response()->json([
                    'success' => false,
                    'short_error' => 'Inactive user.',
                    'error' => 'Sender account is inactive.'
                ], 403);
            }

            // Check sender’s PIN
            if (!Hash::check($validated['sender_pin'], $sender->password)) {
                $transaction->status = 'declined';
                $transaction->error = 'Invalid sender PIN.';
                $transaction->save();
                DB::commit();
                return response()->json([
                    'success' => false,
                    'short_error' => 'Invalid PIN.',
                    'error' => 'Invalid sender PIN.'
                ], 403);
            }

            // Check if sender has sufficient funds
            if ($sender->balance < $validated['amount']) {
                $transaction->status = 'declined';
                $transaction->error = 'Insufficient funds.';
                $transaction->save();
                DB::commit();
                return response()->json([
                    'success' => false,
                    'short_error' => 'Insufficient funds.',
                    'error' => 'Balance issue.'
                ], 403);
            }

            // Perform the actual transfer
            $sender->balance -= $validated['amount'];
            $receiver->balance += $validated['amount'];
            $sender->save();
            $receiver->save();

            // Approve and sign the transaction
            $transaction->status = 'approved';
            $transaction->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'transaction_id' => $transaction->id,
                'signature' => $transaction->signature
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            LogHelper::log('error', 'Unexpected error during transaction #' . $transaction->id ?? 'none' . ' processing: ' . $e->getMessage(), null, null);

            return response()->json([
                'success' => false,
                'short_error' => 'Unexpected error',
                'error' => 'Unexpected error occured. Please try again later.'
            ], 500);
        }
    }
}
