<?php

namespace App\Helpers;

class CompanyDigitHelper 
{
    public static function calculateEIKCheckDigit(string $eik8): int
    {
        $digits = str_split($eik8);

        $coeffs1 = [1, 2, 3, 4, 5, 6, 7, 8];
        $sum1 = 0;
        for ($i = 0; $i < 8; $i++) {
            $sum1 += $digits[$i] * $coeffs1[$i];
        }

        $remainder = $sum1 % 11;
        if ($remainder < 10) {
            return $remainder;
        }

        $coeffs2 = [3, 4, 5, 6, 7, 8, 9, 10];
        $sum2 = 0;
        for ($i = 0; $i < 8; $i++) {
            $sum2 += $digits[$i] * $coeffs2[$i];
        }

        $remainder2 = $sum2 % 11;
        return $remainder2 < 10 ? $remainder2 : 0;
    }
}