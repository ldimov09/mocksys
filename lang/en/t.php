<?php

return [
    'login' => [
        'invalid_credentials' => 'Invalid login credentials.',
        'disabled_account'    => 'Access denied due to deactivated account.',
        'user_not_found'      => 'The user does not exist!',
        'unexpected_error'    => 'Unexpected error!',
        'key_locked'          => 'The key is locked by an administrator.',
        "register_complete"   => 'Full registration complete.',

        'validation' => [
            'user_name_required' => 'The user name field is required.',
            'user_name_string'   => 'The user name must be a string.',
            'password_required'  => 'The password field is required.',
            'password_string'    => 'The password must be a string.',
        ],
    ],

    'middleware' => [
        'unauthorized' => 'Unauthorized',
        'forbidden_role' => 'Forbidden – Insufficient role',
        'missing_device_key' => 'Missing device key',
        'invalid_device' => 'Invalid or disabled device.',
    ],

    'transaction' => [
        'invalid_input' => 'Invalid input data.',
        'invalid_nonce' => 'Invalid nonce.',
        'invalid_or_expired_nonce' => 'Invalid or expired nonce.',
        'device_error' => 'Device error.',
        'device_mismatch' => 'Device does not belong to the given merchant.',
        'key_issue' => 'Key issue.',
        'invalid_transaction_key' => 'Transaction key is disabled or invalid.',
        'user_inactive' => 'User inactive.',
        'receiver_inactive' => 'Receiver account is inactive.',
        'inactive_user' => 'Inactive user.',
        'sender_inactive' => 'Sender account is inactive.',
        'invalid_pin' => 'Invalid PIN.',
        'invalid_sender_pin' => 'Invalid sender PIN.',
        'insufficient_funds' => 'Insufficient funds.',
        'balance_issue' => 'Balance issue.',
        'unexpected_error' => 'Unexpected error',
        'unexpected_error_details' => 'Unexpected error occurred. Please try again later.',
    ],

    'company' => [
        'not_found' => 'No company found.',
        'already_exists' => 'Company already exists.',
        'deleted' => 'Company deleted.',
        'device_not_authorized' => 'Device not authorized for this company.',
        'legal_forms' => [
            'ad'   => 'PLC',
            'ead'  => 'Sole PLC',
            'eood' => 'Ltd (Sole)',
            'et'   => 'Sole Trader',
            'ood'  => 'Ltd',
        ],
    ],

    'device' => [
        'not_found' => 'Device not found',
        'user_not_found' => 'User not found',
    ],

    'fiscalization' => [
        'invalid_nonce' => [
            'short_error' => 'Invalid nonce.',
            'error' => 'Invalid or expired nonce.',
        ],
        'invalid_device' => [
            'short_error' => 'Invalid device.',
            'error' => 'Device information missing or invalid.',
        ],
        'user_not_found' => [
            'short_error' => 'User not found.',
            'error' => 'The user linked to the device could not be found.',
        ],
        'company_mismatch' => [
            'short_error' => 'Company mismatch.',
            'error' => 'The company does not belong to the authenticated user.',
        ],
        'transaction_not_found' => [
            'short_error' => 'Transaction not found.',
            'error' => 'A non-existent transaction ID was provided.',
        ],
        'missing_nonce' => [
            'short_error' => 'Missing nonce.',
            'error' => 'Transaction nonce is missing or invalid.',
        ],
        'invalid_items' => [
            'short_error' => 'Invalid items.',
            'error' => 'Could not parse the items list properly.',
        ],
        'invalid_item_entry' => [
            'short_error' => 'Invalid item entry.',
            'error' => 'Each item must include an id and numeric quantity.',
        ],
        'item_not_found' => [
            'short_error' => 'Item not found.',
            'error' => 'Item does not belong to this user.',
        ],
        'items_total_mismatch' => [
            'short_error' => 'Items total mismatch.',
            'error' => 'Items subtotal does not match the declared total.',
        ],
        'paid_amount_issue' => [
            'short_error' => 'Paid amount issue.',
            'error' => 'Cash paid is less than the total.',
        ],
        'invalid_signature' => [
            'short_error' => 'Invalid signature.',
            'error' => 'The transaction signature is invalid.',
        ],
        'transaction_not_approved' => [
            'short_error' => 'Transaction not approved.',
            'error' => 'Only approved transactions can be fiscalized.',
        ],
        'amount_mismatch' => [
            'short_error' => 'Amount mismatch.',
            'error' => 'Total does not match the transaction amount.',
        ],
        'duplicate_fiscal_record' => [
            'short_error' => 'Duplicate fiscal record.',
            'error' => 'Duplicate fiscal record for this transaction.',
        ],
        'fiscal_key_issue' => [
            'short_error' => 'Fiscal key issue.',
            'error' => 'Fiscal key is incorrect or disabled.',
        ],
    ],
    'receipt' => [
        'uic' => 'UIC: :number',
        'vat_number' => 'VAT Number: FC:number',
        'cash_register' => 'Cash register :register, Store :store, Operator :operator',
        'total' => 'TOTAL:',
        'paid_cash' => 'Paid (in cash)',
        'change' => 'Change',
        'paid_card' => 'Paid (by card)',
        'mock_bank_header' => '# MOCKSYS BANK CARD PAYMENT #',
        'entered_by_hand' => '# Entered by hand',
        'account_number' => '# Account number: :masked',
        'transaction_signature' => 'Transaction signature',
        'pin_required' => '# PIN REQUIRED #',
        'thank_you' => '# THANK YOU FOR YOUR PURCHASE #',
        'keep_receipt' => '# KEEP RECEIPT FOR PROVING YOUR PURCHASE #',
        'items_count' => ':count ITEM/S',
        'system_fiscal_record' => 'SYSTEM FISCAL RECORD',
    ],

    'transfer' => [
        'sender_or_receiver_missing' => 'Sender or receiver do not exist.',
        'sender_receiver_match'      => 'Sender and receiver cannot match.',
        'invalid_pin'                => 'Invalid PIN (password).',
        'invalid_pin_detail'         => 'Transfer PSU :amount to account failed due to invalid PIN.',
        'non_positive'               => 'The amount has to be a positive number!',
        'non_positive_detail'        => 'Transfer PSU :amount to account failed due to non-positive amount.',
        'insufficient_balance'       => 'Insufficient balance!',
        'insufficient_balance_detail' => 'Transfer PSU :amount to account failed due to insufficient sender balance of PSU :balance.',
        'inactive_sender'            => 'Your account is currently inactive!',
        'inactive_sender_detail'     => 'Transfer PSU :amount to account failed due to inactive sender.',
        'inactive_receiver'          => 'The receiver\'s account is currently inactive!',
        'inactive_receiver_detail'   => 'Transfer PSU :amount to account failed due to inactive receiver.',
        'success'                    => 'Transfer successful!',
        'unexpected_error'           => 'Unexpected error!',
    ],
];
