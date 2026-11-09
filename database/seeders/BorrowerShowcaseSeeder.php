<?php

namespace Database\Seeders;

use App\Enums\BookingItemStatus;
use App\Enums\BookingStatus;
use App\Models\Asset;
use App\Models\AssetPhoto;
use App\Models\Booking;
use App\Models\Category;
use App\Models\Organization;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class BorrowerShowcaseSeeder extends Seeder
{
    public function run(): void
    {
        $organization = Organization::where('code', 'LDR')->firstOrFail();
        $borrowerRole = Role::where('slug', 'borrower')->firstOrFail();
        $admin = User::where('email', 'admin@lendora.test')->firstOrFail();

        $user = User::updateOrCreate(
            ['email' => 'ani@lendora.test'],
            [
                'name' => 'Ani Rahmawati',
                'password' => 'password',
                'phone' => '081234567890',
                'role_id' => $borrowerRole->id,
                'organization_id' => $organization->id,
                'status' => 'active',
                'nim' => '2207412089',
                'study_program' => 'Desain Komunikasi Visual',
                'semester' => 5,
                'lab_tier' => 'A',
                'lab_member_id' => 'MP-2024-8842',
                'academic_verified_at' => now(),
                'email_verified_at' => now(),
            ],
        );

        $categories = $this->categories($organization);

        foreach ($this->assets() as $data) {
            $category = $categories[$data['category']];
            $image = $data['image'];
            unset($data['category'], $data['image']);

            $asset = Asset::updateOrCreate(
                ['code' => $data['code']],
                [...$data, 'organization_id' => $organization->id, 'category_id' => $category->id],
            );

            $this->attachPhoto($asset, $image);
        }

        $this->bookings($user, $admin, $organization);
    }

    /**
     * @return array<string, Category>
     */
    private function categories(Organization $organization): array
    {
        $cinema = Category::updateOrCreate(
            ['organization_id' => $organization->id, 'slug' => 'kamera-cinema'],
            ['name' => 'Kamera Cinema', 'parent_id' => null],
        );

        $audio = Category::updateOrCreate(
            ['organization_id' => $organization->id, 'slug' => 'audio'],
            ['name' => 'Audio & Mic', 'parent_id' => null],
        );

        $microphone = Category::updateOrCreate(
            ['organization_id' => $organization->id, 'slug' => 'microphone'],
            ['name' => 'Microphone', 'parent_id' => $audio->id],
        );

        return [
            'cinema' => $cinema,
            'audio' => $microphone,
            'full-frame' => Category::where('organization_id', $organization->id)->where('slug', 'full-frame')->firstOrFail(),
            'zoom' => Category::where('organization_id', $organization->id)->where('slug', 'zoom')->firstOrFail(),
            'prime' => Category::where('organization_id', $organization->id)->where('slug', 'prime')->firstOrFail(),
            'tripod' => Category::where('organization_id', $organization->id)->where('slug', 'tripod')->firstOrFail(),
            'lampu' => Category::where('organization_id', $organization->id)->where('slug', 'lampu')->firstOrFail(),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function assets(): array
    {
        return [
            [
                'code' => 'LDR-CAM-0007', 'category' => 'cinema', 'name' => 'Sony FX3 Cinema Line', 'brand' => 'Sony', 'model' => 'ILME-FX3',
                'serial_number' => 'SN-FX3-001', 'status' => 'available', 'condition_status' => 'good', 'purchase_date' => '2024-02-12',
                'purchase_price' => 49900000, 'location' => 'Studio Video', 'image' => 'kamera-sony-fx3.jpg',
                'description' => 'Kamera cinema ringkas dengan sensor full-frame yang dirancang untuk produksi video profesional. Body dilengkapi pegangan XLR, kipas pendingin aktif, dan thread mount untuk aksesori rig, sehingga siap dipakai untuk wawancara panjang maupun produksi film pendek. Profil warna S-Cinetone menghasilkan gambar yang langsung enak dipakai tanpa grading berat, sedangkan perekaman 4K 120 fps memungkinkan slow motion halus untuk dokumentasi kegiatan sekolah.',
                'specifications' => [
                    'Sensor' => '12,1 MP Full-frame Exmor R',
                    'Video' => '4K UHD hingga 120 fps, 10-bit 4:2:2',
                    'Profil warna' => 'S-Cinetone, S-Log3, HLG',
                    'Audio' => '2 input XLR via handle unit',
                    'Stabilisasi' => 'Active Mode IBIS',
                    'Mount' => 'Sony E',
                    'Aksesori' => 'Handle XLR, baterai NP-FZ100, cage',
                ],
            ],
            [
                'code' => 'LDR-CAM-0008', 'category' => 'cinema', 'name' => 'Blackmagic Pocket Cinema 6K Pro', 'brand' => 'Blackmagic Design', 'model' => 'Pocket 6K Pro',
                'serial_number' => 'SN-BMP6K-001', 'status' => 'available', 'condition_status' => 'good', 'purchase_date' => '2024-05-08',
                'purchase_price' => 36500000, 'location' => 'Studio Video', 'image' => 'kamera-blackmagic-6k.jpg',
                'description' => 'Kamera cinema dengan sensor Super 35 dan mount EF yang merekam RAW hingga 6K. Layar sentuh lipat, ND filter internal, dan dua input mini XLR membuat kamera ini praktis untuk produksi lapangan tanpa rig tambahan yang rumit. Cocok untuk tim yang ingin belajar alur kerja cinema, mulai dari pengaturan exposure, fokus manual, hingga color grading di DaVinci Resolve.',
                'specifications' => [
                    'Sensor' => 'Super 35, 6K',
                    'Video' => '6K RAW, 4K ProRes',
                    'Mount' => 'Canon EF',
                    'ND filter' => 'Internal 2, 4, 6 stop',
                    'Layar' => 'LCD lipat 5 inci',
                    'Audio' => '2 mini XLR',
                    'Media' => 'CFast 2.0 / SD UHS-II',
                ],
            ],
            [
                'code' => 'LDR-CAM-0009', 'category' => 'full-frame', 'name' => 'Sony A7 IV', 'brand' => 'Sony', 'model' => 'ILCE-7M4',
                'serial_number' => 'SN-A7IV-001', 'status' => 'available', 'condition_status' => 'good', 'purchase_date' => '2024-08-19',
                'purchase_price' => 27900000, 'location' => 'Studio Foto', 'image' => 'kamera-sony-a7-iii-alt.jpg',
                'description' => 'Kamera hybrid full-frame yang seimbang untuk foto dan video. Autofokus real-time berbasis AI mengunci mata subjek dengan cepat, layar lipat memudahkan framing dari berbagai sudut, dan mode video 4K 60 fps sudah cukup untuk kebutuhan dokumentasi modern. Pilihan tepat bagi peminjam yang membutuhkan satu kamera serbaguna untuk tugas foto sekaligus video.',
                'specifications' => [
                    'Sensor' => '33 MP Full-frame Exmor R',
                    'Prosesor' => 'BIONZ XR',
                    'Video' => '4K 60 fps, 10-bit 4:2:2',
                    'Stabilisasi' => '5-axis IBIS',
                    'Autofokus' => '759 titik phase-detection',
                    'Mount' => 'Sony E',
                    'Slot media' => 'CFexpress Type A + SD',
                ],
            ],
            [
                'code' => 'LDR-LEN-0005', 'category' => 'prime', 'name' => 'Sony FE 85mm F1.4 GM', 'brand' => 'Sony', 'model' => 'SEL85F14GM',
                'serial_number' => 'SN-85GM-001', 'status' => 'available', 'condition_status' => 'good', 'purchase_date' => '2023-11-02',
                'purchase_price' => 21900000, 'location' => 'Studio Foto', 'image' => 'lensa-sony-24-70.jpg',
                'description' => 'Lensa prime portrait dengan aperture F1.4 yang menghasilkan pemisahan subjek dan latar belakang sangat halus. Elemen kaca G Master menjaga ketajaman tetap tinggi bahkan pada aperture terbuka, sementara motor autofokus linear bekerja cepat dan senyap untuk video. Lensa ini ideal untuk foto wisuda, potret kegiatan, dan wawancara dengan latar belakang yang lembut.',
                'specifications' => [
                    'Focal length' => '85 mm',
                    'Aperture' => 'F1.4',
                    'Mount' => 'Sony E',
                    'Elemen' => '11 elemen dalam 8 grup',
                    'Autofokus' => 'Linear motor, mendukung Eye AF',
                    'Berat' => '820 gram',
                    'Filter' => '77 mm',
                ],
            ],
            [
                'code' => 'LDR-LMP-0005', 'category' => 'lampu', 'name' => 'Aputure Amaran 200D', 'brand' => 'Aputure', 'model' => 'Amaran 200D',
                'serial_number' => 'SN-AM200D-001', 'status' => 'available', 'condition_status' => 'good', 'purchase_date' => '2024-01-22',
                'purchase_price' => 4600000, 'location' => 'Studio Video', 'image' => 'lampu-aputure.jpg',
                'description' => 'Lampu LED daylight 200 watt dengan output tinggi dan kontrol melalui aplikasi. Mount Bowens memungkinkan penggunaan softbox, lantern, atau reflektor yang sudah tersedia di lab. Lampu ini cocok untuk menerangi ruangan wawancara, memperkuat pencahayaan produk, dan menjadi key light pada setup tiga titik yang sederhana.',
                'specifications' => [
                    'Daya' => '200 W',
                    'Temperatur' => '5600K',
                    'CRI' => '≥ 95',
                    'Mount' => 'Bowens',
                    'Kontrol' => 'Aplikasi Sidus Link',
                    'Daya listrik' => 'AC 100–240 V',
                    'Aksesori' => 'Reflektor, power cable',
                ],
            ],
            [
                'code' => 'LDR-TRI-0005', 'category' => 'tripod', 'name' => 'Manfrotto Befree Advanced', 'brand' => 'Manfrotto', 'model' => 'Befree Advanced',
                'serial_number' => 'SN-BEFREE-001', 'status' => 'available', 'condition_status' => 'good', 'purchase_date' => '2023-09-14',
                'purchase_price' => 3200000, 'location' => 'Gudang', 'image' => 'tripod-manfrotto.jpg',
                'description' => 'Tripod travel ringkas yang dapat dilipat hingga masuk ke tas harian. Sistem pengunci kaki aluminium dan ball head membuat penyetelan cepat, sementara beratnya tetap ringan untuk kegiatan lapangan. Cocok untuk kamera mirrorless, dokumentasi acara, dan pengambilan video statis di luar ruangan.',
                'specifications' => [
                    'Material' => 'Aluminium',
                    'Tinggi maksimum' => '150 cm',
                    'Tinggi terlipat' => '40 cm',
                    'Beban maksimum' => '8 kg',
                    'Kepala' => 'Ball head 494',
                    'Berat' => '1,6 kg',
                ],
            ],
            [
                'code' => 'LDR-AUD-0001', 'category' => 'audio', 'name' => 'Rode Wireless GO II', 'brand' => 'Rode', 'model' => 'Wireless GO II',
                'serial_number' => 'SN-RODEWG2-001', 'status' => 'available', 'condition_status' => 'good', 'purchase_date' => '2023-03-07',
                'purchase_price' => 5200000, 'location' => 'Studio Audio', 'image' => 'audio-rode-wireless-go2.jpg',
                'description' => 'Sistem mikrofon nirkabel dua transmitter dan satu receiver untuk wawancara, presentasi, dan dokumentasi acara. Setiap transmitter memiliki mikrofon internal serta input lavalier, dan rekaman cadangan tersimpan di dalam perangkat sehingga audio tetap aman bila sinyal terputus. Koneksi USB-C dan 3,5 mm memudahkan penggunaan dengan kamera maupun ponsel.',
                'specifications' => [
                    'Tipe' => 'Dual-channel wireless microphone',
                    'Transmitter' => '2 unit dengan mic internal',
                    'Receiver' => '1 unit, output 3,5 mm dan USB-C',
                    'Rekaman' => 'Internal backup hingga 24 jam',
                    'Jarak' => 'Hingga 200 meter (line of sight)',
                    'Baterai' => 'Hingga 7 jam',
                    'Aksesori' => 'Kabel USB-C, windshield, tas',
                ],
            ],
            [
                'code' => 'LDR-AUD-0002', 'category' => 'audio', 'name' => 'DJI Mic', 'brand' => 'DJI', 'model' => 'Mic',
                'serial_number' => 'SN-DJIMIC-001', 'status' => 'available', 'condition_status' => 'good', 'purchase_date' => '2023-10-11',
                'purchase_price' => 4300000, 'location' => 'Studio Audio', 'image' => 'audio-dji-mic.jpg',
                'description' => 'Mikrofon nirkabel ringkas dengan dua transmitter, receiver, dan case pengisi daya sekaligus. Layar sentuh pada receiver memudahkan pemantauan level audio dan pengaturan gain saat merekam. Cocok untuk vlog, wawancara lapangan, dan konten media sosial karena dapat langsung tersambung ke kamera, ponsel, atau laptop.',
                'specifications' => [
                    'Tipe' => 'Dual wireless microphone',
                    'Transmitter' => '2 unit, mic internal',
                    'Receiver' => '1 unit dengan layar sentuh',
                    'Rekaman internal' => 'Ya, 14 jam',
                    'Jarak' => 'Hingga 250 meter',
                    'Baterai' => 'Hingga 15 jam dengan case',
                    'Aksesori' => 'Case charging, kabel, adapter',
                ],
            ],
            [
                'code' => 'LDR-AUD-0003', 'category' => 'audio', 'name' => 'Zoom H4n Pro', 'brand' => 'Zoom', 'model' => 'H4n Pro',
                'serial_number' => 'SN-ZOOMH4N-001', 'status' => 'available', 'condition_status' => 'good', 'purchase_date' => '2022-07-30',
                'purchase_price' => 3100000, 'location' => 'Studio Audio', 'image' => 'audio-zoom-h4n.jpg',
                'description' => 'Perekam audio portabel dengan dua mikrofon XY bawaan dan dua input XLR/TRS. Alat ini cocok untuk merekam wawancara, ambience ruangan, dan audio cadangan saat produksi video. Layar LCD dan tombol fisik memudahkan pengaturan level secara cepat, sedangkan file dapat disimpan ke kartu SD untuk dipindahkan ke komputer.',
                'specifications' => [
                    'Tipe' => 'Handy audio recorder',
                    'Mikrofon' => '2 mic XY stereo',
                    'Input' => '2 XLR/TRS combo',
                    'Resolusi' => 'Hingga 24-bit/96 kHz',
                    'Media' => 'Kartu SD/SDHC',
                    'Baterai' => '2 AA atau USB power',
                    'Aksesori' => 'Kabel USB, tas, kartu SD',
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

    private function bookings(User $user, User $admin, Organization $organization): void
    {
        $active = Booking::updateOrCreate(
            ['code' => 'LDR-BK-2026-0101'],
            [
                'organization_id' => $organization->id,
                'borrower_id' => $user->id,
                'status' => BookingStatus::Active,
                'purpose' => 'Produksi video profil jurusan untuk kegiatan open house sekolah',
                'requested_start_date' => now()->subDay()->toDateString(),
                'requested_end_date' => now()->addDay()->toDateString(),
                'approved_by' => $admin->id,
                'approved_at' => now()->subDays(2),
                'pickup_at' => now()->subDay()->setTime(9, 15),
                'due_at' => now()->addDay()->setTime(16, 0),
            ],
        );

        $this->items($active, ['LDR-CAM-0007', 'LDR-LEN-0005'], BookingItemStatus::PickedUp, 'borrowed');

        $pending = Booking::updateOrCreate(
            ['code' => 'LDR-BK-2026-0102'],
            [
                'organization_id' => $organization->id,
                'borrower_id' => $user->id,
                'status' => BookingStatus::Pending,
                'purpose' => 'Rekaman wawancara guru dan siswa untuk konten media sosial sekolah',
                'requested_start_date' => now()->addDays(3)->toDateString(),
                'requested_end_date' => now()->addDays(5)->toDateString(),
            ],
        );

        $this->items($pending, ['LDR-AUD-0001', 'LDR-AUD-0003'], BookingItemStatus::Pending);

        $returned = Booking::updateOrCreate(
            ['code' => 'LDR-BK-2026-0103'],
            [
                'organization_id' => $organization->id,
                'borrower_id' => $user->id,
                'status' => BookingStatus::Returned,
                'purpose' => 'Dokumentasi foto kegiatan class meeting',
                'requested_start_date' => now()->subDays(18)->toDateString(),
                'requested_end_date' => now()->subDays(15)->toDateString(),
                'approved_by' => $admin->id,
                'approved_at' => now()->subDays(20),
                'pickup_at' => now()->subDays(18)->setTime(8, 30),
                'due_at' => now()->subDays(15)->setTime(16, 0),
                'returned_at' => now()->subDays(15)->setTime(14, 45),
            ],
        );

        $this->items($returned, ['LDR-CAM-0009', 'LDR-LEN-0001'], BookingItemStatus::Returned);
    }

    /**
     * @param  array<int, string>  $codes
     */
    private function items(Booking $booking, array $codes, BookingItemStatus $status, ?string $assetStatus = null): void
    {
        foreach ($codes as $code) {
            $asset = Asset::where('code', $code)->firstOrFail();

            $booking->items()->updateOrCreate(
                ['asset_id' => $asset->id],
                [
                    'status' => $status,
                    'condition_out' => 'good',
                    'condition_in' => $status === BookingItemStatus::Returned ? 'good' : null,
                ],
            );

            if ($assetStatus !== null) {
                $asset->update(['status' => $assetStatus]);
            }
        }
    }
}
