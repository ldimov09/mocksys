<?php

// app/Http/Controllers/Api/CardPaymentController.php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Enums\UserStatus;
use App\Helpers\LogHelper;
use App\Models\Transaction;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Helpers\TransactionHelper;
use App\Http\Controllers\Controller;
use App\Repositories\DeviceRepository;
use App\Services\NonceService;
use Illuminate\Log\Logger;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class CardPaymentController extends Controller
{
    public function __construct(
        private NonceService $nonceService,
        private DeviceRepository $deviceRepository
    ) {}

    public function process(Request $request)
    {
        Log::info("TRANSACTION INIT");
        try {
            $validated = $request->validate([
                'sender_card_number' => 'required|string|exists:users,card_number',
                "merchant_id" => "string|exists:companies,id",
                'amount' => 'required|numeric|min:0.01',
                'transaction_key' => 'required|string',
                'sender_pin' => 'required|string',
                'nonce' => "required|string",
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'short_error' => __('t.transaction.invalid_input'),
                'error' => $e->errors(),
            ], 400);
        }

        DB::beginTransaction();

        try {
            Log::info("TRANSACTION VALIDATION ".$validated["merchant_id"]." before: nonce");
            if(!$this->nonceService->validate($validated['nonce'], "transaction")){
                return response()->json([
                    'success' => false,
                    'short_error' => __('t.transaction.invalid_nonce'),
                    'error' => __('t.transaction.invalid_or_expired_nonce')
                ], 401);
            }
            
            $device = $request->get("device");

            Log::info("TRANSACTION VALIDATION ".$validated["merchant_id"]." before: device", [
                "device" => $device,
            ]);
            if(!$this->deviceRepository->doesBelongToCompany($device, $validated["merchant_id"])){
                return response()->json([
                    'success' => false,
                    'short_error' => __('t.transaction.device_error'),
                    'error' => __('t.transaction.device_mismatch'),
                ], 401);
            }

            Log::info("TRANSACTION VALIDATION ".$validated["merchant_id"]." before: getting users");
            $sender = User::where('card_number', $validated['sender_card_number'])->first();
            $receiver = User::where('company_id', $validated['merchant_id'])->first();

            // Save the transaction as pending
            $transaction = TransactionHelper::logTransaction($sender->id, $receiver->id, $validated['amount'], 'pending', 'card_payment');

            $transaction->nonce = $validated["nonce"];

            Log::info("TRANSACTION VALIDATION ".$validated["merchant_id"]." before: keys");
            // Check if receiver has a matching transaction key and it's enabled
            if (
                $receiver->transaction_key !== $validated['transaction_key'] ||
                !$receiver->transaction_key_enabled
            ) {
                $transaction->status = 'declined';
                $transaction->error =  __('t.transaction.invalid_transaction_key');
                $transaction->save();
                DB::commit();
                return response()->json([
                    'success' => false,
                    'short_error' => __('t.transaction.key_issue'),
                    'error' =>  __('t.transaction.invalid_transaction_key'),
                ], 403);
            }

            Log::info("TRANSACTION VALIDATION ".$validated["merchant_id"]." before: active check");
            // Check if receiver or sender is inactive
            if (!$receiver->status->isActive()) {
                $transaction->status = 'declined';
                $transaction->error = __('t.transaction.receiver_inactive');
                $transaction->save();
                DB::commit();
                return response()->json([
                    'success' => false,
                    'short_error' => __('t.transaction.user_inactive'),
                    'error' => __('t.transaction.receiver_inactive')
                ], 403);
            }

            if (!$sender->status->isActive()) {
                $transaction->status = 'declined';
                $transaction->error = __('t.transaction.sender_inactive');
                $transaction->save();
                DB::commit();
                return response()->json([
                    'success' => false,
                    'short_error' => __('t.transaction.inactive_user'),
                    'error' => __('t.transaction.sender_inactive')
                ], 403);
            }

            Log::info("TRANSACTION VALIDATION ".$validated["merchant_id"]." before: pin");
            // Check sender’s PIN
            if (!Hash::check($validated['sender_pin'], $sender->password)) {
                $transaction->status = 'declined';
                $transaction->error = __('t.transaction.invalid_pin');
                $transaction->save();
                DB::commit();
                return response()->json([
                    'success' => false,
                    'short_error' => __('t.transaction.invalid_pin'),
                    'error' => __('t.transaction.invalid_sender_pin')
                ], 403);
            }

            Log::info("TRANSACTION VALIDATION ".$validated["merchant_id"]." before: amount");
            // Check if sender has sufficient funds
            if ($sender->balance < $validated['amount']) {
                $transaction->status = 'declined';
                $transaction->error = __('t.transaction.insufficient_funds');
                $transaction->save();
                DB::commit();
                return response()->json([
                    'success' => false,
                    'short_error' => __('t.transaction.insufficient_funds'),
                    'error' => __('t.transaction.balance_issue'),
                ], 403);
            }

            Log::info("TRANSACTION EXECUTION ".$validated["merchant_id"]." before: transfer", [$transaction]);
            // Perform the actual transfer
            $sender->balance -= $validated['amount'];
            $receiver->balance += $validated['amount'];
            $sender->save();
            $receiver->save();

            // Approve and sign the transaction
            $transaction->status = 'approved';
            $transaction->signature = $transaction->generateExpectedSignature();
            $transaction->save();

            DB::commit();

            Log::info("TRANSACTION RESPONSE ".$validated["merchant_id"]." before: response");
            return response()->json([
                'success' => true,
                'transaction_id' => $transaction->id,
                'signature' => $transaction->signature
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            LogHelper::log('error', 'Unexpected error during transaction #' . ($transaction->id ?? 'none') . ' processing: ' . $e->getMessage(), null, null);
            Log::info("TRANSACTION ERROR");
            Log::error($e);
            return response()->json([
                'success' => false,
                'short_error' => __('t.transaction.unexpected_error'),
                'error' => __('t.transaction.unexpected_error_details')
            ], 500);
        }
    }
}
