<?php

namespace Database\Seeders;

use App\Enums\CertificationStatus;
use App\Models\User;
use App\Models\UserCertification;
use Illuminate\Database\Seeder;

class BorrowerCertificationSeeder extends Seeder
{
    public function run(): void
    {
        $borrower = User::where('email', 'ani@lendora.test')->firstOrFail();

        $certifications = [
            ['title' => 'Sertifikasi Keselamatan Studio', 'issuer' => 'Lab AV Kampus', 'certificate_number' => 'CERT-AV-2025-018', 'issued_at' => now()->subYear(), 'expires_at' => now()->addYear()],
            ['title' => 'Pelatihan Tata Cahaya Dasar', 'issuer' => 'Prodi DKV', 'certificate_number' => 'CERT-DKV-2026-004', 'issued_at' => now()->subMonths(4), 'expires_at' => null],
        ];

        foreach ($certifications as $certification) {
            UserCertification::updateOrCreate(
                ['user_id' => $borrower->id, 'title' => $certification['title']],
                [...$certification, 'status' => CertificationStatus::Valid],
            );
        }
    }
}
