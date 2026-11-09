<?php

namespace App\Enums;

enum StatusPublikasi: string
{
    case Draf = 'draf';
    case Terbit = 'terbit';
    case Arsip = 'arsip';

    public function label(): string
    {
        return match ($this) {
            self::Draf => 'Draf',
            self::Terbit => 'Terbit',
            self::Arsip => 'Arsip',
        };
    }
}
