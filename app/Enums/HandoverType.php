<?php

namespace App\Enums;

enum HandoverType: string
{
    case Pickup = 'pickup';
    case Return = 'return';

    public function label(): string
    {
        return match ($this) {
            self::Pickup => 'Serah terima ambil',
            self::Return => 'Serah terima kembali',
        };
    }
}
