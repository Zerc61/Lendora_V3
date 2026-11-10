<?php

namespace Tests\Feature\Borrower;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\PenaltyStatus;
use App\Models\Asset;
use App\Models\Booking;
use App\Models\Organization;
use App\Models\Payment;
use App\Models\Penalty;
use App\Models\Setting;
use App\Models\User;
use Database\Seeders\AdminSeeder;
use Database\Seeders\AssetComponentSeeder;
use Database\Seeders\BorrowerShowcaseSeeder;
use Database\Seeders\CategoryAndAssetSeeder;
use Database\Seeders\OrganizationSeeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\SettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UiRegressionTest extends TestCase
{
    use RefreshDatabase;

    private User $borrower;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        $this->seed([
            RoleSeeder::class,
            OrganizationSeeder::class,
            AdminSeeder::class,
            CategoryAndAssetSeeder::class,
            BorrowerShowcaseSeeder::class,
            SettingSeeder::class,
            AssetComponentSeeder::class,
        ]);

        $this->borrower = User::where('email', 'ani@lendora.test')->firstOrFail();
    }

    public function test_asset_detail_with_components_renders_condition_badge(): void
    {
        $asset = Asset::whereHas('components')->firstOrFail();

        $this->actingAs($this->borrower)
            ->get(route('borrower.catalog.show', $asset))
            ->assertOk()
            ->assertSee('Kelengkapan Bundle')
            ->assertSee('ldr-badge--condition-good', false)
            ->assertSee('Baik')
            ->assertDontSee('ui.', false);
    }

    public function test_asset_card_marks_available_assets_as_available(): void
    {
        $this->actingAs($this->borrower)
            ->get(route('borrower.home'))
            ->assertOk()
            ->assertSee('ldr-asset-card__cta--available', false)
            ->assertDontSee('0</strong> unit tersedia', false);
    }

    public function test_asset_card_keeps_badges_and_availability_inside_the_card_layout(): void
    {
        $response = $this->actingAs($this->borrower)
            ->get(route('borrower.catalog.index'))
            ->assertOk();

        $html = $response->getContent();

        $this->assertStringContainsString('ldr-asset-card__tags', $html);
        $this->assertStringContainsString('ldr-asset-card__availability-row', $html);
        $this->assertMatchesRegularExpression(
            '/ldr-asset-card__media[^>]*>(?:(?!<\/a>).)*ldr-asset-card__status/s',
            $html,
        );
        $this->assertStringContainsString('ldr-badge--grade', $html);
    }

    public function test_pending_booking_exposes_cancel_action(): void
    {
        $pending = Booking::where('borrower_id', $this->borrower->id)->where('status', 'pending')->firstOrFail();

        $this->actingAs($this->borrower)
            ->get(route('borrower.bookings.index'))
            ->assertOk()
            ->assertSee(route('borrower.bookings.cancel', $pending), false)
            ->assertSee('Batalkan Pengajuan');
    }

    public function test_notifications_page_uses_translated_labels_and_single_empty_state(): void
    {
        $response = $this->actingAs($this->borrower)
            ->get(route('borrower.notifications.index'))
            ->assertOk()
            ->assertSee('Ringkasan')
            ->assertSee('Skor Kepatuhan')
            ->assertDontSee('ui.notification.', false)
            ->assertDontSee('>today<', false)
            ->assertDontSee('>earlier<', false);

        $this->assertSame(1, substr_count($response->getContent(), 'Tidak ada notifikasi'));
    }

    public function test_notification_categories_are_translated_and_filterable(): void
    {
        foreach (['booking_received', 'custom_event'] as $index => $category) {
            $this->borrower->notifications()->create([
                'id' => (string) \Illuminate\Support\Str::uuid(),
                'type' => 'App\\Notifications\\BookingDecisionNotification',
                'data' => ['title' => 'Pesan '.$index, 'category' => $category],
                'read_at' => null,
            ]);
        }

        $this->actingAs($this->borrower)
            ->get(route('borrower.notifications.index'))
            ->assertOk()
            ->assertSee('Pengajuan peminjaman diterima')
            ->assertSee('Custom event')
            ->assertDontSee('ui.notification.', false);

        $this->actingAs($this->borrower)
            ->get(route('borrower.notifications.index', ['category' => 'booking_received']))
            ->assertOk()
            ->assertSee('Pesan 0')
            ->assertDontSee('Pesan 1');
    }

    public function test_booking_tabs_use_the_styled_classes(): void
    {
        $this->actingAs($this->borrower)
            ->get(route('borrower.bookings.index'))
            ->assertOk()
            ->assertSee('ldr-status-tabs__tab', false)
            ->assertDontSee('class="ldr-status-tab ', false);
    }

    public function test_penalty_pages_render_with_translated_status(): void
    {
        $penalty = $this->makePenalty();

        foreach ([
            route('borrower.penalties.index'),
            route('borrower.penalties.show', $penalty),
            route('borrower.penalties.document', [$penalty, 'bap']),
            route('borrower.penalties.document', [$penalty, 'receipt']),
        ] as $url) {
            $this->actingAs($this->borrower)
                ->get($url)
                ->assertOk()
                ->assertSee('Menunggu Pembayaran')
                ->assertDontSee('ui.penalty.', false)
                ->assertDontSee('ui.status.', false);
        }
    }

    public function test_borrower_documents_download_as_pdf(): void
    {
        $booking = Booking::where('borrower_id', $this->borrower->id)->firstOrFail();
        $penalty = $this->makePenalty();

        foreach ([
            route('borrower.bookings.document', ['booking' => $booking, 'type' => 'letter']).'?format=pdf',
            route('borrower.bookings.return-pass', $booking).'?format=pdf',
            route('borrower.penalties.document', ['penalty' => $penalty, 'type' => 'bap']).'?format=pdf',
        ] as $url) {
            $this->actingAs($this->borrower)
                ->get($url)
                ->assertOk()
                ->assertHeader('content-type', 'application/pdf')
                ->assertDownload();
        }
    }

    public function test_payment_detail_page_renders_enum_labels(): void
    {
        $penalty = $this->makePenalty();

        $payment = Payment::create([
            'penalty_id' => $penalty->id,
            'borrower_id' => $this->borrower->id,
            'method' => PaymentMethod::VaBni,
            'amount' => 1_000_000,
            'status' => PaymentStatus::WaitingVerification,
        ]);

        $this->actingAs($this->borrower)
            ->get(route('borrower.payments.show', $payment))
            ->assertOk()
            ->assertSee('Virtual Account BNI')
            ->assertSee('Menunggu Verifikasi')
            ->assertDontSee('ui.payment.', false)
            ->assertDontSee('ui.status.', false);
    }

    public function test_two_factor_page_renders(): void
    {
        $this->actingAs($this->borrower)
            ->get(route('borrower.two-factor.edit'))
            ->assertOk()
            ->assertSee('Keamanan Akun')
            ->assertDontSee('ui.profile.', false);
    }

    public function test_catalog_favorite_toggle_works(): void
    {
        $asset = Asset::firstOrFail();

        $this->actingAs($this->borrower)
            ->get(route('borrower.catalog.index'))
            ->assertOk()
            ->assertSee('data-favorite-toggle', false)
            ->assertSee('data-asset="'.$asset->id.'"', false);

        $this->actingAs($this->borrower)
            ->postJson(route('borrower.catalog.favorite', $asset))
            ->assertOk()
            ->assertJson(['favorited' => true]);

        $this->assertDatabaseHas('asset_favorites', [
            'user_id' => $this->borrower->id,
            'asset_id' => $asset->id,
        ]);

        $this->actingAs($this->borrower)
            ->postJson(route('borrower.catalog.favorite', $asset))
            ->assertOk()
            ->assertJson(['favorited' => false]);

        $this->assertDatabaseMissing('asset_favorites', [
            'user_id' => $this->borrower->id,
            'asset_id' => $asset->id,
        ]);
    }

    public function test_catalog_check_availability_targets_the_calendar(): void
    {
        $asset = Asset::where('status', 'available')->firstOrFail();

        $this->actingAs($this->borrower)
            ->get(route('borrower.catalog.show', $asset))
            ->assertOk()
            ->assertSee('href="#asset-availability"', false)
            ->assertSee('id="asset-availability"', false);
    }

    public function test_booking_progress_dots_are_cumulative(): void
    {
        $response = $this->actingAs($this->borrower)
            ->get(route('borrower.bookings.index'))
            ->assertOk();

        // Seeded: pending (1) + active (3) + returned (4) dots.
        $this->assertSame(8, substr_count($response->getContent(), 'ldr-booking-card__dot is-done'));
    }

    public function test_notification_contact_uses_settings(): void
    {
        Setting::where('key', 'operator_contact_phone')->update(['value' => 'Ext. 999']);
        Cache::flush();

        $this->actingAs($this->borrower)
            ->get(route('borrower.notifications.index'))
            ->assertOk()
            ->assertSee('Ext. 999')
            ->assertDontSee('$operatorInfo', false);
    }

    public function test_return_pass_exposes_qr_token_to_the_client_renderer(): void
    {
        $booking = Booking::where('borrower_id', $this->borrower->id)->firstOrFail();
        $booking->update(['return_token' => 'TEST-RETURN-TOKEN']);

        $this->actingAs($this->borrower)
            ->get(route('borrower.bookings.return-pass', $booking))
            ->assertOk()
            ->assertSee('id="qr-svg"', false)
            ->assertSee('data-qr-token="'.$booking->return_token.'"', false)
            ->assertSee('data-size="200"', false);
    }

    public function test_return_pass_generates_missing_token(): void
    {
        $booking = Booking::where('borrower_id', $this->borrower->id)->firstOrFail();
        $booking->update(['return_token' => null]);

        $this->actingAs($this->borrower)
            ->get(route('borrower.bookings.return-pass', $booking))
            ->assertOk();

        $this->assertNotEmpty($booking->fresh()->return_token);
    }

    public function test_booking_create_page_renders_selected_assets(): void
    {
        $asset = Asset::firstOrFail();

        $this->actingAs($this->borrower)
            ->get(route('borrower.bookings.create', ['assets' => [$asset->id]]))
            ->assertOk()
            ->assertSee('name="assets[]"', false)
            ->assertSee('value="'.$asset->id.'"', false)
            ->assertSee($asset->code)
            ->assertDontSee('ui.booking.', false);
    }

    public function test_static_translation_keys_exist(): void
    {
        $missing = [];
        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(resource_path('views')));

        foreach ($files as $file) {
            if (! $file->isFile() || ! str_ends_with($file->getFilename(), '.blade.php')) {
                continue;
            }

            preg_match_all("/__\(\s*'((?:ui|messages)\.[A-Za-z0-9_.]+)'/", file_get_contents($file->getPathname()), $matches);

            foreach ($matches[1] as $key) {
                if (str_ends_with($key, '.') || Lang::has($key)) {
                    continue;
                }

                $missing[] = $key;
            }
        }

        $this->assertSame([], array_values(array_unique($missing)));
    }

    private function makePenalty(): Penalty
    {
        return Penalty::create([
            'organization_id' => Organization::where('code', 'LDR')->value('id'),
            'asset_id' => Asset::value('id'),
            'borrower_id' => $this->borrower->id,
            'bap_number' => 'LDR-BAP-TEST-0001',
            'incident_at' => now()->subDays(2),
            'inspector_name' => 'Budi Santoso',
            'damage_description' => 'Cacat sensor.',
            'repair_subtotal' => 1_000_000,
            'subsidy_percent' => 10,
            'subsidy_amount' => 100_000,
            'admin_fee' => 0,
            'total_amount' => 900_000,
            'status' => PenaltyStatus::WaitingPayment,
            'due_at' => now()->addDays(7),
        ]);
    }
}
