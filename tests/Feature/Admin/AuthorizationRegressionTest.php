<?php

namespace Tests\Feature\Admin;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\Organization;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\AdminSeeder;
use Database\Seeders\OrganizationSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthorizationRegressionTest extends TestCase
{
    use RefreshDatabase;

    private Organization $organization;

    private User $borrower;

    private User $technician;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([RoleSeeder::class, OrganizationSeeder::class, AdminSeeder::class]);

        $this->borrower = User::where('email', 'borrower1@lendora.test')->firstOrFail();
        $this->technician = User::where('email', 'technician@lendora.test')->firstOrFail();
        $this->organization = $this->borrower->organization;
    }

    public function test_technician_cannot_approve_booking(): void
    {
        $booking = Booking::create([
            'code' => 'LDR-BK-TEST-0001',
            'organization_id' => $this->organization->id,
            'borrower_id' => $this->borrower->id,
            'status' => BookingStatus::Pending,
            'purpose' => 'Uji otorisasi',
            'requested_start_date' => Carbon::parse('2026-12-01'),
            'requested_end_date' => Carbon::parse('2026-12-03'),
        ]);

        $this->actingAs($this->technician)
            ->post(route('admin.bookings.approve', $booking))
            ->assertForbidden();

        $this->assertSame(BookingStatus::Pending, $booking->fresh()->status);
    }

    public function test_borrower_cannot_open_user_management(): void
    {
        $this->actingAs($this->borrower)
            ->get('/admin/users')
            ->assertForbidden();
    }

    public function test_operator_cannot_manage_users_or_settings(): void
    {
        $operator = User::where('email', 'operator@lendora.test')->firstOrFail();

        $this->actingAs($operator)->get(route('admin.users.index'))->assertForbidden();
        $this->actingAs($operator)->get(route('admin.settings.edit'))->assertForbidden();
    }

    public function test_admin_prodi_cannot_open_bap_or_kiosk(): void
    {
        $prodi = User::where('email', 'adminprodi@lendora.test')->firstOrFail();

        $this->actingAs($prodi)->get(route('admin.penalties.index'))->assertForbidden();
        $this->actingAs($prodi)->get(route('admin.kiosk.index'))->assertForbidden();
    }

    public function test_admin_prodi_does_not_see_actions_it_cannot_perform(): void
    {
        $prodi = User::where('email', 'adminprodi@lendora.test')->firstOrFail();
        $booking = Booking::create([
            'code' => 'LDR-BK-TEST-0002',
            'organization_id' => $this->organization->id,
            'borrower_id' => $this->borrower->id,
            'status' => BookingStatus::Pending,
            'purpose' => 'Uji tautan terlarang',
            'requested_start_date' => Carbon::parse('2026-12-01'),
            'requested_end_date' => Carbon::parse('2026-12-03'),
        ]);

        $this->actingAs($prodi)->get(route('admin.assets.index'))
            ->assertOk()
            ->assertDontSee(route('admin.assets.export', absolute: false), false);

        $this->actingAs($prodi)->get(route('admin.bookings.index'))
            ->assertOk()
            ->assertDontSee(route('admin.bookings.calendar', absolute: false), false);

        $this->actingAs($prodi)->get(route('admin.bookings.show', $booking))
            ->assertOk()
            ->assertDontSee(route('admin.penalties.create', ['booking_id' => $booking->id], absolute: false), false);
    }

    public function test_technician_can_open_own_workspace(): void
    {
        $this->actingAs($this->technician)
            ->get('/admin')
            ->assertOk();

        $this->actingAs($this->technician)
            ->get(route('admin.maintenances.index'))
            ->assertOk();
    }
}
