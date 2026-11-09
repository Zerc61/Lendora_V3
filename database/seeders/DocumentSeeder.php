<?php

namespace Database\Seeders;

use App\Enums\DocumentType;
use App\Models\Document;
use App\Models\Organization;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class DocumentSeeder extends Seeder
{
    public function run(): void
    {
        $organization = Organization::where('code', 'LDR')->firstOrFail();

        $documents = [
            ['title' => 'SOP Peminjaman Peralatan', 'type' => DocumentType::Sop, 'file' => 'sop-peminjaman.txt', 'body' => "SOP Peminjaman Peralatan Lendora\n\n1. Pilih peralatan pada katalog.\n2. Isi form pengajuan dan setujui SOP.\n3. Tunggu persetujuan operator.\n4. Ambil unit di loket dengan KTM aktif.\n5. Kembalikan tepat waktu dan ikuti inspeksi.\n"],
            ['title' => 'Formulir Berita Acara Pemeriksaan', 'type' => DocumentType::Form, 'file' => 'form-bap.txt', 'body' => "Formulir BAP\n\nNomor BAP:\nTanggal insiden:\nPetugas pemeriksa:\nDeskripsi kerusakan:\nEstimasi biaya:\n"],
            ['title' => 'Panduan Perawatan Kamera', 'type' => DocumentType::Manual, 'file' => 'panduan-perawatan-kamera.txt', 'body' => "Panduan Perawatan Kamera\n\n- Bersihkan sensor sebelum dan sesudah pemakaian.\n- Simpan kamera di dry box.\n- Jangan melepas lensa di area berdebu.\n- Laporkan kerusakan ke teknisi.\n"],
        ];

        foreach ($documents as $index => $item) {
            $path = 'documents/'.$item['file'];
            Storage::disk('public')->put($path, $item['body']);

            Document::updateOrCreate(
                ['organization_id' => $organization->id, 'title' => $item['title']],
                [
                    'type' => $item['type'],
                    'path' => $path,
                    'mime_type' => 'text/plain',
                    'size' => strlen($item['body']),
                    'is_public' => true,
                    'sort_order' => $index + 1,
                ],
            );
        }
    }
}
