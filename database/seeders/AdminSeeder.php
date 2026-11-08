<?php

namespace Database\Seeders;

use App\Models\Organization;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $organization = Organization::where('code', 'LDR')->firstOrFail();
        $roles = Role::pluck('id', 'slug');

        $users = [
            ['name' => 'Organization Admin', 'email' => 'admin@lendora.test', 'role' => 'organization_admin'],
            ['name' => 'Operator Lendora', 'email' => 'operator@lendora.test', 'role' => 'operator'],
            ['name' => 'Technician Lendora', 'email' => 'technician@lendora.test', 'role' => 'technician'],
            ['name' => 'Admin Prodi', 'email' => 'adminprodi@lendora.test', 'role' => 'admin_prodi'],
            ['name' => 'Borrower Satu', 'email' => 'borrower1@lendora.test', 'role' => 'borrower'],
            ['name' => 'Borrower Dua', 'email' => 'borrower2@lendora.test', 'role' => 'borrower'],
        ];

        foreach ($users as $user) {
            User::updateOrCreate(
                ['email' => $user['email']],
                [
                    'name' => $user['name'],
                    'password' => 'password',
                    'role_id' => $roles[$user['role']],
                    'organization_id' => $organization->id,
                    'status' => 'active',
                ],
            );
        }
    }
}
