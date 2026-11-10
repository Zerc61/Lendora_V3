<?php

namespace App\Enums;

enum InspectionContext: string
{
    case PostReturn = 'post_return';
    case Routine = 'routine';
    case DamageReport = 'damage_report';
    case Procurement = 'procurement';

    public function label(): string
    {
        return match ($this) {
            self::PostReturn => 'Pasca pengembalian',
            self::Routine => 'Inspeksi rutin',
            self::DamageReport => 'Laporan kerusakan',
            self::Procurement => 'Verifikasi pengadaan',
        };
    }
}
