<?php

namespace Tests\Feature\Admin;

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

class GanttCalendarTest extends TestCase
{
    use RefreshDatabase;

    private Organization $organization;

    private User $operator;

    private User $borrower;

    private Asset $asset;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([RoleSeeder::class, OrganizationSeeder::class, AdminSeeder::class]);

        $this->organization = Organization::where('code', 'LDR')->firstOrFail();
        $this->operator = User::where('email', 'operator@lendora.test')->firstOrFail();
        $this->borrower = User::where('email', 'borrower1@lendora.test')->firstOrFail();

        $category = Category::create([
            'organization_id' => $this->organization->id,
            'name' => 'Lampu',
            'slug' => 'lampu',
        ]);

        $this->asset = Asset::create([
            'organization_id' => $this->organization->id,
            'category_id' => $category->id,
            'code' => 'LDR-LMP-8100',
            'name' => 'Lampu Studio',
            'status' => 'available',
            'condition_status' => 'good',
            'purchase_price' => 750_000,
        ]);

        $booking = Booking::create([
            'code' => 'LDR-BK-2026-8100',
            'organization_id' => $this->organization->id,
            'borrower_id' => $this->borrower->id,
            'status' => BookingStatus::Approved,
            'purpose' => 'Uji gantt',
            'requested_start_date' => Carbon::parse('2026-12-03'),
            'requested_end_date' => Carbon::parse('2026-12-06'),
        ]);

        $booking->items()->create(['asset_id' => $this->asset->id, 'status' => 'approved']);
    }

    public function test_calendar_page_exposes_gantt_data(): void
    {
        $response = $this->actingAs($this->operator)
            ->get(route('admin.bookings.calendar', ['month' => '2026-12']))
            ->assertOk()
            ->assertSee('data-gantt-bar', false)
            ->assertSee(__('ui.gantt.occupancy'));

        $gantt = $response->viewData('gantt');

        $this->assertSame('2026-12-03', $gantt['rows'][0]['bars'][0]['start']);
        $this->assertSame(2, $gantt['rows'][0]['bars'][0]['offset']);
    }

    public function test_csv_export_lists_asset_bars(): void
    {
        $response = $this->actingAs($this->operator)
            ->get(route('admin.bookings.calendar.export', [
                'date_from' => '2026-12-01',
                'days' => 14,
            ]))
            ->assertOk();

        $csv = $response->streamedContent();

        $this->assertStringContainsString('Kode Aset', $csv);
        $this->assertStringContainsString('LDR-LMP-8100', $csv);
        $this->assertStringContainsString('LDR-BK-2026-8100', $csv);
    }

    public function test_ical_export_contains_booking_event(): void
    {
        $response = $this->actingAs($this->operator)
            ->get(route('admin.bookings.calendar.export', [
                'date_from' => '2026-12-01',
                'days' => 14,
                'format' => 'ics',
            ]))
            ->assertOk();

        $ics = $response->streamedContent();

        $this->assertStringContainsString('BEGIN:VCALENDAR', $ics);
        $this->assertStringContainsString('SUMMARY:LDR-LMP-8100 LDR-BK-2026-8100', $ics);
    }
}
