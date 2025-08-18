<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Helpers\LogHelper;
use App\Helpers\RefundTransactionHelper;
use App\Helpers\TransactionHelper;
use App\Models\Transaction;
use App\Models\FiscalRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Repositories\ItemRepository;
use App\Services\NonceService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class FiscalRecordController extends Controller
{
    public function __construct(
        private NonceService $nonceService,
        private ItemRepository $itemRepository
    ) {}

    public function process(Request $request)
    {
        try {
            $validated = $request->validate([
                'transaction_id' => 'nullable|integer|exists:transactions,id',
                'total' => 'required|numeric|min:0.01',
                'fiscal_key' => 'required|string',
                'items' => 'required|json', // JSON array: [{ "id": 1, "quantity": 2 }, ...]
                'payment' => 'required|string|in:card,cash',
                'company_id' => 'required|integer|exists:companies,id',
                'cash_register' => 'required|integer',
                'paid' => 'nullable|numeric|min:0.01|required_if:payment,cash',
                'nonce' => 'required|string',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'short_error' => 'Invalid input data.',
                'error' => $e->errors(),
            ], 400);
        }

        Log::info("BEGIN FISCALIZATION", []);
        DB::beginTransaction();

        try {
            // Step 0. Validate nonce
            if (!$this->nonceService->validate($validated['nonce'], "fiscalization")) {
                return response()->json([
                    'success' => false,
                    'short_error' => 'Invalid nonce.',
                    'error' => 'Invalid or expired nonce.'
                ], 401);
            }


            // Step 1: Get user from device
            $device = $request->get('device');
            if (!$device || !isset($device->user_id)) {
                return response()->json([
                    'success' => false,
                    'short_error' => 'Invalid device.',
                    'error' => 'Device information missing or invalid.',
                ], 400);
            }

            $user = User::find($device->user_id);
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'short_error' => 'User not found.',
                    'error' => 'The user linked to the device could not be found.',
                ], 404);
            }

            // Step 2: Get company and check ownership
            $company = Company::find($validated['company_id']);
            if (!$company || $company->account_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'short_error' => 'Company mismatch.',
                    'error' => 'The company does not belong to the authenticated user.',
                ], 403);
            }

            // Step 3: Get transaction if applicable
            $transaction = $validated['transaction_id'] ? Transaction::find($validated['transaction_id']) : null;

            // Step 4: If card payment, verify transaction has a valid nonce
            if ($validated['payment'] === 'card') {
                if (!$transaction) {
                    return response()->json([
                        'success' => false,
                        'short_error' => 'Transaction not found.',
                        'error' => 'A non-existent transaction ID was provided.',
                    ], 404);
                }
                if (empty($transaction->nonce) || strtolower($transaction->nonce) === 'none') {
                    return response()->json([
                        'success' => false,
                        'short_error' => 'Missing nonce.',
                        'error' => 'Transaction nonce is missing or invalid.',
                    ], 400);
                }
            }

            // Step 5: Parse and validate items
            $itemsArray = json_decode($validated['items'], true);
            if (!is_array($itemsArray)) {
                return response()->json([
                    'success' => false,
                    'short_error' => 'Invalid items.',
                    'error' => 'Could not parse the items list properly.',
                ], 400);
            }

            $calculatedTotal = 0;
            Log::info("ITEMS", $itemsArray);
            $groupedItems = [];

            foreach ($itemsArray as $item) {
                if (!isset($item['item_id'], $item['quantity']) || !is_numeric($item['quantity'])) {
                    return response()->json([
                        'success' => false,
                        'short_error' => 'Invalid item entry.',
                        'error' => 'Each item must include an id and numeric quantity.',
                    ], 400);
                }

                $dbItem = $this->itemRepository->findByIdForUser($item['item_id'], $user->id);
                if (!$dbItem) {
                    return response()->json([
                        'success' => false,
                        'short_error' => 'Item not found.',
                        'error' => "Item ID {$item['item_id']} does not belong to this user.",
                    ], 403);
                }

                // If already in grouped array, just add to the quantity
                if (isset($groupedItems[$dbItem->id])) {
                    $groupedItems[$dbItem->id]['quantity'] += $item['quantity'];
                } else {
                    $groupedItems[$dbItem->id] = [
                        'id' => $dbItem->id,
                        'name' => $dbItem->name,
                        'unit' => $dbItem->unit,
                        'price' => $dbItem->price,
                        'quantity' => $item['quantity'],
                    ];
                }
            }

            // Now compute totals after grouping
            foreach ($groupedItems as $gItem) {
                $calculatedTotal += $gItem['price'] * $gItem['quantity'];
            }

            $fetchedItems = array_values($groupedItems); // reindex numerically if needed

            $calculatedTotal = round($calculatedTotal, 2);
            if (abs($calculatedTotal - round($validated['total'], 2)) > 0.01) {
                return response()->json([
                    'success' => false,
                    'short_error' => 'Items total mismatch.',
                    'error' => "Items subtotal ($calculatedTotal) does not match the declared total ({$validated['total']}).",
                ], 400);
            }

            if ($validated['paid'] && round($validated['paid'], 2) < $validated['total']) {
                return response()->json([
                    'success' => false,
                    'short_error' => 'Paid amount issue.',
                    'error' => "Cash paid ({$validated['paid']}) is less than the total {$validated['total']}.",
                ], 400);
            }

            // Step 6: Payment-specific checks (same as before, but skipping for brevity)
            if ($validated['payment'] === 'card') {

                Log::info("SIGNATURES" . "   " . $transaction->generateExpectedSignature() . "  " . $transaction->signature, [$transaction]);

                if (!$transaction->verifySignature()) {
                    return response()->json([
                        'success' => false,
                        'short_error' => 'Invalid signature.',
                        'error' => 'The transaction signature is invalid.',
                    ], 400);
                }
                if ($transaction->status !== 'approved') {
                    return response()->json([
                        'success' => false,
                        'short_error' => 'Transaction not approved.',
                        'error' => 'Only approved transactions can be fiscalized.',
                    ], 403);
                }
                Log::info("DEBUG VALUES", [floatval($validated['total']), floatval($transaction->amount)]);
                if (abs($calculatedTotal - round($transaction->amount, 2)) > 0.01) {
                    return response()->json([
                        'success' => false,
                        'short_error' => 'Amount mismatch.',
                        'error' => 'Total does not match the transaction amount.',
                    ], 400);
                }
                if (FiscalRecord::where('transaction_id', $transaction->id)->where('status', 'fiscalized')->exists()) {
                    return response()->json([
                        'success' => false,
                        'short_error' => 'Duplicate fiscal record.',
                        'error' => 'Duplicate fiscal record for this transaction.',
                    ], 409);
                }
            }

            // Step 7: Verify fiscal key
            if (!$user->fiscal_key_enabled || $user->fiscal_key !== $validated['fiscal_key']) {
                return response()->json([
                    'success' => false,
                    'short_error' => 'Fiscal key issue.',
                    'error' => 'Fiscal key is incorrect or disabled.',
                ], 403);
            }

            // Step 8: Create fiscal record
            $fiscalRecord = new FiscalRecord([
                'total' => $validated['total'],
                'items' => json_encode($fetchedItems),
                'business_id' => $user->id,
                'company_id' => $company->id,
                'status' => 'fiscalized',
                'transaction_id' => $transaction->id ?? null,
                'transaction_signature' => $transaction->signature ?? null,
            ]);

            $fiscalRecord->storeSignature();
            $fiscalRecord->save();

            $map = [
                'ad' => 'PLC',
                'ead' => 'Sole PLC',
                'eood' => 'Ltd (Sole)',
                'et' => 'Sole Trader',
                'ood' => 'Ltd',
            ];

            $ppml = $this->generateReceipt(
                $company->name . " " . $map[$company->legal_form],
                $company->number,
                $company->address,
                $validated['cash_register'],
                $device->number, // shop_number from device
                $device->name,   // operator_name from device
                $groupedItems,
                round(($validated['paid'] ?? 0) - $validated['total'], 2),
                $transaction->sender->account_number ?? null,
                $transaction->signature ?? null,
                $fiscalRecord->fiscal_signature,
                $transaction->id ?? null,
                $fiscalRecord->id,
                Carbon::now()->format('d.m.Y H:i:s'),
                $validated['payment'],
                $device->id
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'fiscal_record_id' => $fiscalRecord->id,
                'fiscal_signature' => $fiscalRecord->fiscal_signature,
                'status' => $fiscalRecord->status,
                'company' => [
                    'name' => $company->name,
                    'address' => $company->address,
                    'legal_form' => $company->legal_form
                ],
                'receipt' => $ppml
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            LogHelper::log('error', 'Unexpected error during fiscal record creation: ' . $e->getMessage(), null, null);
            Log::error($e->getMessage());
            return response()->json([
                'success' => false,
                'short_error' => 'Unexpected error.',
                'error' => 'Something went wrong during fiscalization. Please try again later.',
            ], 500);
        }
    }


    private function generateReceipt(
        string $companyName,
        string $companyNumber,
        string $address,
        int $cashRegister,
        int $shopNumber,
        string $operatorName,
        array $items,
        float $change,
        string|null $accountNumber,
        string|null $signature,
        string $fiscalSignature,
        int|null $transactionId,
        int $fiscalRecordId,
        string $date,
        string $paymentMethod,
        int $deviceId
    ) {
        // Calculate printable length, accounting for <b> tag double-width behavior except spaces
        function printableLength($text)
        {
            $length = 0;
            $pattern = '/(<b>|<\/b>)/i';
            $parts = preg_split($pattern, $text, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
            $bold = false;
            foreach ($parts as $part) {
                if (strcasecmp($part, '<b>') === 0) {
                    $bold = true;
                } elseif (strcasecmp($part, '</b>') === 0) {
                    $bold = false;
                } else {
                    $chars = preg_split('//u', $part, -1, PREG_SPLIT_NO_EMPTY);
                    foreach ($chars as $char) {
                        $length += ($bold && $char !== ' ') ? 2 : 1;
                    }
                }
            }
            return $length;
        }

        $lineWidth = 48;

        function padLine($left, $right, $lineWidth)
        {
            $leftLen  = printableLength($left);
            $rightLen = printableLength($right);
            $spaces   = $lineWidth - $leftLen - $rightLen;
            if ($spaces < 0) $spaces = 0;
            return $left . str_repeat(' ', $spaces) . $right;
        }

        function centerLine($text, $lineWidth)
        {
            $textLen = printableLength($text);
            $spaces  = max(0, floor(($lineWidth - $textLen) / 2));
            return str_repeat(' ', $spaces) . $text;
        }

        $receipt = "";

        // Header
        $receipt .= "<center>{$companyName}\n";
        $receipt .= "{$address}\n";
        $receipt .= "UIC: {$companyNumber}\n";
        $receipt .= "VAT Number: FC{$companyNumber}\n";
        $receipt .= "Cash register {$cashRegister}, Store {$shopNumber}, Operator {$operatorName}\n";
        $receipt .= "</center>\n\n";

        foreach ($items as $item) {
            $name = $item['name'];
            $quantity = $item['quantity'];
            $price = $item['price'];
            $unit = $item['unit'];
            $qtyFmt  = number_format($quantity, 3, '.', '');
            $unitFmt = number_format($price,    2, '.', '');
            $right1  = "x{$qtyFmt} {$unit} @ PSU {$unitFmt}";
            $receipt .= padLine($name, $right1, $lineWidth) . "\n";

            $total   = $quantity * $price;
            $totFmt  = number_format($total, 2, '.', '');
            $receipt .= padLine('', "PSU {$totFmt}", $lineWidth) . "\n";
        }

        $receipt .= str_repeat('=', $lineWidth) . "\n";

        $sum = 0;
        foreach ($items as $item) {
            $sum += $item['quantity'] * $item['price'];
        }
        $sumFmt = number_format($sum, 2, '.', '');
        $receipt .= padLine('<b>TOTAL:</b>', "<b>PSU {$sumFmt}</b>", $lineWidth) . "\n";
        $receipt .= str_repeat('=', $lineWidth) . "\n";

        if ($paymentMethod === 'cash') {
            $paid = $sumFmt + $change;
            $receipt .= padLine('Paid (in cash)', "PSU {$paid}", $lineWidth) . "\n";
            $changeFmt = number_format($change, 2, '.', '');
            $receipt .= padLine('Change', "PSU {$changeFmt}", $lineWidth) . "\n\n";
        } else {
            $receipt .= padLine('Paid (by card)', "PSU {$sumFmt}", $lineWidth) . "\n\n";
            $receipt .= str_repeat('*', $lineWidth) . "\n";
            $receipt .= centerLine('# MOCKSYS BANK CARD PAYMENT #', $lineWidth) . "\n";
            $receipt .= str_repeat('*', $lineWidth) . "\n";
            $receipt .= "# Entered by hand\n";

            $masked = str_repeat('*', max(0, strlen($accountNumber) - 3)) . substr($accountNumber, -3);
            $receipt .= "# Account number: {$masked}\n";
            $receipt .= "Transaction signature\n";

            $chunks = str_split($signature, 32);
            foreach ($chunks as $chunk) {
                $receipt .= $chunk . "\n";
            }
            $receipt .= "# PIN REQUIRED #\n\n";
        }

        $receipt .= "<center># THANK YOU FOR YOUR PURCHASE #\n";
        $receipt .= "# KEEP RECEIPT FOR PROVING YOUR PURCHASE #\n";
        $receipt .= "</center>\n";

        $itemCount = count($items) . ' ITEM/S';
        $receipt .= padLine($date, $itemCount, $lineWidth) . "\n";

        $receipt .= "<center><qr>" . $date . " " . ($transactionId ? "{$transactionId} - " : "") . "{$fiscalRecordId} - {$companyNumber}\n" . "</qr>SYSTEM FISCAL RECORD\n";
        $receipt .= ($transactionId ? "{$transactionId} - " : "") . "{$fiscalRecordId} - {$companyNumber}\n";
        $receipt .= strtoupper($fiscalSignature) . "\n";
        $receipt .= "</center>\n\n";

        $receipt .= "<cut>";

        return $receipt;
    }
}
