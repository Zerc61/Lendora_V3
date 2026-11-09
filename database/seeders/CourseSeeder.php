<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Organization;
use Illuminate\Database\Seeder;

class CourseSeeder extends Seeder
{
    public function run(): void
    {
        $organization = Organization::where('code', 'LDR')->firstOrFail();

        $courses = [
            ['code' => 'DKV-201', 'name' => 'Fotografi Dasar', 'lecturer_name' => 'Rina Kusuma, M.Ds.', 'semester' => 3, 'credits' => 3],
            ['code' => 'DKV-312', 'name' => 'Sinematografi', 'lecturer_name' => 'Bagas Prasetyo, M.Sn.', 'semester' => 5, 'credits' => 4],
            ['code' => 'DKV-405', 'name' => 'Produksi Video Korporat', 'lecturer_name' => 'Sinta Maharani, M.Ds.', 'semester' => 7, 'credits' => 3],
            ['code' => 'DKV-501', 'name' => 'Proyek Akhir', 'lecturer_name' => 'Dr. Hendra Wijaya', 'semester' => 7, 'credits' => 6],
        ];

        foreach ($courses as $course) {
            Course::updateOrCreate(
                ['organization_id' => $organization->id, 'code' => $course['code']],
                [...$course, 'is_active' => true],
            );
        }
    }
}
