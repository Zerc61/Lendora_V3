<?php

namespace Tests\Unit;

use App\Enums\QueueServiceType;
use App\Enums\QueueTicketStatus;
use App\Models\Asset;
use App\Models\Booking;
use App\Models\BookingItem;
use App\Models\Category;
use App\Models\Handover;
use App\Models\HandoverItem;
use App\Models\Maintenance;
use App\Models\MaintenancePart;
use App\Models\Organization;
use App\Models\QueueTicket;
use App\Models\User;
use Database\Seeders\AdminSeeder;
use Database\Seeders\OrganizationSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OperatorSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_queue_ticket_casts_service_and_status(): void
    {
        $ticket = new QueueTicket([
            'service_type' => 'pickup',
            'status' => 'waiting',
        ]);

        $this->assertSame(QueueServiceType::Pickup, $ticket->service_type);
        $this->assertSame(QueueTicketStatus::Waiting, $ticket->status);
    }

    public function test_maintenance_total_cost_adds_parts_and_labor(): void
    {
        $maintenance = new Maintenance(['labor_cost' => 250_000]);
        $maintenance->setRelation('parts', collect([
            new MaintenancePart(['quantity' => 1, 'unit_cost' => 600_000]),
            new MaintenancePart(['quantity' => 2, 'unit_cost' => 75_000]),
        ]));

        $this->assertSame(750_000, $maintenance->partsTotal());
        $this->assertSame(1_000_000, $maintenance->totalCost());
    }

    public function test_handover_item_stores_accessories_and_condition(): void
    {
        $this->seed([RoleSeeder::class, OrganizationSeeder::class, AdminSeeder::class]);

        $operator = User::where('email', 'operator@lendora.test')->firstOrFail();
        $borrower = User::where('email', 'borrower1@lendora.test')->firstOrFail();
        $organization = Organization::where('code', 'LDR')->firstOrFail();

        $category = Category::create([
            'organization_id' => $organization->id,
            'name' => 'Kamera Mirrorless',
            'slug' => 'kamera-mirrorless',
        ]);

        $asset = Asset::create([
            'organization_id' => $organization->id,
            'category_id' => $category->id,
            'code' => 'LDR-CAM-9001',
            'name' => 'Kamera Uji',
            'status' => 'available',
        ]);

        $booking = Booking::create([
            'code' => 'LDR-BK-2026-9001',
            'organization_id' => $organization->id,
            'borrower_id' => $borrower->id,
            'status' => 'approved',
            'purpose' => 'Uji skema',
            'requested_start_date' => now()->toDateString(),
            'requested_end_date' => now()->addDays(2)->toDateString(),
        ]);

        $item = BookingItem::create([
            'booking_id' => $booking->id,
            'asset_id' => $asset->id,
            'status' => 'approved',
        ]);

        $handover = Handover::create([
            'booking_id' => $booking->id,
            'type' => 'pickup',
            'performed_by' => $operator->id,
            'happened_at' => now(),
        ]);

        $handoverItem = HandoverItem::create([
            'handover_id' => $handover->id,
            'booking_item_id' => $item->id,
            'condition' => 'good',
            'accessories' => ['body_cap', 'battery'],
        ]);

        $this->assertSame(['body_cap', 'battery'], $handoverItem->fresh()->accessories);
        $this->assertCount(1, $handover->fresh()->items);
    }
}
