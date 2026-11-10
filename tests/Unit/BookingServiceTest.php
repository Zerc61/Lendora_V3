<?php

namespace Tests\Unit;

use App\Enums\AssetStatus;
use App\Enums\BookingStatus;
use App\Exceptions\InvalidStatusTransitionException;
use App\Models\Asset;
use App\Models\Booking;
use App\Models\Category;
use App\Models\Organization;
use App\Models\User;
use App\Notifications\BookingDecisionNotification;
use App\Notifications\BookingReceivedNotification;
use App\Services\BookingService;
use Carbon\Carbon;
use Database\Seeders\AdminSeeder;
use Database\Seeders\OrganizationSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class BookingServiceTest extends TestCase
{
    use RefreshDatabase;

    private Organization $organization;

    private User $borrower;

    private User $operator;

    private User $admin;

    private User $prodi;

    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([RoleSeeder::class, OrganizationSeeder::class, AdminSeeder::class]);

        $this->borrower = User::where('email', 'borrower1@lendora.test')->firstOrFail();
        $this->operator = User::where('email', 'operator@lendora.test')->firstOrFail();
        $this->admin = User::where('email', 'admin@lendora.test')->firstOrFail();
        $this->prodi = User::where('email', 'adminprodi@lendora.test')->firstOrFail();
        $this->organization = $this->borrower->organization;

        $this->category = Category::create([
            'organization_id' => $this->organization->id,
            'name' => 'Kamera Mirrorless',
            'slug' => 'kamera-mirrorless',
        ]);
    }

    public function test_create_request_creates_pending_booking_with_items_and_code(): void
    {
        Notification::fake();

        $asset = $this->asset();

        $booking = $this->service()->createRequest(
            $this->borrower,
            [$asset->id],
            Carbon::parse('2026-12-01'),
            Carbon::parse('2026-12-03'),
            'Kegiatan dokumentasi',
        );

        $this->assertSame(BookingStatus::Pending, $booking->status);
        $this->assertMatchesRegularExpression('/^LDR-BK-2026-\d{4}$/', $booking->code);
        $this->assertCount(1, $booking->items);
        $this->assertSame($asset->id, $booking->items->first()->asset_id);

        Notification::assertSentTo($this->borrower, BookingReceivedNotification::class);
    }

    public function test_create_request_rejects_duration_above_max_loan_days(): void
    {
        $this->expectException(ValidationException::class);

        $this->service()->createRequest(
            $this->borrower,
            [$this->asset()->id],
            Carbon::parse('2026-12-01'),
            Carbon::parse('2026-12-20'),
            'Terlalu lama',
        );
    }

    public function test_operator_approve_reserves_assets_and_notifies_borrower(): void
    {
        Notification::fake();

        $asset = $this->asset();
        $booking = $this->pendingBooking($asset);

        $approved = $this->service()->approve($booking, $this->operator);

        $this->assertSame(BookingStatus::Approved, $approved->status);
        $this->assertSame($this->operator->id, $approved->approved_by);
        $this->assertNotNull($approved->approved_at);
        $this->assertSame('16:00', $approved->due_at->format('H:i'));
        $this->assertSame(AssetStatus::Reserved, $asset->fresh()->status);
        $this->assertSame('approved', $approved->items->first()->status->value);
        $this->assertNotEmpty($approved->return_token);

        Notification::assertSentTo($this->borrower, BookingDecisionNotification::class);
    }

    public function test_ensure_return_token_fills_missing_token_once(): void
    {
        $booking = $this->pendingBooking($this->asset());
        $this->assertNull($booking->return_token);

        $filled = $this->service()->ensureReturnToken($booking);

        $this->assertNotEmpty($filled->return_token);
        $this->assertSame($filled->return_token, $this->service()->ensureReturnToken($booking->fresh())->return_token);
    }

    public function test_reject_requires_reason_and_keeps_assets_available(): void
    {
        Notification::fake();

        $asset = $this->asset();
        $booking = $this->pendingBooking($asset);

        try {
            $this->service()->reject($booking, $this->operator, '');
            $this->fail('Reject without reason must fail.');
        } catch (ValidationException) {
            // expected
        }

        $rejected = $this->service()->reject($booking, $this->operator, 'Unit sedang dirawat.');

        $this->assertSame(BookingStatus::Rejected, $rejected->status);
        $this->assertSame('Unit sedang dirawat.', $rejected->rejection_reason);
        $this->assertSame(AssetStatus::Available, $asset->fresh()->status);

        Notification::assertSentTo($this->borrower, BookingDecisionNotification::class);
    }

    public function test_borrower_can_cancel_pending_booking(): void
    {
        $booking = $this->pendingBooking($this->asset());

        $cancelled = $this->service()->cancel($booking, $this->borrower);

        $this->assertSame(BookingStatus::Cancelled, $cancelled->status);
    }

    public function test_operator_cancelling_approved_booking_releases_asset(): void
    {
        $asset = $this->asset();
        $booking = $this->service()->approve($this->pendingBooking($asset), $this->operator);

        $cancelled = $this->service()->cancel($booking, $this->operator);

        $this->assertSame(BookingStatus::Cancelled, $cancelled->status);
        $this->assertSame(AssetStatus::Available, $asset->fresh()->status);
    }

    public function test_illegal_transition_throws(): void
    {
        $booking = $this->pendingBooking($this->asset());
        $booking->update(['status' => BookingStatus::Cancelled]);

        $this->expectException(InvalidStatusTransitionException::class);

        $this->service()->approve($booking, $this->admin);
    }

    public function test_operator_cannot_approve_above_value_limit(): void
    {
        $asset = $this->asset(purchasePrice: 6_000_000);
        $booking = $this->pendingBooking($asset);

        $this->expectException(AuthorizationException::class);

        $this->service()->approve($booking, $this->operator);
    }

    public function test_admin_prodi_only_approves_long_duration_requests(): void
    {
        $short = $this->pendingBooking($this->asset(), duration: 3);

        try {
            $this->service()->approve($short, $this->prodi);
            $this->fail('Admin Prodi must not approve a short request.');
        } catch (AuthorizationException) {
            // expected
        }

        $long = $this->pendingBooking($this->asset(), duration: 10);
        $approved = $this->service()->approve($long, $this->prodi);

        $this->assertSame(BookingStatus::Approved, $approved->status);
    }

    public function test_organization_admin_approves_any_value(): void
    {
        $asset = $this->asset(purchasePrice: 9_000_000);
        $booking = $this->pendingBooking($asset);

        $approved = $this->service()->approve($booking, $this->admin);

        $this->assertSame(BookingStatus::Approved, $approved->status);
    }

    public function test_timeline_lists_created_and_approved_events(): void
    {
        $booking = $this->service()->approve($this->pendingBooking($this->asset()), $this->operator);

        $timeline = $this->service()->timelineFor($booking->load(['borrower', 'approvedBy']));

        $this->assertSame(BookingStatus::Pending->value, $timeline[0]['status']);
        $this->assertSame(BookingStatus::Approved->value, $timeline[1]['status']);
        $this->assertSame($this->operator->name, $timeline[1]['actor']);
    }

    private function service(): BookingService
    {
        return app(BookingService::class);
    }

    private function asset(int $purchasePrice = 1_000_000): Asset
    {
        return Asset::create([
            'organization_id' => $this->organization->id,
            'category_id' => $this->category->id,
            'code' => 'LDR-CAM-'.fake()->unique()->numerify('####'),
            'name' => 'Kamera Uji',
            'status' => AssetStatus::Available,
            'condition_status' => 'good',
            'purchase_price' => $purchasePrice,
        ]);
    }

    private function pendingBooking(Asset $asset, int $duration = 3): Booking
    {
        $start = Carbon::parse('2026-12-01');

        $booking = Booking::create([
            'code' => 'LDR-BK-TEST-'.fake()->unique()->numerify('####'),
            'organization_id' => $this->organization->id,
            'borrower_id' => $this->borrower->id,
            'status' => BookingStatus::Pending,
            'purpose' => 'Uji persetujuan',
            'requested_start_date' => $start,
            'requested_end_date' => $start->copy()->addDays($duration),
        ]);

        $booking->items()->create([
            'asset_id' => $asset->id,
            'status' => 'pending',
        ]);

        return $booking->load('items.asset');
    }
}
