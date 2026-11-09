<?php

namespace App\Enums;

enum JenisAksara: string
{
    case Jawa = 'jawa';
    case Sunda = 'sunda';

    public function label(): string
    {
        return match ($this) {
            self::Jawa => 'Aksara Jawa',
            self::Sunda => 'Aksara Sunda',
        };
    }
}
