<?php

namespace Tests\Feature\Borrower;

use App\Enums\AssetStatus;
use App\Enums\BookingStatus;
use App\Models\Asset;
use App\Models\Booking;
use App\Models\Category;
use App\Models\Course;
use App\Models\Organization;
use App\Models\User;
use App\Services\BookingService;
use Carbon\Carbon;
use Database\Seeders\AdminSeeder;
use Database\Seeders\OrganizationSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingFlowTest extends TestCase
{
    use RefreshDatabase;

    private Organization $organization;

    private User $borrower;

    private User $otherBorrower;

    private User $operator;

    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([RoleSeeder::class, OrganizationSeeder::class, AdminSeeder::class]);

        $this->borrower = User::where('email', 'borrower1@lendora.test')->firstOrFail();
        $this->otherBorrower = User::where('email', 'borrower2@lendora.test')->firstOrFail();
        $this->operator = User::where('email', 'operator@lendora.test')->firstOrFail();
        $this->organization = $this->borrower->organization;

        $this->category = Category::create([
            'organization_id' => $this->organization->id,
            'name' => 'Kamera Mirrorless',
            'slug' => 'kamera-mirrorless',
        ]);
    }

    public function test_borrower_submits_conflict_free_request(): void
    {
        $asset = $this->asset();

        $response = $this->actingAs($this->borrower)->post(route('borrower.bookings.store'), [
            'assets' => [$asset->id],
            'purpose' => 'Dokumentasi kegiatan sekolah',
            'requested_start_date' => '2026-12-01',
            'requested_end_date' => '2026-12-04',
        ]);

        $booking = Booking::with('items')->firstOrFail();

        $response->assertRedirect(route('borrower.bookings.show', $booking));
        $this->assertSame(BookingStatus::Pending, $booking->status);
        $this->assertSame($this->borrower->id, $booking->borrower_id);
        $this->assertMatchesRegularExpression('/^LDR-BK-2026-\d{4}$/', $booking->code);
        $this->assertCount(1, $booking->items);
        $this->assertSame($asset->id, $booking->items->first()->asset_id);
    }

    public function test_borrower_can_attach_course_to_request(): void
    {
        $asset = $this->asset();
        $course = $this->course(['quota_hours' => 120]);

        $this->actingAs($this->borrower)->post(route('borrower.bookings.store'), [
            'assets' => [$asset->id],
            'purpose' => 'Tugas sinematografi',
            'requested_start_date' => '2026-12-01',
            'requested_end_date' => '2026-12-04',
            'course_id' => $course->id,
        ])->assertRedirect();

        $this->assertSame($course->id, Booking::firstOrFail()->course_id);
    }

    public function test_borrower_cannot_attach_inactive_course(): void
    {
        $asset = $this->asset();
        $course = $this->course(['is_active' => false]);

        $this->actingAs($this->borrower)->post(route('borrower.bookings.store'), [
            'assets' => [$asset->id],
            'purpose' => 'Tugas sinematografi',
            'requested_start_date' => '2026-12-01',
            'requested_end_date' => '2026-12-04',
            'course_id' => $course->id,
        ])->assertSessionHasErrors('course_id');

        $this->assertSame(0, Booking::count());
    }

    public function test_borrower_cannot_view_or_cancel_another_borrowers_booking(): void
    {
        $booking = $this->pendingBooking($this->otherBorrower, $this->asset());

        $this->actingAs($this->borrower)
            ->get(route('borrower.bookings.show', $booking))
            ->assertForbidden();

        $this->actingAs($this->borrower)
            ->post(route('borrower.bookings.cancel', $booking))
            ->assertForbidden();

        $this->assertSame(BookingStatus::Pending, $booking->fresh()->status);
    }

    public function test_duration_above_max_loan_days_is_blocked_on_submit(): void
    {
        $asset = $this->asset();

        $response = $this->actingAs($this->borrower)->post(route('borrower.bookings.store'), [
            'assets' => [$asset->id],
            'purpose' => 'Terlalu lama',
            'requested_start_date' => '2026-12-01',
            'requested_end_date' => '2026-12-20',
        ]);

        $response->assertSessionHasErrors('requested_end_date');
        $this->assertStringContainsString('7 hari', session('errors')->first('requested_end_date'));
        $this->assertDatabaseCount('bookings', 0);
    }

    public function test_conflicting_request_is_rejected_with_code_and_suggestion(): void
    {
        $asset = $this->asset();
        $holder = $this->pendingBooking($this->borrower, $asset, '2026-12-01', '2026-12-04');
        app(BookingService::class)->approve($holder, $this->operator);

        $response = $this->actingAs($this->borrower)->post(route('borrower.bookings.store'), [
            'assets' => [$asset->id],
            'purpose' => 'Bentrok',
            'requested_start_date' => '2026-12-03',
            'requested_end_date' => '2026-12-05',
        ]);

        $response->assertSessionHasErrors('assets');

        $message = session('errors')->first('assets');
        $this->assertStringContainsString($holder->code, $message);
        $this->assertStringContainsString('Kamera Uji', $message);
        $this->assertDatabaseCount('bookings', 1);
    }

    public function test_borrower_cancels_own_pending_booking(): void
    {
        $booking = $this->pendingBooking($this->borrower, $this->asset());

        $this->actingAs($this->borrower)
            ->post(route('borrower.bookings.cancel', $booking))
            ->assertRedirect(route('borrower.bookings.show', $booking));

        $this->assertSame(BookingStatus::Cancelled, $booking->fresh()->status);
    }

    public function test_borrower_index_only_lists_own_bookings(): void
    {
        $own = $this->pendingBooking($this->borrower, $this->asset());
        $other = $this->pendingBooking($this->otherBorrower, $this->asset());

        $this->actingAs($this->borrower)
            ->get(route('borrower.bookings.index'))
            ->assertOk()
            ->assertSee($own->code)
            ->assertDontSee($other->code);
    }

    public function test_borrower_requests_an_extension_and_sees_it_pending(): void
    {
        $booking = $this->activeBooking($this->borrower, $this->asset());

        $this->actingAs($this->borrower)
            ->post(route('borrower.bookings.extension.request', $booking), [
                'new_end_date' => '2026-12-10',
                'reason' => 'Kegiatan tambahan',
            ])
            ->assertRedirect(route('borrower.bookings.show', $booking));

        $this->assertDatabaseHas('booking_extensions', [
            'booking_id' => $booking->id,
            'requested_by' => $this->borrower->id,
            'status' => 'pending',
            'reason' => 'Kegiatan tambahan',
        ]);

        $this->actingAs($this->borrower)
            ->get(route('borrower.bookings.show', $booking))
            ->assertOk()
            ->assertSee('Kegiatan tambahan')
            ->assertSee(route('borrower.bookings.extensions.cancel', [$booking, $booking->extensions()->first()]), false);

        $extension = $booking->extensions()->firstOrFail();

        $this->actingAs($this->borrower)
            ->post(route('borrower.bookings.extensions.cancel', [$booking, $extension]))
            ->assertRedirect(route('borrower.bookings.show', $booking));

        $this->assertSame('cancelled', $extension->fresh()->status->value);
    }

    public function test_extension_form_and_buttons_link_to_the_form_route(): void
    {
        $booking = $this->activeBooking($this->borrower, $this->asset());

        $this->actingAs($this->borrower)
            ->get(route('borrower.bookings.show', $booking))
            ->assertOk()
            ->assertSee(route('borrower.bookings.extension.form', $booking), false);

        $this->actingAs($this->borrower)
            ->get(route('borrower.bookings.extension.form', $booking))
            ->assertOk()
            ->assertSee(route('borrower.bookings.extension.request', $booking), false);
    }

    public function test_extension_is_rejected_when_the_asset_is_booked(): void
    {
        $asset = $this->asset();
        $booking = $this->activeBooking($this->borrower, $asset);

        $otherBooking = $this->pendingBooking($this->otherBorrower, $asset, '2026-12-06', '2026-12-09');
        app(BookingService::class)->approve($otherBooking, $this->operator);

        $this->actingAs($this->borrower)
            ->post(route('borrower.bookings.extension.request', $booking), [
                'new_end_date' => '2026-12-10',
            ])
            ->assertSessionHasErrors('new_end_date');

        $this->assertDatabaseCount('booking_extensions', 0);
    }

    public function test_borrower_cannot_extend_another_borrowers_booking(): void
    {
        $booking = $this->activeBooking($this->otherBorrower, $this->asset());

        $this->actingAs($this->borrower)
            ->get(route('borrower.bookings.extension.form', $booking))
            ->assertForbidden();

        $this->actingAs($this->borrower)
            ->post(route('borrower.bookings.extension.request', $booking), [
                'new_end_date' => '2026-12-10',
            ])
            ->assertForbidden();
    }

    private function activeBooking(User $borrower, Asset $asset): Booking
    {
        $booking = $this->pendingBooking($borrower, $asset, '2026-12-01', '2026-12-04');

        $booking->update([
            'status' => BookingStatus::Active,
            'pickup_at' => now(),
            'due_at' => Carbon::parse('2026-12-04'),
        ]);

        $booking->items()->update(['status' => 'picked_up']);

        return $booking;
    }

    private function course(array $attributes = []): Course
    {
        return Course::create([
            'organization_id' => $this->organization->id,
            'code' => 'DKV-'.fake()->unique()->numerify('###'),
            'name' => 'Fotografi Dasar',
            'lecturer_name' => 'Rina Kusuma, M.Ds.',
            'semester' => 3,
            'credits' => 3,
            'quota_hours' => 120,
            'is_active' => true,
            ...$attributes,
        ]);
    }

    private function asset(): Asset
    {
        return Asset::create([
            'organization_id' => $this->organization->id,
            'category_id' => $this->category->id,
            'code' => 'LDR-CAM-'.fake()->unique()->numerify('####'),
            'name' => 'Kamera Uji',
            'status' => AssetStatus::Available,
            'condition_status' => 'good',
            'purchase_price' => 1_000_000,
        ]);
    }

    private function pendingBooking(
        User $borrower,
        Asset $asset,
        string $start = '2026-12-01',
        string $end = '2026-12-04',
    ): Booking {
        $booking = Booking::create([
            'code' => 'LDR-BK-TEST-'.fake()->unique()->numerify('####'),
            'organization_id' => $this->organization->id,
            'borrower_id' => $borrower->id,
            'status' => BookingStatus::Pending,
            'purpose' => 'Uji alur',
            'requested_start_date' => Carbon::parse($start),
            'requested_end_date' => Carbon::parse($end),
        ]);

        $booking->items()->create([
            'asset_id' => $asset->id,
            'status' => 'pending',
        ]);

        return $booking;
    }
}
