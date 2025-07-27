<?php

namespace App\Traits;

use Illuminate\Support\Facades\DB;

trait BudgetCalculationTrait
{
    /**
     * Budget calculation SQL expression
     */
    private const BUDGET_CALCULATION = 'biaya_transport + biaya_penginapan + uang_harian + biaya_lainnya';

    /**
     * Calculate total budget for a travel request
     */
    protected function calculateTotalBudget($travelRequest): float
    {
        return ($travelRequest->biaya_transport ?? 0) + 
               ($travelRequest->biaya_penginapan ?? 0) + 
               ($travelRequest->uang_harian ?? 0) + 
               ($travelRequest->biaya_lainnya ?? 0);
    }

    /**
     * Get budget calculation SQL expression
     */
    protected function getBudgetCalculation(): string
    {
        return self::BUDGET_CALCULATION;
    }

    /**
     * Get budget calculation as DB::raw
     */
    protected function getBudgetCalculationRaw()
    {
        return DB::raw(self::BUDGET_CALCULATION);
    }
} 