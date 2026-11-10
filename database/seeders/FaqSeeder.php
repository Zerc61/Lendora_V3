<?php

namespace Database\Seeders;

use App\Models\Faq;
use App\Models\Organization;
use Illuminate\Database\Seeder;

class FaqSeeder extends Seeder
{
    public function run(): void
    {
        $organization = Organization::where('code', 'LDR')->firstOrFail();

        $faqs = [
            ['category' => 'peminjaman', 'question' => 'Berapa lama maksimal peminjaman peralatan?', 'answer' => 'Maksimal 7 hari per pengajuan. Perpanjangan dapat diajukan satu hari sebelum jatuh tempo dan menunggu persetujuan operator.'],
            ['category' => 'peminjaman', 'question' => 'Apakah saya bisa meminjam lebih dari satu unit?', 'answer' => 'Bisa, maksimal 3 unit aktif per pengajuan. Aksesori tambahan dibatasi 3 unit.'],
            ['category' => 'pengembalian', 'question' => 'Apa yang terjadi jika saya terlambat mengembalikan?', 'answer' => 'Keterlambatan dikenakan denda Rp 10.000 per jam setelah toleransi 15 menit. Skor kepatuhan juga berkurang.'],
            ['category' => 'kerusakan', 'question' => 'Bagaimana prosedur jika peralatan rusak?', 'answer' => 'Kerusakan dicatat dalam Berita Acara Pemeriksaan (BAP). Biaya perbaikan dihitung setelah inspeksi teknisi, dengan subsidi prodi sesuai kebijakan.'],
            ['category' => 'akun', 'question' => 'Bagaimana cara memulihkan akun yang ditangguhkan?', 'answer' => 'Selesaikan pembayaran denda melalui halaman Pembayaran Denda, lalu ikuti tahapan pemulihan akses hingga otorisasi kaprodi.'],
            ['category' => 'operasional', 'question' => 'Kapan loket pengambilan buka?', 'answer' => 'Loket A buka Senin–Jumat pukul 08:30–16:00 WIB. Ambil unit maksimal 30 menit sebelum sesi dimulai.'],
        ];

        foreach ($faqs as $index => $faq) {
            Faq::updateOrCreate(
                ['organization_id' => $organization->id, 'question' => $faq['question']],
                [...$faq, 'sort_order' => $index + 1, 'is_active' => true],
            );
        }
    }
}
