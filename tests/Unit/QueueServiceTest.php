<?php

namespace Tests\Unit;

use App\Enums\QueueServiceType;
use App\Enums\QueueTicketStatus;
use App\Models\Organization;
use App\Models\QueueTicket;
use App\Models\User;
use App\Services\QueueService;
use Database\Seeders\AdminSeeder;
use Database\Seeders\OrganizationSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QueueServiceTest extends TestCase
{
    use RefreshDatabase;

    private Organization $organization;

    private User $operator;

    private QueueService $queue;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([RoleSeeder::class, OrganizationSeeder::class, AdminSeeder::class]);

        $this->organization = Organization::where('code', 'LDR')->firstOrFail();
        $this->operator = User::where('email', 'operator@lendora.test')->firstOrFail();
        $this->queue = app(QueueService::class);
    }

    public function test_issue_uses_prefix_and_running_sequence_per_day(): void
    {
        $first = $this->queue->issue($this->organization->id, 'pickup');
        $second = $this->queue->issue($this->organization->id, 'pickup');
        $return = $this->queue->issue($this->organization->id, 'return');
        $consult = $this->queue->issue($this->organization->id, 'consultation');

        $this->assertSame('A-001', $first->number);
        $this->assertSame('A-002', $second->number);
        $this->assertSame('B-001', $return->number);
        $this->assertSame('T-001', $consult->number);
        $this->assertSame(QueueTicketStatus::Waiting, $first->status);
        $this->assertSame(QueueServiceType::Pickup, $first->service_type);
    }

    public function test_call_next_and_serve_transition_ticket(): void
    {
        $ticket = $this->queue->issue($this->organization->id, 'pickup');

        $called = $this->queue->callNext($this->organization->id, 'A', $this->operator);

        $this->assertNotNull($called);
        $this->assertSame($ticket->id, $called->id);
        $this->assertSame(QueueTicketStatus::Called, $called->status);
        $this->assertNotNull($called->called_at);

        $served = $this->queue->serve($called, $this->operator);

        $this->assertSame(QueueTicketStatus::Done, $served->status);
        $this->assertNotNull($served->served_at);
        $this->assertNull($this->queue->callNext($this->organization->id, 'A', $this->operator));
    }

    public function test_board_reports_waiting_counters_and_done(): void
    {
        $first = $this->queue->issue($this->organization->id, 'pickup');
        $this->queue->issue($this->organization->id, 'return');
        $this->queue->callNext($this->organization->id, 'A', $this->operator);
        $this->queue->serve($first, $this->operator);

        $board = $this->queue->board($this->organization->id);

        $this->assertSame(1, $board['waiting_count']);
        $this->assertSame(1, $board['done_count']);
    }

    public function test_prune_deletes_only_old_finished_tickets(): void
    {
        $old = QueueTicket::create([
            'organization_id' => $this->organization->id,
            'number' => 'A-001',
            'service_type' => 'pickup',
            'status' => 'done',
            'issued_at' => now()->subDays(10),
            'service_date' => now()->subDays(10)->toDateString(),
        ]);

        $oldWaiting = QueueTicket::create([
            'organization_id' => $this->organization->id,
            'number' => 'A-002',
            'service_type' => 'pickup',
            'status' => 'waiting',
            'issued_at' => now()->subDays(10),
            'service_date' => now()->subDays(10)->toDateString(),
        ]);

        $today = $this->queue->issue($this->organization->id, 'pickup');

        $this->assertSame(1, $this->queue->prune(7));
        $this->assertDatabaseMissing('queue_tickets', ['id' => $old->id]);
        $this->assertDatabaseHas('queue_tickets', ['id' => $oldWaiting->id]);
        $this->assertDatabaseHas('queue_tickets', ['id' => $today->id]);
    }
}
