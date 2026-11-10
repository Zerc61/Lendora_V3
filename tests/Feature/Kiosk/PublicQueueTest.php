<?php

namespace Tests\Feature\Kiosk;

use App\Models\Organization;
use Database\Seeders\AdminSeeder;
use Database\Seeders\OrganizationSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicQueueTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([RoleSeeder::class, OrganizationSeeder::class, AdminSeeder::class]);
    }

    public function test_kiosk_requires_device_token(): void
    {
        $this->get(route('kiosk.queue'))->assertForbidden();
    }

    public function test_valid_token_opens_kiosk_and_is_remembered_in_session(): void
    {
        $this->get(route('kiosk.queue', ['token' => config('lendora.kiosk_token')]))
            ->assertOk()
            ->assertSessionHas('kiosk.authorized', true)
            ->assertSee('data-queue-poll="20"', false)
            ->assertSee(__('ui.kiosk.take_ticket'))
            ->assertSee('name="service_type"', false);

        $this->get(route('kiosk.queue'))->assertOk();
    }

    public function test_kiosk_shows_issued_ticket(): void
    {
        $this->withSession([
            'kiosk.authorized' => true,
            'kiosk.ticket' => ['number' => 'A-009', 'service_type' => 'pickup', 'waiting' => 3],
        ])
            ->get(route('kiosk.queue'))
            ->assertOk()
            ->assertSee('A-009')
            ->assertSee(__('ui.kiosk.ticket_label'));
    }

    public function test_kiosk_issues_ticket_with_service_prefix(): void
    {
        $organization = Organization::where('code', 'LDR')->firstOrFail();

        $this->withSession(['kiosk.authorized' => true])
            ->post(route('kiosk.tickets.store'), [
                'nim' => '2205346089',
                'service_type' => 'return',
            ])
            ->assertRedirect(route('kiosk.queue'))
            ->assertSessionHas('kiosk.ticket');

        $this->assertDatabaseHas('queue_tickets', [
            'organization_id' => $organization->id,
            'number' => 'B-001',
            'service_type' => 'return',
            'status' => 'waiting',
        ]);
    }

    public function test_kiosk_rejects_invalid_service_type(): void
    {
        $this->withSession(['kiosk.authorized' => true])
            ->post(route('kiosk.tickets.store'), ['service_type' => 'lainnya'])
            ->assertSessionHasErrors('service_type');
    }
}
