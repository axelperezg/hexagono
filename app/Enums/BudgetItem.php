<?php

namespace App\Enums;

/**
 * Budget item (partida) an opportunity is charged to.
 */
enum BudgetItem: string
{
    case Item36101 = '36101';
    case Item36201 = '36201';
    case NotApplicable = 'no_aplica';

    /**
     * Human-readable label used in the CRM modules.
     */
    public function label(): string
    {
        return match ($this) {
            self::Item36101 => '36101',
            self::Item36201 => '36201',
            self::NotApplicable => 'No aplica',
        };
    }
}
