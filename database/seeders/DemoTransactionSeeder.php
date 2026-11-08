<?php

namespace Database\Seeders;

use App\Enums\AssetStatus;
use App\Enums\BookingItemStatus;
use App\Enums\BookingStatus;
use App\Enums\MaintenanceStatus;
use App\Models\Asset;
use App\Models\Booking;
use App\Models\Maintenance;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoTransactionSeeder extends Seeder
{
    public function run(): void
    {
        $organization = Organization::where('code', 'LDR')->firstOrFail();
        $admin = User::where('email', 'admin@lendora.test')->firstOrFail();
        $operator = User::where('email', 'operator@lendora.test')->firstOrFail();
        $technician = User::where('email', 'technician@lendora.test')->firstOrFail();
        $borrower1 = User::where('email', 'borrower1@lendora.test')->firstOrFail();
        $borrower2 = User::where('email', 'borrower2@lendora.test')->firstOrFail();

        $completed = [
            [
                'code' => 'LDR-BK-2026-0001',
                'borrower' => $borrower1,
                'purpose' => 'Dokumentasi kegiatan sekolah',
                'start' => now()->subDays(20)->toDateString(),
                'end' => now()->subDays(15)->toDateString(),
                'assets' => ['LDR-CAM-0001', 'LDR-LEN-0001'],
            ],
            [
                'code' => 'LDR-BK-2026-0002',
                'borrower' => $borrower2,
                'purpose' => 'Pengambilan video profil unit',
                'start' => now()->subDays(12)->toDateString(),
                'end' => now()->subDays(9)->toDateString(),
                'assets' => ['LDR-TRI-0001', 'LDR-LMP-0001'],
            ],
        ];

        foreach ($completed as $data) {
            $booking = Booking::updateOrCreate(
                ['code' => $data['code']],
                [
                    'organization_id' => $organization->id,
                    'borrower_id' => $data['borrower']->id,
                    'status' => BookingStatus::Returned,
                    'purpose' => $data['purpose'],
                    'requested_start_date' => $data['start'],
                    'requested_end_date' => $data['end'],
                    'approved_by' => $admin->id,
                    'approved_at' => $data['start'].' 08:00:00',
                    'pickup_at' => $data['start'].' 09:00:00',
                    'due_at' => $data['end'].' 16:00:00',
                    'returned_at' => $data['end'].' 15:00:00',
                ],
            );

            foreach ($data['assets'] as $code) {
                $asset = Asset::where('code', $code)->firstOrFail();

                $booking->items()->updateOrCreate(
                    ['asset_id' => $asset->id],
                    [
                        'status' => BookingItemStatus::Returned,
                        'condition_out' => 'good',
                        'condition_in' => 'good',
                    ],
                );
            }
        }

        $activeBooking = Booking::updateOrCreate(
            ['code' => 'LDR-BK-2026-0003'],
            [
                'organization_id' => $organization->id,
                'borrower_id' => $borrower1->id,
                'status' => BookingStatus::Active,
                'purpose' => 'Liputan acara pameran sekolah',
                'requested_start_date' => now()->subDays(2)->toDateString(),
                'requested_end_date' => now()->addDays(3)->toDateString(),
                'approved_by' => $admin->id,
                'approved_at' => now()->subDays(3),
                'pickup_at' => now()->subDays(2),
                'due_at' => now()->addDays(3)->setTime(16, 0),
            ],
        );

        foreach (['LDR-CAM-0003', 'LDR-LMP-0004'] as $code) {
            $asset = Asset::where('code', $code)->firstOrFail();
            $asset->update(['status' => AssetStatus::Borrowed]);

            $activeBooking->items()->updateOrCreate(
                ['asset_id' => $asset->id],
                [
                    'status' => BookingItemStatus::PickedUp,
                    'condition_out' => 'good',
                ],
            );
        }

        $maintenanceAsset = Asset::where('code', 'LDR-CAM-0005')->firstOrFail();
        $maintenanceAsset->update(['status' => AssetStatus::Maintenance]);

        Maintenance::updateOrCreate(
            ['asset_id' => $maintenanceAsset->id, 'status' => MaintenanceStatus::InProgress->value],
            [
                'reported_by' => $operator->id,
                'assigned_to' => $technician->id,
                'description' => 'Pembersihan sensor dan kalibrasi fokus.',
                'started_at' => now()->subDay(),
            ],
        );
    }
}
