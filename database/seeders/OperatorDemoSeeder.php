<?php

namespace Database\Seeders;

use App\Enums\HandoverType;
use App\Enums\InspectionContext;
use App\Enums\InspectionFollowUp;
use App\Enums\InspectionResult;
use App\Enums\MaintenancePriority;
use App\Enums\MaintenanceStatus;
use App\Enums\QueueServiceType;
use App\Enums\QueueTicketStatus;
use App\Models\Booking;
use App\Models\Handover;
use App\Models\HandoverItem;
use App\Models\Inspection;
use App\Models\Maintenance;
use App\Models\MaintenancePart;
use App\Models\Organization;
use App\Models\Penalty;
use App\Models\PenaltyItem;
use App\Models\QueueTicket;
use App\Models\ShiftNote;
use App\Models\User;
use Illuminate\Database\Seeder;

class OperatorDemoSeeder extends Seeder
{
    public function run(): void
    {
        $organization = Organization::where('code', 'LDR')->firstOrFail();
        $operator = User::where('email', 'operator@lendora.test')->firstOrFail();
        $technician = User::where('email', 'technician@lendora.test')->firstOrFail();

        $this->seedActivePickup($operator);
        $this->seedReturnHistory($operator);
        $this->seedMaintenance($operator, $technician);
        $this->seedBapItems();
        $this->seedQueue($organization);
        $this->seedShiftNotes($organization, $operator);
    }

    private function seedActivePickup(User $operator): void
    {
        $booking = Booking::where('code', 'LDR-BK-2026-0003')->with('items')->first();

        if ($booking === null) {
            return;
        }

        $handover = Handover::updateOrCreate(
            ['code' => 'TRX-2026-0003-OUT'],
            [
                'booking_id' => $booking->id,
                'type' => HandoverType::Pickup,
                'performed_by' => $operator->id,
                'shift_label' => 'Pagi (08:00–16:00)',
                'checklist' => ['sensor_clean' => true, 'battery_full' => true, 'accessories_complete' => true],
                'happened_at' => $booking->pickup_at ?? now()->subDays(2),
                'due_at' => $booking->due_at,
                'notes' => 'Unit diserahkan lengkap dengan hardcase dan dua baterai.',
            ],
        );

        foreach ($booking->items as $item) {
            HandoverItem::updateOrCreate(
                ['handover_id' => $handover->id, 'booking_item_id' => $item->id],
                [
                    'condition' => 'good',
                    'accessories' => ['body_cap', 'battery', 'charger', 'strap'],
                    'notes' => null,
                ],
            );
        }
    }

    private function seedReturnHistory(User $operator): void
    {
        $booking = Booking::where('code', 'LDR-BK-2026-0001')->with('items')->first();

        if ($booking === null) {
            return;
        }

        $handover = Handover::updateOrCreate(
            ['code' => 'TRX-2026-0001-IN'],
            [
                'booking_id' => $booking->id,
                'type' => HandoverType::Return,
                'performed_by' => $operator->id,
                'shift_label' => 'Sore (12:00–20:00)',
                'checklist' => ['sensor_clean' => true, 'accessories_complete' => true],
                'happened_at' => $booking->returned_at ?? now()->subDays(15),
                'notes' => 'Dikembalikan tepat waktu, kondisi baik.',
            ],
        );

        foreach ($booking->items as $item) {
            HandoverItem::updateOrCreate(
                ['handover_id' => $handover->id, 'booking_item_id' => $item->id],
                ['condition' => 'good', 'accessories' => ['body_cap', 'battery', 'strap'], 'notes' => null],
            );

            Inspection::updateOrCreate(
                ['booking_item_id' => $item->id, 'inspector_id' => $operator->id],
                [
                    'asset_id' => $item->asset_id,
                    'code' => 'INS-2026-'.str_pad((string) $item->id, 4, '0', STR_PAD_LEFT),
                    'context' => InspectionContext::PostReturn,
                    'result' => InspectionResult::Ok,
                    'checklist' => ['shutter' => true, 'sensor' => true, 'ports' => true, 'lcd' => true],
                    'condition_score' => 98,
                    'follow_up' => InspectionFollowUp::None,
                    'notes' => 'Bersih, fungsi normal, siap kembali ke rak.',
                    'checked_at' => $booking->returned_at ?? now()->subDays(15),
                ],
            );
        }
    }

    private function seedMaintenance(User $operator, User $technician): void
    {
        $maintenance = Maintenance::where('status', MaintenanceStatus::InProgress->value)
            ->whereHas('asset', fn ($query) => $query->where('code', 'LDR-CAM-0005'))
            ->first();

        if ($maintenance === null) {
            return;
        }

        $maintenance->update([
            'code' => 'MTC-2026-0004',
            'priority' => MaintenancePriority::High,
            'vendor' => 'Lab AV Internal',
            'assigned_to' => $technician->id,
            'due_at' => now()->addDays(3),
            'labor_cost' => 250_000,
            'progress' => [
                ['label' => 'Tiket dibuat', 'done' => true, 'at' => now()->subDay()->toDateTimeString()],
                ['label' => 'Diagnosa & suku cadang tiba', 'done' => true, 'at' => now()->toDateTimeString()],
                ['label' => 'Penggantian LCD & kalibrasi sensor', 'done' => false, 'at' => null],
                ['label' => 'Uji kelayakan & rilis tersedia', 'done' => false, 'at' => null],
            ],
        ]);

        foreach ([
            ['name' => 'Flex Cable LCD Canon Artikulasi', 'part_code' => 'PART-CAN-LCD', 'quantity' => 1, 'unit_cost' => 600_000],
            ['name' => 'Jasa Kalibrasi Sensor & Mounting', 'part_code' => 'SRV-CAL-OPT-02', 'quantity' => 1, 'unit_cost' => 250_000],
        ] as $part) {
            MaintenancePart::updateOrCreate(
                ['maintenance_id' => $maintenance->id, 'part_code' => $part['part_code']],
                $part,
            );
        }
    }

    private function seedBapItems(): void
    {
        $penalty = Penalty::where('bap_number', 'LDR-BAP-2026-0004')->first();

        if ($penalty === null) {
            return;
        }

        $penalty->update([
            'case_category' => 'Kerusakan berat elemen optik / sensor',
            'chronology' => 'Unit terjatuh saat produksi di area pantai. Peminjam melaporkan pembersihan sensor dilakukan tanpa blower.',
            'payment_terms_days' => 7,
            'photo_paths' => [],
        ]);

        foreach ([
            ['description' => 'Original Sony A7 III IR-Cut Glass Sensor Assembly', 'part_code' => 'PART-SNY-A73-IRC', 'quantity' => 1, 'unit_cost' => 2_100_000],
            ['description' => 'Lens Mount Ring Collar & Brass Screw Set', 'part_code' => 'PART-SNY-A7-MNT', 'quantity' => 1, 'unit_cost' => 450_000],
            ['description' => 'Jasa Kalibrasi Optik Presisi & Kolimasi Lab', 'part_code' => 'SRV-CAL-OPT-02', 'quantity' => 1, 'unit_cost' => 400_000],
            ['description' => 'Denda keterlambatan & administrasi klaim', 'part_code' => null, 'quantity' => 1, 'unit_cost' => 500_000],
        ] as $item) {
            PenaltyItem::updateOrCreate(
                ['penalty_id' => $penalty->id, 'description' => $item['description']],
                [
                    'part_code' => $item['part_code'],
                    'quantity' => $item['quantity'],
                    'unit_cost' => $item['unit_cost'],
                    'subtotal' => $item['quantity'] * $item['unit_cost'],
                ],
            );
        }
    }

    private function seedQueue(Organization $organization): void
    {
        $today = now()->toDateString();

        foreach ([
            ['number' => 'A-018', 'service_type' => QueueServiceType::Pickup, 'status' => QueueTicketStatus::Called, 'counter' => 'A', 'nim' => '2205346089'],
            ['number' => 'A-019', 'service_type' => QueueServiceType::Return, 'status' => QueueTicketStatus::Waiting, 'counter' => null, 'nim' => '2108201044'],
            ['number' => 'A-020', 'service_type' => QueueServiceType::Consultation, 'status' => QueueTicketStatus::Waiting, 'counter' => null, 'nim' => '2207412089'],
            ['number' => 'B-009', 'service_type' => QueueServiceType::Return, 'status' => QueueTicketStatus::Serving, 'counter' => 'B', 'nim' => '2109302088'],
        ] as $ticket) {
            QueueTicket::updateOrCreate(
                ['organization_id' => $organization->id, 'service_date' => $today, 'number' => $ticket['number']],
                [
                    'service_type' => $ticket['service_type'],
                    'status' => $ticket['status'],
                    'counter' => $ticket['counter'],
                    'nim' => $ticket['nim'],
                    'issued_at' => now()->subMinutes(30),
                    'called_at' => $ticket['status'] === QueueTicketStatus::Waiting ? null : now()->subMinutes(12),
                ],
            );
        }
    }

    private function seedShiftNotes(Organization $organization, User $operator): void
    {
        ShiftNote::firstOrCreate(
            ['organization_id' => $organization->id, 'note' => 'Baterai Sony NP-FZ100 sudah dicas penuh di dock rak B. Dry box #2 stabil di kelembaban 45%.'],
            ['user_id' => $operator->id, 'shift' => 'Sore'],
        );
    }
}
