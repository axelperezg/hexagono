<?php

namespace App\Enums;

/**
 * Kind of phone number stored for a contact (App\Models\ContactPhone).
 */
enum PhoneType: string
{
    case Home = 'home';
    case Office = 'office';
    case Mobile = 'mobile';

    /**
     * Human-readable label used in the CRM modules.
     */
    public function label(): string
    {
        return match ($this) {
            self::Home => 'Casa',
            self::Office => 'Oficina',
            self::Mobile => 'Celular',
        };
    }
}
