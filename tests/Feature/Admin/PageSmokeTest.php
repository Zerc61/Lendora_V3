<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Database\Seeders\AdminSeeder;
use Database\Seeders\OrganizationSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PageSmokeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([RoleSeeder::class, OrganizationSeeder::class, AdminSeeder::class]);
    }

    public function test_organization_admin_pages_render(): void
    {
        $admin = User::where('email', 'admin@lendora.test')->firstOrFail();

        $routes = [
            route('admin.dashboard'),
            route('admin.search'),
            route('admin.profile.edit'),
            route('admin.notifications.index'),
            route('admin.users.index'),
            route('admin.users.create'),
            route('admin.users.edit', $admin),
            route('admin.assets.index'),
            route('admin.categories.index'),
            route('admin.inspections.index'),
            route('admin.inspections.create'),
            route('admin.maintenances.index'),
            route('admin.maintenances.create'),
            route('admin.handovers.index'),
            route('admin.activity-logs.index'),
            route('admin.reports.index'),
            route('admin.settings.edit'),
        ];

        foreach ($routes as $route) {
            $this->actingAs($admin)->get($route)->assertOk();
        }
    }

    public function test_borrower_account_pages_render(): void
    {
        $borrower = User::where('email', 'borrower1@lendora.test')->firstOrFail();

        $this->actingAs($borrower)
            ->get(route('borrower.notifications.index'))
            ->assertOk();

        $this->actingAs($borrower)
            ->get(route('borrower.profile.edit'))
            ->assertOk();
    }
}
