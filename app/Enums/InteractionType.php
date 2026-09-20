<?php

namespace App\Enums;

/**
 * Kind of touchpoint logged against an opportunity (App\Models\Interaction).
 */
enum InteractionType: string
{
    case Call = 'call';
    case Meeting = 'meeting';
    case Email = 'email';
    case Demo = 'demo';
    case Visit = 'visit';

    /**
     * Human-readable label used in the CRM modules.
     */
    public function label(): string
    {
        return match ($this) {
            self::Call => 'Llamada',
            self::Meeting => 'Reunión',
            self::Email => 'Correo',
            self::Demo => 'Demo',
            self::Visit => 'Visita',
        };
    }

    /**
     * Flux icon name used to render the type in the interactions timeline.
     */
    public function icon(): string
    {
        return match ($this) {
            self::Call => 'phone',
            self::Meeting => 'user-group',
            self::Email => 'envelope',
            self::Demo => 'presentation-chart-line',
            self::Visit => 'map-pin',
        };
    }
}
