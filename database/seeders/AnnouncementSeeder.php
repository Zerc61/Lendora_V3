<?php

namespace Database\Seeders;

use App\Enums\AnnouncementCategory;
use App\Models\Announcement;
use App\Models\Organization;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AnnouncementSeeder extends Seeder
{
    public function run(): void
    {
        $organization = Organization::where('code', 'LDR')->firstOrFail();

        $items = [
            [
                'title' => 'Jadwal Operasional Ruang Operator',
                'category' => AnnouncementCategory::Operational,
                'excerpt' => 'Pengambilan dan pengembalian unit dilayani Senin–Jumat pukul 08:30–16:00 WIB.',
                'body' => 'Pengambilan dan pengembalian unit dilayani Senin–Jumat pukul 08:30–16:00 WIB di Lemari A1 Ruang Operator. Harap konfirmasi QR code saat serah terima unit.',
                'publisher_name' => 'Operator Lendora',
                'is_pinned' => true,
            ],
            [
                'title' => 'Pemeliharaan Sensor Kamera Minggu Ini',
                'category' => AnnouncementCategory::Maintenance,
                'excerpt' => 'Tiga unit kamera menjalani pembersihan sensor dan kalibrasi fokus.',
                'body' => 'Unit LDR-CAM-0005 menjalani pembersihan sensor dan kalibrasi fokus. Unit tidak dapat dipinjam sampai teknisi menyelesaikan pemeriksaan.',
                'publisher_name' => 'Teknisi Lab',
                'is_pinned' => false,
            ],
        ];

        foreach ($items as $item) {
            Announcement::updateOrCreate(
                ['organization_id' => $organization->id, 'slug' => Str::slug($item['title'])],
                [
                    ...$item,
                    'published_at' => now()->subDays($item['is_pinned'] ? 1 : 3),
                    'cta_label' => null,
                    'cta_url' => null,
                ],
            );
        }
    }
}
