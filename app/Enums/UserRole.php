<?php

namespace App\Enums;

/**
 * Role assigned to a user, controlling access to internal admin modules
 * such as App\Livewire\Users\Index.
 */
enum UserRole: string
{
    case Admin = 'admin';
    case User = 'user';

    /**
     * Human-readable label used in the users module.
     */
    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Administrador',
            self::User => 'Usuario',
        };
    }
}
