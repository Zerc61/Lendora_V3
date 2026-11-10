<?php

namespace Tests\Unit;

use App\Services\ReportAnalyticsService;
use Database\Seeders\AdminSeeder;
use Database\Seeders\CategoryAndAssetSeeder;
use Database\Seeders\DemoTransactionSeeder;
use Database\Seeders\OrganizationSeeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\SettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportAnalyticsServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_builds_report_data_for_period(): void
    {
        $this->seed([
            RoleSeeder::class,
            OrganizationSeeder::class,
            AdminSeeder::class,
            CategoryAndAssetSeeder::class,
            SettingSeeder::class,
            DemoTransactionSeeder::class,
        ]);

        $organization = \App\Models\Organization::where('code', 'LDR')->firstOrFail();

        $data = app(ReportAnalyticsService::class)->build(
            $organization->id,
            now()->subDays(60)->startOfDay(),
            now()->addDays(60)->endOfDay(),
        );

        $this->assertSame(3, $data['kpis']['total_loans']);
        $this->assertGreaterThan(0, $data['kpis']['borrowed_days']);
        $this->assertArrayHasKey('late_returns', $data['kpis']);
        $this->assertNotEmpty($data['utilization']);
        $this->assertNotEmpty($data['topBorrowers']);
        $this->assertNotEmpty($data['topAssets']);
        $this->assertSame($data['kpis']['total_loans'], $data['monthlyTrend']->sum('total'));
        $this->assertArrayHasKey('date_from', $data['filters']);
        $this->assertArrayHasKey('date_to', $data['filters']);
    }
}
