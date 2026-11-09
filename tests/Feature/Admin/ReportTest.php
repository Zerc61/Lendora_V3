<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Database\Seeders\AdminSeeder;
use Database\Seeders\CategoryAndAssetSeeder;
use Database\Seeders\DemoTransactionSeeder;
use Database\Seeders\OrganizationSeeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\SettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        $this->seed([
            RoleSeeder::class,
            OrganizationSeeder::class,
            AdminSeeder::class,
            CategoryAndAssetSeeder::class,
            SettingSeeder::class,
            DemoTransactionSeeder::class,
        ]);

        $this->admin = User::where('email', 'admin@lendora.test')->firstOrFail();
    }

    public function test_report_totals_match_seeded_transactions(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.reports.index', [
                'date_from' => now()->subDays(60)->toDateString(),
                'date_to' => now()->addDays(60)->toDateString(),
            ]))
            ->assertOk();

        $kpis = $response->viewData('kpis');

        $this->assertSame(3, $kpis['total_loans']);
        $this->assertGreaterThan(0, $kpis['borrowed_days']);
        $this->assertSame(0, $kpis['damages']);
        $this->assertArrayHasKey('late_returns', $kpis);
        $response->assertSee('Transaksi pada periode ini');
    }

    public function test_report_exposes_monthly_trend_and_top_assets(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.reports.index', [
                'date_from' => now()->subDays(60)->toDateString(),
                'date_to' => now()->addDays(60)->toDateString(),
            ]))
            ->assertOk();

        $trend = $response->viewData('monthlyTrend');
        $topAssets = $response->viewData('topAssets');

        $this->assertNotEmpty($trend);
        $this->assertSame(
            $trend->sum('total'),
            $response->viewData('kpis')['total_loans'],
        );
        $this->assertNotEmpty($topAssets);
        $this->assertArrayHasKey('code', $topAssets->first());
        $this->assertGreaterThan(0, $topAssets->sum('total'));
    }

    public function test_csv_export_returns_valid_file(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.reports.export', [
                'date_from' => now()->subDays(60)->toDateString(),
                'date_to' => now()->addDays(60)->toDateString(),
            ]))
            ->assertOk();

        $content = $response->streamedContent();

        $this->assertStringContainsString('Kode,Peminjam,Mulai,Selesai', $content);
        $this->assertStringContainsString('LDR-BK-2026-0001', $content);
        $this->assertCount(4, array_filter(explode("\n", trim($content))));
    }
}
