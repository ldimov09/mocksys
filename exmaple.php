<?php
// POS Printer Markup Language (PPML) receipt generator

$companyName = "МАГАЗИН ЕАД";
$companyNumber = 255209453;
$address = "ул. Добруджа №55, Басарбово";
$cashRegister = 2;
$shopNumber = 12;
$operatorName = 'sysoperator';
$items = [[
    'name' => 'Laser pointer',
    'quantity' => 1,
    'price' => 1555.99
]];
$change = 0.01;
$accountNumber = '12345678';
$signature = '8b837711913f34198219080fc97e8e2c17fb97b74c7a79a';
$fiscalSignature = 'c310fd74299ac4e093852c88a32397725a8508e101ce7b4a762de8b6d7519b2c';
$transactionId = 123;
$fiscalRecordId = 422;
$date = '27.05.2025 09:45:25';

// Calculate printable length, accounting for <b> tag double-width behavior
function printableLength($text) {
    $length = 0;
    $pattern = '/(<b>|<\/b>)/i';
    $parts = preg_split($pattern, $text, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
    $bold = false;
    foreach ($parts as $part) {
        if (strcasecmp($part, '<b>') === 0) {
            $bold = true;
        } elseif (strcasecmp($part, '</b>') === 0) {
            $bold = false;
        } elseif (preg_match($pattern, $part)) {
            // skip other tags if any
        } else {
            $len = mb_strlen($part);
            $length += $bold ? $len * 2 : $len;
        }
    }
    return $length;
}

$lineWidth = 49;

// Left/right pad to fit within $lineWidth
function padLine($left, $right) {
    global $lineWidth;
    $leftLen  = printableLength($left);
    $rightLen = printableLength($right);
    $spaces   = $lineWidth - $leftLen - $rightLen;
    if ($spaces < 0) $spaces = 0;
    return $left . str_repeat(' ', $spaces) . $right;
}

// Center text within $lineWidth
function centerLine($text) {
    global $lineWidth;
    $textLen = printableLength($text);
    $spaces  = max(0, floor(($lineWidth - $textLen) / 2));
    return str_repeat(' ', $spaces) . $text;
}

// Build the receipt
$receipt = "";

// Header
$receipt .= "<center>{$companyName}\n";
$receipt .= "{$address}\n";
$receipt .= "ЕИК: {$companyNumber}\n";
$receipt .= "НОМЕР ЗДДС: PT{$companyNumber}\n";
$receipt .= "Каса {$cashRegister}, Магазин {$shopNumber}, Оператор {$operatorName}\n";
$receipt .= "</center>\n\n";

// Line items
foreach ($items as $item) {
    list($name, $quantity, $price) = $item;
    $qtyFmt   = number_format($quantity, 3, '.', '');
    $unitFmt  = number_format($price,    2, '.', '');
    $right1   = "x{$qtyFmt} @ {$unitFmt} PSU";
    $receipt .= padLine($name, $right1) . "\n";

    $total    = $quantity * $price;
    $totFmt   = number_format($total, 2, '.', '');
    $receipt .= padLine('', "{$totFmt} PSU") . "\n";
}

// Separator
$receipt .= str_repeat('=', $lineWidth) . "\n";

// Calculate sum
$sum = 0;
foreach ($items as $item) {
    $sum += $item[1] * $item[2];
}
$sumFmt = number_format($sum, 2, '.', '');

// Total sum line (bold)
$receipt .= padLine('<b>ОБЩА СУМА:</b>', "<b>{$sumFmt} PSU</b>") . "\n";

// Separator
$receipt .= str_repeat('=', $lineWidth) . "\n";

// Payment section
if ($paymentMethod === 'cash') {
    $receipt .= padLine('Платено (в брой)', ($sumFmt + $change)." PSU") . "\n";
    $changeFmt = number_format($change, 2, '.', '');
    $receipt .= padLine('Ресто', "{$changeFmt} PSU") . "\n\n";
} else {
    // Card transaction block
    $receipt .= str_repeat('*', $lineWidth) . "\n";
    $receipt .= centerLine('# MOCKSYS BANK CARD PAYMENT #') . "\n";
    $receipt .= str_repeat('*', $lineWidth) . "\n";
    $receipt .= "# Entered by hand\n";

    // Mask account number leaving last 3 digits
    $masked = str_repeat('*', max(0, strlen($accountNumber) - 3)) . substr($accountNumber, -3);
    $receipt .= "# Account number: {$masked}\n";

    $receipt .= "Подпис транзакция\n";
    $receipt .= "Transaction signature\n";

    // Split signature into 32-char chunks
    $chunks = str_split($signature, 32);
    foreach ($chunks as $chunk) {
        $receipt .= $chunk . "\n";
    }
    $receipt .= "# PIN REQUIRED #  # ИЗИСКВА СЕ ПИН #\n\n";
}

// Thank-you footer
$receipt .= "<center># БЛАГОДАРИМ ВИ ЗА ПОКУПКАТА #\n";
$receipt .= "# ЗАПАЗЕТЕ РАЗПИСКАТА ЗА СПРАВКА #\n";
$receipt .= "</center>\n";

// Date and item count
$itemCount = count($items) . ' АРТИКУЛА';
$receipt .= padLine($date, $itemCount) . "\n";

// Fiscal signature and IDs
$receipt .= "<center><qr>{$fiscalSignature}</qr>СИСТЕМЕН КАСОВ БОН\n";
$receipt .= "{$transactionId} - {$fiscalRecordId} - {$companyNumber}\n";
$receipt .= strtoupper($fiscalSignature) . "\n";
$receipt .= "</center>\n\n";

// Cutter command
$receipt .= "<cut>";

// Output the PPML receipt
echo $receipt;