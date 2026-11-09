<?php

namespace Database\Seeders;

use App\Enums\BookingExtensionStatus;
use App\Models\Booking;
use App\Models\BookingExtension;
use App\Models\User;
use Illuminate\Database\Seeder;

class BookingExtensionSeeder extends Seeder
{
    public function run(): void
    {
        $booking = Booking::where('code', 'LDR-BK-2026-0101')->first();
        $borrower = User::where('email', 'ani@lendora.test')->first();

        if ($booking === null || $borrower === null) {
            return;
        }

        BookingExtension::updateOrCreate(
            ['booking_id' => $booking->id, 'status' => BookingExtensionStatus::Pending->value],
            [
                'requested_by' => $borrower->id,
                'requested_days' => 1,
                'requested_due_at' => $booking->due_at?->copy()->addDay() ?? now()->addDay(),
                'reason' => 'Penambahan sesi wawancara di luar jadwal awal.',
            ],
        );
    }
}
