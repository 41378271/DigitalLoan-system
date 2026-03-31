<?php
/**
 * Core Loan Math Calculator
 */

class LoanCalculator {
    /**
     * Constants
     */
    const MONTHLY_INTEREST_RATE = 3.00; // Flat 3% per month
    
    /**
     * Calculate Flat Rate Monthly Payment (EMI)
     * Microfinance usually uses Flat Rate for simplicity.
     * Formula: (Principal + (Principal * Rate * Time)) / Time
     */
    public static function calculateEMI($principal, $months) {
        $rate_decimal = self::MONTHLY_INTEREST_RATE / 100;
        $total_interest = $principal * $rate_decimal * $months;
        $total_repayable = $principal + $total_interest;
        $monthly_payment = $total_repayable / $months;
        
        return [
            'principal' => (float)$principal,
            'interest_rate_pct' => self::MONTHLY_INTEREST_RATE,
            'term_months' => (int)$months,
            'total_interest' => round($total_interest, 2),
            'total_repayable' => round($total_repayable, 2),
            'monthly_payment' => round($monthly_payment, 2)
        ];
    }
    
    /**
     * Generate an Amortization Schedule (Flat Rate)
     */
    public static function generateSchedule($principal, $months, $start_date = null) {
        if (!$start_date) {
            $start_date = date('Y-m-d');
        }
        
        $loan_details = self::calculateEMI($principal, $months);
        $total_interest = $loan_details['total_interest'];
        $monthly_payment = $loan_details['monthly_payment'];
        
        $principal_component = round($principal / $months, 2);
        // Ensure interest component perfectly matches the remaining amount to avoid rounding errors
        $interest_component = $monthly_payment - $principal_component;
        
        $schedule = [];
        $current_date = new DateTime($start_date);
        
        for ($i = 1; $i <= $months; $i++) {
            // Add exactly 1 month per iteration
            $current_date->modify('+1 month');
            
            // Adjust last instalment rounding
            $instalment_principal = $principal_component;
            $instalment_interest = $interest_component;
            $instalment_total = $monthly_payment;
            
            if ($i === $months) {
                // Settle any rounding fractions on the last month
                $accumulated_principal = $principal_component * ($months - 1);
                $accumulated_interest = $interest_component * ($months - 1);
                
                $instalment_principal = round($principal - $accumulated_principal, 2);
                $instalment_interest = round($total_interest - $accumulated_interest, 2);
                $instalment_total = $instalment_principal + $instalment_interest;
            }
            
            $schedule[] = [
                'instalment_number' => $i,
                'due_date' => $current_date->format('Y-m-d'),
                'amount_due' => $instalment_total,
                'principal_component' => $instalment_principal,
                'interest_component' => $instalment_interest
            ];
        }
        
        return $schedule;
    }
}
?>
