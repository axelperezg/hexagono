<?php

namespace App\Enums;

/**
 * Type of study a contact message is requesting information about.
 */
enum StudyType: string
{
    case Pretest = 'pretest';
    case Posttest = 'posttest';
    case Opinion = 'opinion';
    case Otro = 'otro';

    /**
     * Human-readable label used in the contact form select and in emails.
     */
    public function label(): string
    {
        return match ($this) {
            self::Pretest => 'Estudio pre-test de campaña',
            self::Posttest => 'Estudio post-test / evaluación de impacto',
            self::Opinion => 'Investigación de opinión pública',
            self::Otro => 'Otro',
        };
    }
}
