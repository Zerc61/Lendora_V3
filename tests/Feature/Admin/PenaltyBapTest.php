<?php

namespace Tests\Feature\Admin;

use App\Models\Asset;
use App\Models\Booking;
use App\Models\BookingItem;
use App\Models\Category;
use App\Models\Organization;
use App\Models\Penalty;
use App\Models\User;
use Database\Seeders\AdminSeeder;
use Database\Seeders\OrganizationSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PenaltyBapTest extends TestCase
{
    use RefreshDatabase;

    private Organization $organization;

    private User $operator;

    private User $borrower;

    private Booking $booking;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([RoleSeeder::class, OrganizationSeeder::class, AdminSeeder::class]);

        $this->organization = Organization::where('code', 'LDR')->firstOrFail();
        $this->operator = User::where('email', 'operator@lendora.test')->firstOrFail();
        $this->borrower = User::where('email', 'borrower1@lendora.test')->firstOrFail();
        $this->booking = $this->booking();
    }

    public function test_operator_opens_bap_pages(): void
    {
        $this->actingAs($this->operator)
            ->get(route('admin.penalties.index'))
            ->assertOk()
            ->assertSee('BAP &amp; Denda', false)
            ->assertSee(__('ui.penalty.total_bap'))
            ->assertSee(__('ui.penalty.outstanding'));

        $this->actingAs($this->operator)
            ->get(route('admin.penalties.create'))
            ->assertOk()
            ->assertSee($this->booking->code)
            ->assertSee(__('ui.penalty.chronology'))
            ->assertSee('name="items[0][description]"', false)
            ->assertSee('data-dynamic-rows', false)
            ->assertSee('name="photos[]"', false);
    }

    public function test_operator_creates_and_issues_bap(): void
    {
        $this->actingAs($this->operator)
            ->post(route('admin.penalties.store'), $this->payload())
            ->assertRedirect();

        $penalty = Penalty::firstOrFail();

        $this->assertSame('LDR-BAP-'.now()->year.'-0001', $penalty->bap_number);
        $this->assertSame(2_750_000, $penalty->total_amount);
        $this->assertDatabaseHas('penalty_items', [
            'penalty_id' => $penalty->id,
            'part_code' => 'PART-MNT',
            'quantity' => 2,
        ]);

        $this->actingAs($this->operator)
            ->get(route('admin.penalties.show', $penalty))
            ->assertOk()
            ->assertSee($penalty->bap_number)
            ->assertSee(__('ui.penalty.summary_title'))
            ->assertSee(__('ui.penalty.payment_history'))
            ->assertSee(__('ui.penalty.issue_action'));

        $this->actingAs($this->operator)
            ->get(route('admin.penalties.print', $penalty))
            ->assertOk()
            ->assertSee('Berita Acara Perbaikan')
            ->assertSee($penalty->bap_number)
            ->assertSee(__('ui.penalty.print_hint'));

        $this->actingAs($this->operator)
            ->post(route('admin.penalties.issue', $penalty))
            ->assertRedirect(route('admin.penalties.show', $penalty));

        $this->assertNotNull($penalty->fresh()->issued_at);
        $this->assertSame('suspended', $this->borrower->fresh()->status);
    }

    public function test_added_item_recalculates_bap_total(): void
    {
        $this->actingAs($this->operator)
            ->post(route('admin.penalties.store'), $this->payload())
            ->assertRedirect();

        $penalty = Penalty::firstOrFail();
        $before = $penalty->total_amount;

        $this->actingAs($this->operator)
            ->post(route('admin.penalties.items.store', $penalty), [
                'description' => 'Ganti LCD',
                'part_code' => 'PART-LCD',
                'quantity' => 1,
                'unit_cost' => 500_000,
            ])
            ->assertRedirect();

        $this->assertSame($before + 450_000, $penalty->fresh()->total_amount);
    }

    public function test_bap_photos_are_stored(): void
    {
        Storage::fake('public');

        $this->actingAs($this->operator)
            ->post(route('admin.penalties.store'), $this->payload([
                'photos' => [UploadedFile::fake()->image('damage.jpg')],
            ]))
            ->assertRedirect();

        $penalty = Penalty::firstOrFail();

        $this->assertCount(1, $penalty->photo_paths);
        Storage::disk('public')->assertExists($penalty->photo_paths[0]);
    }

    public function test_bap_with_wrong_borrower_is_rejected(): void
    {
        $other = User::where('email', 'borrower2@lendora.test')->firstOrFail();

        $this->actingAs($this->operator)
            ->post(route('admin.penalties.store'), $this->payload(['borrower_id' => $other->id]))
            ->assertSessionHasErrors('borrower_id');

        $this->assertSame(0, Penalty::count());
    }

    public function test_borrower_cannot_access_bap_admin_pages(): void
    {
        $this->actingAs($this->borrower)
            ->get(route('admin.penalties.index'))
            ->assertForbidden();
    }

    private function payload(array $overrides = []): array
    {
        return $overrides + [
            'booking_id' => $this->booking->id,
            'asset_id' => $this->booking->items->first()->asset_id,
            'borrower_id' => $this->borrower->id,
            'incident_at' => now()->subDay()->format('Y-m-d\TH:i'),
            'inspector_name' => 'Budi Santoso',
            'damage_description' => 'Mount bengkok.',
            'case_category' => 'Kerusakan ringan',
            'chronology' => 'Unit terjatuh.',
            'payment_terms_days' => 7,
            'subsidy_percent' => 10,
            'admin_fee' => 50_000,
            'items' => [
                ['description' => 'Mount ring', 'part_code' => 'PART-MNT', 'quantity' => 2, 'unit_cost' => 1_000_000],
                ['description' => 'Jasa kalibrasi', 'quantity' => 1, 'unit_cost' => 1_000_000],
            ],
        ];
    }

    private function booking(): Booking
    {
        $category = Category::create([
            'organization_id' => $this->organization->id,
            'name' => 'Kamera Mirrorless',
            'slug' => 'kamera-mirrorless',
        ]);

        $asset = Asset::create([
            'organization_id' => $this->organization->id,
            'category_id' => $category->id,
            'code' => 'LDR-CAM-9003',
            'name' => 'Kamera Uji',
            'status' => 'maintenance',
        ]);

        $booking = Booking::create([
            'code' => 'LDR-BK-2026-9003',
            'organization_id' => $this->organization->id,
            'borrower_id' => $this->borrower->id,
            'status' => 'returned',
            'purpose' => 'Uji BAP',
            'requested_start_date' => now()->subDays(5)->toDateString(),
            'requested_end_date' => now()->subDays(2)->toDateString(),
        ]);

        BookingItem::create([
            'booking_id' => $booking->id,
            'asset_id' => $asset->id,
            'status' => 'damaged',
        ]);

        return $booking->fresh(['items']);
    }
}
