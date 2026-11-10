<?php

namespace Tests\Feature\Admin;

use App\Enums\BookingStatus;
use App\Models\Asset;
use App\Models\Booking;
use App\Models\BookingItem;
use App\Models\Category;
use App\Models\Organization;
use App\Models\QueueTicket;
use App\Models\ShiftNote;
use App\Models\User;
use Database\Seeders\AdminSeeder;
use Database\Seeders\OrganizationSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class KioskTest extends TestCase
{
    use RefreshDatabase;

    private Organization $organization;

    private User $operator;

    private User $borrower;

    private Booking $booking;

    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();
        $this->seed([RoleSeeder::class, OrganizationSeeder::class, AdminSeeder::class]);

        $this->organization = Organization::where('code', 'LDR')->firstOrFail();
        $this->operator = User::where('email', 'operator@lendora.test')->firstOrFail();
        $this->borrower = User::where('email', 'borrower1@lendora.test')->firstOrFail();
        $this->booking = $this->makeBooking();
    }

    public function test_operator_opens_scanner_and_queue_pages(): void
    {
        $this->actingAs($this->operator)
            ->get(route('admin.kiosk.index'))
            ->assertOk()
            ->assertSee('Kiosk Scanner')
            ->assertSee('data-admin-shell', false)
            ->assertSee('ldr-admin__sidebar', false)
            ->assertSee('data-kiosk-scan', false)
            ->assertSee(__('ui.kiosk.stat_waiting'));

        $this->actingAs($this->operator)
            ->get(route('admin.kiosk.queue'))
            ->assertOk()
            ->assertSee('Antrean Loket')
            ->assertSee('data-queue-poll="30"', false)
            ->assertSee('Panggil berikutnya');
    }

    public function test_lookup_stores_booking_in_session(): void
    {
        $this->actingAs($this->operator)
            ->post(route('admin.kiosk.lookup'), ['query' => $this->booking->code])
            ->assertRedirect(route('admin.kiosk.index'))
            ->assertSessionHas('kiosk.booking_id', $this->booking->id);

        $this->actingAs($this->operator)
            ->withSession([
                'kiosk.booking_id' => $this->booking->id,
                'kiosk.type' => 'pickup',
            ])
            ->get(route('admin.kiosk.index'))
            ->assertOk()
            ->assertSee($this->booking->code)
            ->assertSee('data-signature="borrower_signature"', false)
            ->assertSee('data-signature-input="operator_signature"', false)
            ->assertSee(__('ui.kiosk.confirm'), false);
    }

    public function test_lookup_unknown_code_returns_validation_error(): void
    {
        $this->actingAs($this->operator)
            ->from(route('admin.kiosk.index'))
            ->post(route('admin.kiosk.lookup'), ['query' => 'TIDAK-ADA'])
            ->assertRedirect(route('admin.kiosk.index'))
            ->assertSessionHasErrors('query');
    }

    public function test_process_pickup_activates_booking(): void
    {
        $item = $this->booking->items()->firstOrFail();

        $this->actingAs($this->operator)
            ->post(route('admin.kiosk.process'), [
                'booking_id' => $this->booking->id,
                'type' => 'pickup',
                'conditions' => [$item->id => 'good'],
                'checklist' => ['sensor_clean' => true],
                'shift_label' => 'Pagi',
            ])
            ->assertRedirect(route('admin.kiosk.index'));

        $this->assertSame(BookingStatus::Active, $this->booking->fresh()->status);
        $this->assertDatabaseHas('handovers', [
            'booking_id' => $this->booking->id,
            'type' => 'pickup',
        ]);
    }

    public function test_process_rejects_booking_from_other_organization(): void
    {
        $this->actingAs($this->operator)
            ->post(route('admin.kiosk.process'), [
                'booking_id' => 99999,
                'type' => 'pickup',
                'conditions' => [1 => 'good'],
            ])
            ->assertSessionHasErrors('booking_id');
    }

    public function test_queue_call_and_serve_ticket(): void
    {
        $ticket = QueueTicket::create([
            'organization_id' => $this->organization->id,
            'number' => 'A-001',
            'service_type' => 'pickup',
            'status' => 'waiting',
            'counter' => 'A',
            'issued_at' => now(),
            'service_date' => now()->toDateString(),
        ]);

        $this->actingAs($this->operator)
            ->post(route('admin.kiosk.queue.call'), ['counter' => 'A'])
            ->assertRedirect();

        $this->assertSame('called', $ticket->fresh()->status->value);

        $this->actingAs($this->operator)
            ->post(route('admin.kiosk.queue.serve', $ticket))
            ->assertRedirect();

        $this->assertSame('done', $ticket->fresh()->status->value);
    }

    public function test_operator_stores_shift_note(): void
    {
        $this->actingAs($this->operator)
            ->post(route('admin.shifts.notes.store'), ['note' => 'Dry box stabil.'])
            ->assertRedirect();

        $this->assertDatabaseHas('shift_notes', [
            'organization_id' => $this->organization->id,
            'user_id' => $this->operator->id,
            'note' => 'Dry box stabil.',
        ]);

        $this->assertSame(1, ShiftNote::count());
    }

    public function test_borrower_cannot_open_kiosk(): void
    {
        $this->actingAs($this->borrower)
            ->get(route('admin.kiosk.index'))
            ->assertForbidden();
    }

    private function makeBooking(): Booking
    {
        $category = Category::create([
            'organization_id' => $this->organization->id,
            'name' => 'Kamera Mirrorless',
            'slug' => 'kamera-mirrorless',
        ]);

        $asset = Asset::create([
            'organization_id' => $this->organization->id,
            'category_id' => $category->id,
            'code' => 'LDR-CAM-9001',
            'name' => 'Kamera Uji',
            'status' => 'reserved',
        ]);

        $booking = Booking::create([
            'code' => 'LDR-BK-2026-9001',
            'organization_id' => $this->organization->id,
            'borrower_id' => $this->borrower->id,
            'status' => 'approved',
            'purpose' => 'Uji kiosk',
            'requested_start_date' => now()->toDateString(),
            'requested_end_date' => now()->addDays(2)->toDateString(),
        ]);

        BookingItem::create([
            'booking_id' => $booking->id,
            'asset_id' => $asset->id,
            'status' => 'approved',
        ]);

        return $booking->fresh(['items.asset']);
    }
}
