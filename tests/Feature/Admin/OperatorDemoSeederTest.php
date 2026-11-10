<?php

namespace Tests\Feature\Admin;

use App\Models\Handover;
use App\Models\MaintenancePart;
use App\Models\PenaltyItem;
use App\Models\QueueTicket;
use App\Models\ShiftNote;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OperatorDemoSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_seed_fills_operator_operational_data(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertSame(2, Handover::query()->count());
        $this->assertGreaterThanOrEqual(2, MaintenancePart::query()->count());
        $this->assertGreaterThanOrEqual(4, PenaltyItem::query()->count());
        $this->assertSame(4, QueueTicket::query()->whereDate('service_date', now()->toDateString())->count());
        $this->assertGreaterThanOrEqual(1, ShiftNote::query()->count());
    }
}
