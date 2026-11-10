<?php

namespace App\Enums;

enum QueueServiceType: string
{
    case Pickup = 'pickup';
    case Return = 'return';
    case Consultation = 'consultation';

    public function label(): string
    {
        return match ($this) {
            self::Pickup => 'Pengambilan alat',
            self::Return => 'Pengembalian alat',
            self::Consultation => 'Konsultasi',
        };
    }
}
