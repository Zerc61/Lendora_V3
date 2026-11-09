<?php

namespace Database\Seeders;

use App\Models\Asset;
use App\Models\AssetPhoto;
use App\Models\Category;
use App\Models\Organization;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class CategoryAndAssetSeeder extends Seeder
{
    public function run(): void
    {
        $organization = Organization::where('code', 'LDR')->firstOrFail();

        $roots = [
            'kamera-mirrorless' => ['name' => 'Kamera Mirrorless', 'children' => [
                'full-frame' => 'Full-frame',
                'aps-c' => 'APS-C',
            ]],
            'lensa' => ['name' => 'Lensa', 'children' => [
                'zoom' => 'Zoom',
                'prime' => 'Prime',
            ]],
            'tripod' => ['name' => 'Tripod', 'children' => []],
            'lampu' => ['name' => 'Lampu', 'children' => []],
        ];

        $categories = [];

        foreach ($roots as $slug => $root) {
            $category = Category::updateOrCreate(
                ['organization_id' => $organization->id, 'slug' => $slug],
                ['name' => $root['name'], 'parent_id' => null],
            );

            $categories[$slug] = $category;

            foreach ($root['children'] as $childSlug => $childName) {
                $categories[$childSlug] = Category::updateOrCreate(
                    ['organization_id' => $organization->id, 'slug' => $childSlug],
                    ['name' => $childName, 'parent_id' => $category->id],
                );
            }
        }

        foreach ($this->assets() as $data) {
            $categorySlug = $data['category'];
            $image = $data['image'];
            unset($data['category'], $data['image']);

            $asset = Asset::updateOrCreate(
                ['code' => $data['code']],
                [
                    ...$data,
                    'organization_id' => $organization->id,
                    'category_id' => $categories[$categorySlug]->id,
                    'grade' => 'A',
                    'access_level' => 'open',
                    'condition_score' => 96,
                    'is_featured' => in_array($data['code'], ['LDR-CAM-0001', 'LDR-LEN-0001', 'LDR-TRI-0001', 'LDR-LMP-0001'], true),
                ],
            );

            $this->attachPhoto($asset, $image);
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function assets(): array
    {
        return [
            [
                'code' => 'LDR-CAM-0001', 'category' => 'full-frame', 'name' => 'Sony A7 III', 'brand' => 'Sony', 'model' => 'ILCE-7M3',
                'serial_number' => 'SN-A7III-001', 'status' => 'available', 'condition_status' => 'good', 'purchase_date' => '2022-03-15',
                'purchase_price' => 22000000, 'location' => 'Studio Foto', 'image' => 'kamera-sony-a7-iii.jpg',
                'description' => 'Kamera mirrorless full-frame serba bisa untuk dokumentasi kegiatan sekolah, wawancara, dan produksi video pendek. Autofokus mata manusia bekerja cepat dan stabil sehingga cocok untuk pemula maupun operator berpengalaman. Bodinya ringkas, tahan cuaca, dan memiliki dua slot kartu memori agar hasil rekaman tetap aman selama sesi panjang.',
                'specifications' => [
                    'Sensor' => '24,2 MP Full-frame Exmor R BSI',
                    'Prosesor' => 'BIONZ X',
                    'ISO' => '100–51.200 (dapat diperluas)',
                    'Video' => '4K UHD 30 fps, Full HD 120 fps',
                    'Stabilisasi' => '5-axis in-body image stabilization',
                    'Mount' => 'Sony E-mount',
                ],
            ],
            [
                'code' => 'LDR-CAM-0002', 'category' => 'full-frame', 'name' => 'Sony A7 III', 'brand' => 'Sony', 'model' => 'ILCE-7M3',
                'serial_number' => 'SN-A7III-002', 'status' => 'borrowed', 'condition_status' => 'good', 'purchase_date' => '2022-03-15',
                'purchase_price' => 22000000, 'location' => 'Studio Foto', 'image' => 'kamera-sony-a7-iii-alt.jpg',
                'description' => 'Unit kedua Sony A7 III yang disiapkan untuk kebutuhan produksi tim dengan dua kamera. Konfigurasi tombol dan menu identik dengan unit pertama sehingga operator dapat berpindah bodi tanpa menyesuaikan pengaturan. Termasuk baterai cadangan, charger, dan kartu memori 64 GB di dalam hard case.',
                'specifications' => [
                    'Sensor' => '24,2 MP Full-frame Exmor R BSI',
                    'Prosesor' => 'BIONZ X',
                    'ISO' => '100–51.200 (dapat diperluas)',
                    'Video' => '4K UHD 30 fps, Full HD 120 fps',
                    'Stabilisasi' => '5-axis in-body image stabilization',
                    'Aksesori' => '2 baterai, charger, kartu 64 GB',
                ],
            ],
            [
                'code' => 'LDR-CAM-0003', 'category' => 'full-frame', 'name' => 'Canon EOS R6', 'brand' => 'Canon', 'model' => 'EOS R6',
                'serial_number' => 'SN-R6-001', 'status' => 'available', 'condition_status' => 'good', 'purchase_date' => '2023-01-10',
                'purchase_price' => 24500000, 'location' => 'Studio Foto', 'image' => 'kamera-canon-eos-r6.jpg',
                'description' => 'Kamera full-frame dengan stabilisasi bodi yang sangat baik untuk rekaman genggam dan foto acara di ruangan minim cahaya. Sistem autofokus Dual Pixel CMOS AF II mengenali mata manusia serta hewan, sehingga memudahkan pengambilan gambar candid. Menu sentuh dan layar lipat memudahkan pengambilan sudut rendah saat kegiatan sekolah.',
                'specifications' => [
                    'Sensor' => '20,1 MP Full-frame CMOS',
                    'Prosesor' => 'DIGIC X',
                    'ISO' => '100–102.400 (dapat diperluas)',
                    'Video' => '4K UHD 60 fps, Full HD 120 fps',
                    'Stabilisasi' => '5-axis IBIS hingga 8 stop',
                    'Mount' => 'Canon RF',
                ],
            ],
            [
                'code' => 'LDR-CAM-0004', 'category' => 'full-frame', 'name' => 'Canon EOS R6', 'brand' => 'Canon', 'model' => 'EOS R6',
                'serial_number' => 'SN-R6-002', 'status' => 'reserved', 'condition_status' => 'minor_damage', 'purchase_date' => '2023-01-10',
                'purchase_price' => 24500000, 'location' => 'Studio Foto', 'image' => 'kamera-canon-eos-r6.jpg',
                'description' => 'Unit Canon EOS R6 dengan cat bodi tergores halus di bagian sudut bawah, tetapi seluruh fungsi mekanik dan elektronik berjalan normal. Kamera dijadwalkan keluar untuk kegiatan dokumentasi setelah pemeriksaan operator. Pengguna wajib memeriksa kondisi unit bersama petugas sebelum dan sesudah pemakaian.',
                'specifications' => [
                    'Sensor' => '20,1 MP Full-frame CMOS',
                    'Prosesor' => 'DIGIC X',
                    'ISO' => '100–102.400 (dapat diperluas)',
                    'Video' => '4K UHD 60 fps, Full HD 120 fps',
                    'Catatan' => 'Bodi tergores halus, fungsi normal',
                    'Mount' => 'Canon RF',
                ],
            ],
            [
                'code' => 'LDR-CAM-0005', 'category' => 'aps-c', 'name' => 'Fujifilm X-T5', 'brand' => 'Fujifilm', 'model' => 'X-T5',
                'serial_number' => 'SN-XT5-001', 'status' => 'maintenance', 'condition_status' => 'good', 'purchase_date' => '2023-06-20',
                'purchase_price' => 19500000, 'location' => 'Ruang Perawatan', 'image' => 'kamera-fujifilm-xt5.jpg',
                'description' => 'Kamera APS-C bergaya klasik dengan simulasi film bawaan yang disukai untuk fotografi editorial dan dokumentasi kreatif. Resolusi 40 MP memberi ruang crop yang lebar tanpa kehilangan detail. Unit ini sedang menjalani pembersihan sensor dan kalibrasi dial sebelum dikembalikan ke daftar pinjam.',
                'specifications' => [
                    'Sensor' => '40,2 MP APS-C X-Trans CMOS 5 HR',
                    'Prosesor' => 'X-Processor 5',
                    'ISO' => '125–12.800 (dapat diperluas)',
                    'Video' => '6.2K 30 fps, 4K 60 fps',
                    'Stabilisasi' => '5-axis IBIS hingga 7 stop',
                    'Mount' => 'Fujifilm X',
                ],
            ],
            [
                'code' => 'LDR-CAM-0006', 'category' => 'full-frame', 'name' => 'Nikon Z6 II', 'brand' => 'Nikon', 'model' => 'Z6 II',
                'serial_number' => 'SN-Z6II-001', 'status' => 'retired', 'condition_status' => 'damaged', 'purchase_date' => '2021-11-05',
                'purchase_price' => 21000000, 'location' => 'Gudang', 'image' => 'kamera-nikon-z6ii.jpg',
                'description' => 'Unit Nikon Z6 II yang sudah dikeluarkan dari daftar pinjam karena kerusakan pada port kartu memori. Kamera tetap disimpan sebagai sumber suku cadang dan bukti inventaris. Tidak tersedia untuk dipinjam sampai ada keputusan perbaikan atau penghapusan aset dari admin organisasi.',
                'specifications' => [
                    'Sensor' => '24,5 MP Full-frame CMOS',
                    'Prosesor' => 'Dual EXPEED 6',
                    'ISO' => '100–51.200 (dapat diperluas)',
                    'Video' => '4K UHD 60 fps',
                    'Status' => 'Rusak, tidak untuk dipinjam',
                    'Mount' => 'Nikon Z',
                ],
            ],
            [
                'code' => 'LDR-LEN-0001', 'category' => 'zoom', 'name' => 'Sony FE 24-70mm F2.8 GM', 'brand' => 'Sony', 'model' => 'SEL2470GM',
                'serial_number' => 'SN-2470-001', 'status' => 'available', 'condition_status' => 'good', 'purchase_date' => '2022-03-15',
                'purchase_price' => 28000000, 'location' => 'Studio Foto', 'image' => 'lensa-sony-24-70.jpg',
                'description' => 'Lensa zoom standar profesional dengan aperture konstan F2.8 untuk seluruh rentang focal length. Cocok untuk dokumentasi acara, potret setengah badan, dan video wawancara karena menghasilkan bokeh halus serta ketajaman tinggi. Lensa dilengkapi tudung, filter UV, dan tas pelindung.',
                'specifications' => [
                    'Focal length' => '24–70 mm',
                    'Aperture' => 'F2.8 konstan',
                    'Mount' => 'Sony E',
                    'Elemen' => '18 elemen dalam 13 grup',
                    'Berat' => '886 gram',
                    'Aksesori' => 'Tudung, filter UV, tas lensa',
                ],
            ],
            [
                'code' => 'LDR-LEN-0002', 'category' => 'prime', 'name' => 'Canon RF 50mm F1.8 STM', 'brand' => 'Canon', 'model' => 'RF50',
                'serial_number' => 'SN-RF50-001', 'status' => 'available', 'condition_status' => 'good', 'purchase_date' => '2023-01-10',
                'purchase_price' => 3200000, 'location' => 'Studio Foto', 'image' => 'lensa-canon-rf50.jpg',
                'description' => 'Lensa prime ringkas dengan aperture besar yang ideal untuk potret, foto produk, dan dokumentasi suasana ruangan. Autofokus STM bekerja halus sehingga cocok untuk rekaman video yang membutuhkan perpindahan fokus tanpa suara motor yang mengganggu. Beratnya ringan dan mudah dibawa selama kegiatan luar ruangan.',
                'specifications' => [
                    'Focal length' => '50 mm',
                    'Aperture' => 'F1.8',
                    'Mount' => 'Canon RF',
                    'Autofokus' => 'STM',
                    'Berat' => '160 gram',
                    'Filter' => '43 mm',
                ],
            ],
            [
                'code' => 'LDR-LEN-0003', 'category' => 'zoom', 'name' => 'Sigma 18-35mm F1.8 Art', 'brand' => 'Sigma', 'model' => '18-35 Art',
                'serial_number' => 'SN-SIG1835-001', 'status' => 'borrowed', 'condition_status' => 'good', 'purchase_date' => '2022-08-01',
                'purchase_price' => 9800000, 'location' => 'Studio Foto', 'image' => 'lensa-sigma-18-35.jpg',
                'description' => 'Lensa zoom sudut lebar dengan aperture F1.8 yang tidak biasa untuk kelasnya. Sangat berguna untuk merekam ruangan sempit, kegiatan kelas, dan wawancara jarak dekat dengan latar belakang yang lembut. Lensa ini hanya dapat dipasang pada bodi APS-C melalui adapter yang tersedia di loker operator.',
                'specifications' => [
                    'Focal length' => '18–35 mm',
                    'Aperture' => 'F1.8 konstan',
                    'Format' => 'APS-C',
                    'Mount' => 'Sony E / Canon EF (adapter)',
                    'Berat' => '810 gram',
                    'Aksesori' => 'Tudung dan tas lensa',
                ],
            ],
            [
                'code' => 'LDR-LEN-0004', 'category' => 'zoom', 'name' => 'Tamron 70-200mm F2.8 G2', 'brand' => 'Tamron', 'model' => 'A025',
                'serial_number' => 'SN-TAM70200-001', 'status' => 'maintenance', 'condition_status' => 'minor_damage', 'purchase_date' => '2021-09-12',
                'purchase_price' => 16500000, 'location' => 'Ruang Perawatan', 'image' => 'lensa-tamron-70-200.jpg',
                'description' => 'Lensa tele zoom untuk upacara, pertandingan, dan pengambilan gambar dari jarak jauh. Unit ini masuk perawatan karena ring zoom terasa sedikit berat dan getaran stabilizer perlu dikalibrasi ulang. Setelah selesai diperiksa teknisi, lensa akan kembali tersedia untuk peminjaman.',
                'specifications' => [
                    'Focal length' => '70–200 mm',
                    'Aperture' => 'F2.8 konstan',
                    'Stabilisasi' => 'VC (Vibration Compensation)',
                    'Mount' => 'Canon EF',
                    'Berat' => '1.500 gram',
                    'Catatan' => 'Ring zoom perlu perawatan',
                ],
            ],
            [
                'code' => 'LDR-TRI-0001', 'category' => 'tripod', 'name' => 'Manfrotto MT055XPRO3', 'brand' => 'Manfrotto', 'model' => 'MT055XPRO3',
                'serial_number' => 'SN-MTF055-001', 'status' => 'available', 'condition_status' => 'good', 'purchase_date' => '2022-02-02',
                'purchase_price' => 4100000, 'location' => 'Gudang', 'image' => 'tripod-manfrotto.jpg',
                'description' => 'Tripod aluminium kokoh dengan mekanisme kolom tengah yang dapat diputar ke posisi horizontal. Cocok untuk foto produk, wawancara statis, dan pengambilan gambar dengan sudut rendah. Kaki tripod memiliki pengunci cepat dan dilengkapi kepala ball head serta pelat kamera.',
                'specifications' => [
                    'Material' => 'Aluminium',
                    'Tinggi maksimum' => '170 cm',
                    'Beban maksimum' => '9 kg',
                    'Kepala' => 'Ball head dengan pelat kamera',
                    'Berat' => '2,5 kg',
                    'Aksesori' => 'Tas tripod',
                ],
            ],
            [
                'code' => 'LDR-TRI-0002', 'category' => 'tripod', 'name' => 'Gitzo GT1545T', 'brand' => 'Gitzo', 'model' => 'GT1545T',
                'serial_number' => 'SN-GTZ1545-001', 'status' => 'available', 'condition_status' => 'good', 'purchase_date' => '2023-04-18',
                'purchase_price' => 7800000, 'location' => 'Gudang', 'image' => 'tripod-gitzo.jpg',
                'description' => 'Tripod carbon fiber ringan untuk perjalanan dan kegiatan luar ruangan. Bobotnya mudah dibawa dalam tas punggung, tetapi tetap stabil untuk kamera full-frame dengan lensa standar. Sistem kaki twist lock memudahkan penyetelan ketinggian di permukaan yang tidak rata.',
                'specifications' => [
                    'Material' => 'Carbon fiber',
                    'Tinggi maksimum' => '156 cm',
                    'Beban maksimum' => '10 kg',
                    'Berat' => '1,45 kg',
                    'Kepala' => 'Ball head',
                    'Aksesori' => 'Tas travel',
                ],
            ],
            [
                'code' => 'LDR-TRI-0003', 'category' => 'tripod', 'name' => 'Benro A2573FS4', 'brand' => 'Benro', 'model' => 'A2573FS4',
                'serial_number' => 'SN-BEN2573-001', 'status' => 'retired', 'condition_status' => 'good', 'purchase_date' => '2019-07-21',
                'purchase_price' => 2300000, 'location' => 'Gudang', 'image' => 'tripod-manfrotto.jpg',
                'description' => 'Tripod lama yang sudah dipensiunkan dari daftar pinjam karena pengunci salah satu kaki mulai longgar. Masih dapat digunakan untuk kebutuhan internal terbatas, tetapi tidak disarankan untuk produksi yang membutuhkan kestabilan tinggi. Unit disimpan di gudang sebagai cadangan.',
                'specifications' => [
                    'Material' => 'Aluminium',
                    'Tinggi maksimum' => '165 cm',
                    'Beban maksimum' => '8 kg',
                    'Kepala' => 'Fluid head',
                    'Status' => 'Pensiun, cadangan internal',
                    'Catatan' => 'Pengunci kaki longgar',
                ],
            ],
            [
                'code' => 'LDR-TRI-0004', 'category' => 'tripod', 'name' => 'DJI RS 3', 'brand' => 'DJI', 'model' => 'RS 3',
                'serial_number' => 'SN-DJIRS3-001', 'status' => 'available', 'condition_status' => 'good', 'purchase_date' => '2023-08-09',
                'purchase_price' => 8500000, 'location' => 'Gudang', 'image' => 'gimbal-stabilizer.jpg',
                'description' => 'Gimbal tiga sumbu untuk merekam video bergerak dengan hasil halus. Cocok untuk kegiatan sekolah, liputan acara, dan pembuatan konten pendek. Unit dilengkapi pelat kamera, baterai cadangan, dan tas pelindung; pengguna wajib menyeimbangkan kamera sebelum menyalakan gimbal.',
                'specifications' => [
                    'Tipe' => '3-axis camera stabilizer',
                    'Beban maksimum' => '3 kg',
                    'Baterai' => 'Hingga 12 jam',
                    'Fitur' => 'ActiveTrack, mode panorama',
                    'Berat' => '1,3 kg',
                    'Aksesori' => 'Tas, pelat kamera, baterai cadangan',
                ],
            ],
            [
                'code' => 'LDR-LMP-0001', 'category' => 'lampu', 'name' => 'Godox SL60W', 'brand' => 'Godox', 'model' => 'SL60W',
                'serial_number' => 'SN-SL60W-001', 'status' => 'available', 'condition_status' => 'good', 'purchase_date' => '2022-05-11',
                'purchase_price' => 1900000, 'location' => 'Studio Foto', 'image' => 'lampu-godox.jpg',
                'description' => 'Lampu LED continuous 60 watt dengan temperatur warna tetap 5600K. Cahaya yang dihasilkan stabil dan mudah diatur melalui remote, sehingga cocok untuk wawancara dan foto produk. Unit dilengkapi reflektor standar dan dapat dipasang pada stand lampu yang tersedia di studio.',
                'specifications' => [
                    'Daya' => '60 W',
                    'Temperatur' => '5600K',
                    'CRI' => '≥ 95',
                    'Mount' => 'Bowens',
                    'Kontrol' => 'Remote nirkabel',
                    'Aksesori' => 'Reflektor standar',
                ],
            ],
            [
                'code' => 'LDR-LMP-0002', 'category' => 'lampu', 'name' => 'Godox AD200 Pro', 'brand' => 'Godox', 'model' => 'AD200 Pro',
                'serial_number' => 'SN-AD200-001', 'status' => 'borrowed', 'condition_status' => 'good', 'purchase_date' => '2022-05-11',
                'purchase_price' => 5200000, 'location' => 'Studio Foto', 'image' => 'lampu-godox.jpg',
                'description' => 'Flash portabel dengan daya tinggi yang dapat digunakan di dalam maupun luar ruangan. Dua kepala flash tersedia untuk menghasilkan cahaya langsung atau melalui softbox. Cocok untuk potret acara dan foto kelompok dengan pencahayaan yang konsisten.',
                'specifications' => [
                    'Daya' => '200 Ws',
                    'Temperatur' => '5600K ± 200K',
                    'Baterai' => 'Lithium-ion, hingga 500 flash',
                    'Mode' => 'TTL, Manual, HSS',
                    'Kepala' => 'Standard dan bare bulb',
                    'Aksesori' => 'Charger dan tas',
                ],
            ],
            [
                'code' => 'LDR-LMP-0003', 'category' => 'lampu', 'name' => 'Aputure 120D II', 'brand' => 'Aputure', 'model' => '120D II',
                'serial_number' => 'SN-AP120D-001', 'status' => 'maintenance', 'condition_status' => 'damaged', 'purchase_date' => '2021-12-01',
                'purchase_price' => 6900000, 'location' => 'Ruang Perawatan', 'image' => 'lampu-aputure.jpg',
                'description' => 'Lampu LED COB 120 watt yang biasa dipakai untuk produksi video dengan kebutuhan cahaya besar. Unit sedang diperiksa karena kipas pendingin mengeluarkan suara tidak normal. Belum tersedia untuk dipinjam sampai teknisi menyelesaikan perawatan dan pengujian suhu.',
                'specifications' => [
                    'Daya' => '135 W',
                    'Temperatur' => '5500K',
                    'CRI' => '≥ 96',
                    'Mount' => 'Bowens',
                    'Status' => 'Perawatan kipas pendingin',
                    'Aksesori' => 'Reflektor, remote',
                ],
            ],
            [
                'code' => 'LDR-LMP-0004', 'category' => 'lampu', 'name' => 'Godox Softbox 90x90', 'brand' => 'Godox', 'model' => 'SB-90',
                'serial_number' => 'SN-SB90-001', 'status' => 'available', 'condition_status' => 'good', 'purchase_date' => '2022-05-11',
                'purchase_price' => 750000, 'location' => 'Studio Foto', 'image' => 'studio-softbox.jpg',
                'description' => 'Softbox persegi 90 x 90 cm untuk melembutkan cahaya lampu studio. Difuser ganda menghasilkan bayangan yang halus pada wajah dan mengurangi hotspot pada foto produk. Softbox dipasang dengan mount Bowens dan dilengkapi tas jinjing.',
                'specifications' => [
                    'Ukuran' => '90 x 90 cm',
                    'Mount' => 'Bowens',
                    'Difuser' => 'Ganda (inner dan outer)',
                    'Berat' => '1,2 kg',
                    'Aksesori' => 'Tas jinjing',
                    'Penggunaan' => 'Foto potret dan produk',
                ],
            ],
        ];
    }

    private function attachPhoto(Asset $asset, string $image): void
    {
        $source = public_path("images/products/{$image}");

        if (! is_file($source)) {
            return;
        }

        $path = "assets/{$asset->code}/photo.jpg";
        Storage::disk('public')->put($path, file_get_contents($source));
        Storage::disk('public')->delete("assets/{$asset->code}/placeholder.svg");

        $asset->photos()->delete();

        AssetPhoto::create([
            'asset_id' => $asset->id,
            'path' => $path,
            'is_primary' => true,
            'sort_order' => 0,
        ]);
    }
}
