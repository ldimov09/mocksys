<?php

namespace App\Http\Controllers\Api;

use App\Helpers\LogHelper;
use App\Helpers\TransactionHelper;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Repositories\TransactionRepository;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class TransferController extends Controller
{
    public function __construct(
        private TransactionRepository $transactionRepository
    ) {}

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

            if (!$sender || !$receiver) {
                return response()->json([
                    'success' => false,
                    'error' => __('t.transfer.sender_or_receiver_missing'),
                ], 400);
            }

            if ($sender->id == $receiver->id) {
                return response()->json([
                    'success' => false,
                    'error' => __('t.transfer.sender_receiver_match'),
                ], 400);
            }

            $transaction = TransactionHelper::logTransaction($sender->id, $receiver->id, $data['amount'], 'pending', 'transfer');

            // Verify PIN
            if (!Hash::check($data["pin"], $sender->password)) {
                $transaction->status = 'declined';
                $transaction->error = __('t.transfer.invalid_pin_detail', ['amount' => $data['amount']]);
                $transaction->save();

                return response()->json([
                    'success' => false,
                    'error' => __('t.transfer.invalid_pin'),
                ], 400);
            }

            if ($data["amount"] <= 0) {
                $transaction->status = 'declined';
                $transaction->error = __('t.transfer.non_positive_detail', ['amount' => $data['amount']]);
                $transaction->save();

                return response()->json([
                    'success' => false,
                    'error' => __('t.transfer.non_positive'),
                ], 400);
            }

            if ($sender->balance < $data["amount"]) {
                $transaction->status = 'declined';
                $transaction->error = __('t.transfer.insufficient_balance_detail', [
                    'amount' => $data['amount'],
                    'balance' => $sender->balance
                ]);
                $transaction->save();

                return response()->json([
                    'success' => false,
                    'error' => __('t.transfer.insufficient_balance'),
                ], 400);
            }

            if (!$sender->status->isActive()) {
                $transaction->status = 'declined';
                $transaction->error = __('t.transfer.inactive_sender_detail', ['amount' => $data['amount']]);
                $transaction->save();

                return response()->json([
                    'success' => false,
                    'error' => __('t.transfer.inactive_sender'),
                ], 403);
            }

            if (!$receiver->status->isActive()) {
                $transaction->status = 'declined';
                $transaction->error = __('t.transfer.inactive_receiver_detail', ['amount' => $data['amount']]);
                $transaction->save();

                return response()->json([
                    'success' => false,
                    'error' => __('t.transfer.inactive_receiver'),
                ], 400);
            }

            // Do the transfer in a DB transaction
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
                'data' => __('t.transfer.success'),
                'balance' => $sender->balance,
            ], 200);
        } catch (Exception $e) {
            DB::rollBack();
            LogHelper::log('error', 'Unexpected error during transfer #' . ($transaction->id ?? 'none') . ' processing: ' . $e->getMessage(), null, null);

            return response()->json([
                'success' => false,
                'error' => __('t.transfer.unexpected_error'),
            ], 500);
        }
    }

    public function index()
    {
        try {
            $user = request()->user();
            $result = $this->transactionRepository->getByUser($user->id);

            return response()->json([
                'success' => true,
                'data' => $result
            ], 200);
        } catch (Exception $e) {
            Log::log('laravel', "ERROR " . $e->getMessage(), []);

            return response()->json([
                'success' => false,
                'error' => __('t.transfer.unexpected_error'),
            ], 500);
        }
    }
}
