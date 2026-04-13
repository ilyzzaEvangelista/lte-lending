<?php

namespace App\Support;

class LoanMath
{
    /**
     * Monthly payment (annuity) from principal, annual interest %, and number of months.
     */
    public static function monthlyPayment(float $principal, float $annualInterestPercent, int $months): float
    {
        if ($months < 1) {
            return 0;
        }
        $monthlyRate = ($annualInterestPercent / 100) / 12;
        if ($monthlyRate <= 0) {
            return round($principal / $months, 2);
        }

        $pow = pow(1 + $monthlyRate, $months);

        return round($principal * ($monthlyRate * $pow) / ($pow - 1), 2);
    }

    public static function totalRepayable(float $monthly, int $months): float
    {
        return round($monthly * $months, 2);
    }
}
