<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['name' => 'Borrower', 'slug' => 'borrower', 'description' => 'Mengajukan dan memantau peminjaman sendiri.'],
            ['name' => 'Operator', 'slug' => 'operator', 'description' => 'Menjalankan operasional harian peminjaman.'],
            ['name' => 'Organization Admin', 'slug' => 'organization_admin', 'description' => 'Mengelola inventaris, pengguna, dan kebijakan organisasi.'],
            ['name' => 'Admin Prodi', 'slug' => 'admin_prodi', 'description' => 'Menyetujui peminjaman durasi panjang dan memantau laporan prodi.'],
            ['name' => 'Technician', 'slug' => 'technician', 'description' => 'Mengerjakan perawatan dan mencatat inspeksi aset.'],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(['slug' => $role['slug']], $role);
        }
    }
}
