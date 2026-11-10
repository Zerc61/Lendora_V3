<?php

namespace Tests\Feature\Admin;

use App\Enums\AssetStatus;
use App\Enums\BookingStatus;
use App\Models\Asset;
use App\Models\Booking;
use App\Models\Category;
use App\Models\Organization;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\AdminSeeder;
use Database\Seeders\OrganizationSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecommendationTest extends TestCase
{
    use RefreshDatabase;

    private Organization $organization;

    private User $prodi;

    private User $borrower;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([RoleSeeder::class, OrganizationSeeder::class, AdminSeeder::class]);

        $this->organization = Organization::where('code', 'LDR')->firstOrFail();
        $this->prodi = User::where('email', 'adminprodi@lendora.test')->firstOrFail();
        $this->borrower = User::where('email', 'borrower1@lendora.test')->firstOrFail();
    }

    public function test_prodi_sees_procurement_recommendation(): void
    {
        $category = Category::create([
            'organization_id' => $this->organization->id,
            'name' => 'Kamera Cinema',
            'slug' => 'kamera-cinema',
        ]);

        $asset = Asset::create([
            'organization_id' => $this->organization->id,
            'category_id' => $category->id,
            'code' => 'LDR-CAM-0001',
            'name' => 'Kamera Uji',
            'status' => AssetStatus::Available,
            'condition_status' => 'good',
            'purchase_price' => 5_000_000,
        ]);

        foreach ([BookingStatus::Approved, BookingStatus::Rejected] as $status) {
            $start = Carbon::parse('2026-09-01');

            $booking = Booking::create([
                'code' => 'LDR-BK-REC-'.fake()->unique()->numerify('####'),
                'organization_id' => $this->organization->id,
                'borrower_id' => $this->borrower->id,
                'status' => $status,
                'purpose' => 'Uji rekomendasi',
                'requested_start_date' => $start,
                'requested_end_date' => $start->copy()->addDay(),
            ]);

            $booking->items()->create([
                'asset_id' => $asset->id,
                'status' => 'pending',
            ]);
        }

        $this->actingAs($this->prodi)
            ->get(route('admin.recommendations.index'))
            ->assertOk()
            ->assertSee(__('ui.recommendation.title'))
            ->assertSee('Kamera Cinema')
            ->assertSee(__('ui.semester_report.priority_urgent'))
            ->assertSee('Rp 5.000.000');
    }

    public function test_page_shows_empty_state_without_rejected_requests(): void
    {
        $this->actingAs($this->prodi)
            ->get(route('admin.recommendations.index'))
            ->assertOk()
            ->assertSee(__('ui.recommendation.empty_title'));
    }

    public function test_non_prodi_roles_are_forbidden(): void
    {
        foreach (['operator@lendora.test', 'admin@lendora.test', 'technician@lendora.test'] as $email) {
            $this->actingAs(User::where('email', $email)->firstOrFail())
                ->get(route('admin.recommendations.index'))
                ->assertForbidden();
        }
    }
}
