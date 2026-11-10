<?php

namespace Tests\Feature\Admin;

use App\Enums\BookingStatus;
use App\Enums\PenaltyStatus;
use App\Models\Booking;
use App\Models\Organization;
use App\Models\Penalty;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\AdminSeeder;
use Database\Seeders\OrganizationSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentComplianceTest extends TestCase
{
    use RefreshDatabase;

    private Organization $organization;

    private User $prodi;

    private User $low;

    private User $high;

    private User $suspended;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([RoleSeeder::class, OrganizationSeeder::class, AdminSeeder::class]);

        $this->organization = Organization::where('code', 'LDR')->firstOrFail();
        $this->prodi = User::where('email', 'adminprodi@lendora.test')->firstOrFail();

        $this->low = User::where('email', 'borrower1@lendora.test')->firstOrFail();
        $this->low->update(['name' => 'Rara Sekar', 'nim' => '2207412089', 'compliance_score' => 54, 'study_program' => 'DKV']);

        $this->high = User::where('email', 'borrower2@lendora.test')->firstOrFail();
        $this->high->update(['name' => 'Dimas Arya', 'nim' => '2207411012', 'compliance_score' => 99]);

        $this->suspended = User::create([
            'name' => 'Fandi Ahmad',
            'email' => 'fandi@lendora.test',
            'password' => 'password',
            'role_id' => $this->low->role_id,
            'organization_id' => $this->organization->id,
            'status' => 'suspended',
            'compliance_score' => 68,
        ]);
    }

    public function test_prodi_sees_student_list_with_score_usage_and_bap(): void
    {
        $start = Carbon::parse('2026-10-01');
        Booking::create([
            'code' => 'LDR-BK-STU-0001',
            'organization_id' => $this->organization->id,
            'borrower_id' => $this->low->id,
            'status' => BookingStatus::Returned,
            'purpose' => 'Uji',
            'requested_start_date' => $start,
            'requested_end_date' => $start->copy()->addDays(2),
        ]);

        Penalty::create([
            'organization_id' => $this->organization->id,
            'borrower_id' => $this->low->id,
            'bap_number' => 'LDR-BAP-2026-0004',
            'incident_at' => $start,
            'inspector_name' => 'Petugas',
            'damage_description' => 'Cacat sensor kamera',
            'repair_subtotal' => 2_500_000,
            'total_amount' => 2_500_000,
            'status' => PenaltyStatus::WaitingPayment,
            'due_at' => $start->copy()->addDays(7),
        ]);

        $this->actingAs($this->prodi)
            ->get(route('admin.students.index'))
            ->assertOk()
            ->assertSee(__('ui.student.title'))
            ->assertSee('Rara Sekar')
            ->assertSee('2207412089')
            ->assertSee('Tier D')
            ->assertSee(__('ui.student.sessions', ['count' => 1]))
            ->assertSee(__('ui.student.days', ['days' => 3]))
            ->assertSee('Rp 2.500.000')
            ->assertSee(__('ui.student.unpaid', ['count' => 1]))
            ->assertSee(__('ui.user.suspended'));
    }

    public function test_sorted_lowest_score_first(): void
    {
        $response = $this->actingAs($this->prodi)->get(route('admin.students.index'));

        $response->assertOk();
        $this->assertLessThan(
            strpos($response->getContent(), 'Dimas Arya'),
            strpos($response->getContent(), 'Rara Sekar'),
        );
    }

    public function test_tier_filter_only_matches_range(): void
    {
        $this->actingAs($this->prodi)
            ->get(route('admin.students.index', ['tier' => 'c']))
            ->assertOk()
            ->assertSee('Rara Sekar')
            ->assertDontSee('Dimas Arya');
    }

    public function test_search_matches_nim(): void
    {
        $this->actingAs($this->prodi)
            ->get(route('admin.students.index', ['search' => '2207411012']))
            ->assertOk()
            ->assertSee('Dimas Arya')
            ->assertDontSee('Rara Sekar');
    }

    public function test_status_filter_lists_suspended_students(): void
    {
        $this->actingAs($this->prodi)
            ->get(route('admin.students.index', ['status' => 'suspended']))
            ->assertOk()
            ->assertSee('Fandi Ahmad')
            ->assertDontSee('Rara Sekar');
    }

    public function test_non_prodi_roles_are_forbidden(): void
    {
        foreach (['operator@lendora.test', 'admin@lendora.test', 'technician@lendora.test'] as $email) {
            $this->actingAs(User::where('email', $email)->firstOrFail())
                ->get(route('admin.students.index'))
                ->assertForbidden();
        }
    }
}
