<?php

namespace App\Enums;

/**
 * How urgent an opportunity is for the team.
 */
enum Priority: string
{
    case Low = 'low';
    case Medium = 'medium';
    case High = 'high';

    /**
     * Human-readable label used in the CRM modules.
     */
    public function label(): string
    {
        return match ($this) {
            self::Low => 'Prioridad baja',
            self::Medium => 'Prioridad media',
            self::High => 'Prioridad alta',
        };
    }

    /**
     * Flux badge color that goes with the priority.
     */
    public function color(): string
    {
        return match ($this) {
            self::Low => 'zinc',
            self::Medium => 'amber',
            self::High => 'red',
        };
    }
}
