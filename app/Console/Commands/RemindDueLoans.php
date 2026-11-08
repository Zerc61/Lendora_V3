<?php

namespace App\Console\Commands;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Notifications\LoanDueReminderNotification;
use App\Services\SettingService;
use Illuminate\Console\Command;

class RemindDueLoans extends Command
{
    protected $signature = 'loans:remind-due';

    protected $description = 'Kirim pengingat H-1 kepada peminjam dengan peminjaman aktif yang jatuh tempo besok';

    public function handle(SettingService $settings): int
    {
        $tomorrow = now()->addDay()->toDateString();
        $sent = 0;

        Booking::query()
            ->with('borrower')
            ->where('status', BookingStatus::Active->value)
            ->whereDate('due_at', $tomorrow)
            ->chunkById(100, function ($bookings) use ($settings, &$sent): void {
                foreach ($bookings as $booking) {
                    if (! $settings->returnReminderEnabled($booking->organization_id)) {
                        continue;
                    }

                    if ($booking->borrower === null) {
                        continue;
                    }

                    $booking->borrower->notify(new LoanDueReminderNotification($booking));
                    $sent++;
                }
            });

        $this->info("Pengingat jatuh tempo terkirim: {$sent}.");

        return self::SUCCESS;
    }
}
