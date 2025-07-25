<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Helpers\LogHelper;
use App\Helpers\RefundTransactionHelper;
use App\Models\Transaction;
use App\Models\FiscalRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Company;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class FiscalRecordController extends Controller
{
    public function process(Request $request)
    {
        try {
            $validated = $this->validateFiscalRequest($request);
        } catch (ValidationException $e) {
            return $this->handleValidationException($e);
        }

        DB::beginTransaction();

        try {
            [$business, $company, $transaction] = $this->loadModels($validated);
            $this->validateCompanyOwnership($business, $company);

            $fiscalRecord = $this->initializeFiscalRecord($validated, $business, $company);
            $items = $this->decodeItems($validated['items'], $fiscalRecord);
            $this->validateItemsAndTotal($items, $validated, $fiscalRecord);

            if ($validated['paid'] && round($validated['paid'], 2) < round($validated['total'], 2)) {
                return $this->handleInsufficientCash($validated, $fiscalRecord);
            }

            if ($validated['payment'] === 'card') {
                $this->handleCardPayment($transaction, $validated, $fiscalRecord);
            }

            $this->validateFiscalKey($business, $validated, $transaction, $fiscalRecord);

            $fiscalRecord->status = 'fiscalized';
            $fiscalRecord->storeSignature();
            $fiscalRecord->save();

            $ppml = $this->generateReceiptData($company, $validated, $items, $transaction, $fiscalRecord);
            DB::commit();

            return $this->buildSuccessResponse($fiscalRecord, $company, $ppml);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->handleUnexpectedException($e);
        }
    }

    private function validateFiscalRequest(Request $request)
    {
        return $request->validate([
            'transaction_id' => 'nullable|integer|exists:transactions,id',
            'business_account_number' => 'required|string|exists:users,account_number',
            'total' => 'required|numeric|min:0.01',
            'fiscal_key' => 'required|string',
            'items' => 'required|json',
            'payment' => 'required|string|in:card,cash',
            'company_number' => 'required|string',
            'cash_register' => 'required|integer',
            'shop_number' => 'required|integer',
            'operator_name' => 'required|string',
            'paid' => 'nullable|numeric|min:0.01|required_if:payment,cash',
        ]);
    }

    private function handleValidationException(ValidationException $e)
    {
        return response()->json([
            'success' => false,
            'short_error' => 'Invalid input data.',
            'error' => $e->errors(),
        ], 400);
    }

    private function loadModels(array $validated)
    {
        $business = User::where('account_number', $validated['business_account_number'])->first();
        $company = Company::where('number', $validated['company_number'])->first();
        $transaction = $validated['transaction_id'] ? Transaction::find($validated['transaction_id']) : null;

        if (!$business || !$company) {
            DB::rollBack();
            throw new \RuntimeException(!$business ? 'Business not found.' : 'Company not found.');
        }

        return [$business, $company, $transaction];
    }

    private function validateCompanyOwnership($business, $company)
    {
        if ($company->account_id !== $business->id) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'short_error' => 'Company mismatch.',
                'error' => 'The company does not belong to the provided business user.',
            ], 403);
        }
    }

    private function initializeFiscalRecord($validated, $business, $company)
    {
        return new FiscalRecord([
            'total' => $validated['total'],
            'items' => $validated['items'],
            'business_id' => $business->id,
            'company_id' => $company->id,
        ]);
    }

    private function decodeItems($itemsJson, $fiscalRecord)
    {
        $items = json_decode($itemsJson, true);
        if (!is_array($items)) {
            $fiscalRecord->status = 'cancelled';
            $fiscalRecord->error = 'Could not parse the items list properly.';
            $fiscalRecord->save();
            DB::commit();
            return response()->json([
                'success' => false,
                'short_error' => 'Invalid items.',
                'error' => 'Could not parse the items list properly.',
            ], 400);
        }
        return $items;
    }

    private function validateItemsAndTotal($items, $validated, $fiscalRecord)
    {
        $calculatedTotal = 0;
        foreach ($items as $item) {
            if (!isset($item['price'], $item['quantity']) || !is_numeric($item['price']) || !is_numeric($item['quantity'])) {
                $fiscalRecord->status = 'cancelled';
                $fiscalRecord->error = 'Each item must include a numeric price and quantity.';
                $fiscalRecord->save();
                DB::commit();
                return response()->json([
                    'success' => false,
                    'short_error' => 'Invalid item entry.',
                    'error' => 'Each item must include a numeric price and quantity.',
                ], 400);
            }
            $calculatedTotal += $item['price'] * $item['quantity'];
        }

        if (abs(round($calculatedTotal, 2) - round($validated['total'], 2)) > 0.01) {
            $fiscalRecord->status = 'cancelled';
            $fiscalRecord->error = "Items subtotal does not match the declared total.";
            $fiscalRecord->save();
            DB::commit();
            return response()->json([
                'success' => false,
                'short_error' => 'Items total mismatch.',
                'error' => "Items subtotal does not match the declared total.",
            ], 400);
        }
    }

    private function handleInsufficientCash($validated, $fiscalRecord)
    {
        $fiscalRecord->status = 'cancelled';
        $fiscalRecord->error = "Cash paid is less than the total.";
        $fiscalRecord->save();
        DB::commit();
        return response()->json([
            'success' => false,
            'short_error' => 'Paid amount issue.',
            'error' => "Cash paid is less than the total.",
        ], 400);
    }

    private function handleCardPayment($transaction, $validated, $fiscalRecord)
    {
        if (!$transaction) {
            return $this->failWith($fiscalRecord, 'Transaction not found.', 404);
        }

        $fiscalRecord->transaction_id = $transaction->id;

        if (!$transaction->verifySignature()) {
            return $this->failWith($fiscalRecord, 'The transaction signature is invalid.', 400);
        }

        if ($transaction->status !== 'approved') {
            return $this->failWith($fiscalRecord, 'Only approved transactions can be fiscalized.', 403);
        }

        if (floatval($validated['total']) !== floatval($transaction->amount)) {
            return $this->failWith($fiscalRecord, 'Total does not match the transaction amount.', 400);
        }

        if (FiscalRecord::where('transaction_id', $transaction->id)->where('status', 'fiscalized')->exists()) {
            return $this->failWith($fiscalRecord, 'Duplicate fiscal record for this transaction.', 409);
        }

        $fiscalRecord->transaction_signature = $transaction->signature;
    }

    private function validateFiscalKey($business, $validated, $transaction, $fiscalRecord)
    {
        if (!$business->fiscal_key_enabled || $business->fiscal_key !== $validated['fiscal_key']) {
            $fiscalRecord->status = 'cancelled';
            $fiscalRecord->error = 'Fiscal key is incorrect or disabled.';
            $fiscalRecord->save();
            if ($transaction) RefundTransactionHelper::refundTransaction($transaction);
            DB::commit();
            return response()->json([
                'success' => false,
                'short_error' => 'Fiscal key issue.',
                'error' => 'Fiscal key is incorrect or disabled.',
            ], 403);
        }
    }

    private function failWith($fiscalRecord, $error, $status)
    {
        $fiscalRecord->status = 'cancelled';
        $fiscalRecord->error = $error;
        $fiscalRecord->save();
        DB::commit();
        return response()->json([
            'success' => false,
            'short_error' => explode('.', $error)[0] . '.',
            'error' => $error,
        ], $status);
    }

    private function generateReceiptData($company, $validated, $items, $transaction, $fiscalRecord)
    {
        $map = [
            'ad' => 'PLC',
            'ead' => 'Sole PLC',
            'eood' => 'Ltd (Sole)',
            'et' => 'Sole Trader',
            'ood' => 'Ltd',
        ];

        return $this->generateReceipt(
            $company->name . " " . $map[$company->legal_form],
            $company->number,
            $company->address,
            $validated['cash_register'],
            $validated['shop_number'],
            $validated['operator_name'],
            $items,
            round($validated['paid'], 2) - round($validated['total'], 2),
            $transaction->sender->account_number ?? null,
            $transaction->signature ?? null,
            $fiscalRecord->fiscal_signature,
            $transaction->id ?? null,
            $fiscalRecord->id,
            Carbon::now()->format('d.m.Y H:i:s'),
            $validated['payment']
        );
    }

    private function buildSuccessResponse($fiscalRecord, $company, $ppml)
    {
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
    }

    private function handleUnexpectedException($e)
    {
        LogHelper::log('error', 'Unexpected error during fiscal record creation: ' . $e->getMessage(), null, null);
        Log::error($e->getMessage());
        return response()->json([
            'success' => false,
            'short_error' => 'Unexpected error.',
            'error' => 'Something went wrong during fiscalization. Please try again later.',
        ], 500);
    }


    private function generateReceipt(
        string $companyName,
        int $companyNumber,
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
        string $paymentMethod
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
        $receipt .= "VAT Number: PT{$companyNumber}\n";
        $receipt .= "Cash register {$cashRegister}, Store {$shopNumber}, Operator {$operatorName}\n";
        $receipt .= "</center>\n\n";

        foreach ($items as $item) {
            $name = $item['name'];
            $quantity = $item['quantity'];
            $price = $item['price'];
            $qtyFmt  = number_format($quantity, 3, '.', '');
            $unitFmt = number_format($price,    2, '.', '');
            $right1  = "x{$qtyFmt} @ {$unitFmt} PSU";
            $receipt .= padLine($name, $right1, $lineWidth) . "\n";

            $total   = $quantity * $price;
            $totFmt  = number_format($total, 2, '.', '');
            $receipt .= padLine('', "{$totFmt} PSU", $lineWidth) . "\n";
        }

        $receipt .= str_repeat('=', $lineWidth) . "\n";

        $sum = 0;
        foreach ($items as $item) {
            $sum += $item['quantity'] * $item['price'];
        }
        $sumFmt = number_format($sum, 2, '.', '');
        $receipt .= padLine('<b>TOTAL:</b>', "<b>{$sumFmt} PSU</b>", $lineWidth) . "\n";
        $receipt .= str_repeat('=', $lineWidth) . "\n";

        if ($paymentMethod === 'cash') {
            $receipt .= padLine('Paid (in cash)', "{$sumFmt} PSU", $lineWidth) . "\n";
            $changeFmt = number_format($change, 2, '.', '');
            $receipt .= padLine('Change', "{$changeFmt} PSU", $lineWidth) . "\n\n";
        } else {
            $receipt .= padLine('Paid (by card)', "{$sumFmt} PSU", $lineWidth) . "\n\n";
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
