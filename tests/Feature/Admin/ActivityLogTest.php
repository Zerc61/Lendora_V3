<?php

namespace Tests\Feature\Admin;

use App\Models\ActivityLog;
use App\Models\Organization;
use App\Models\User;
use Database\Seeders\AdminSeeder;
use Database\Seeders\OrganizationSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActivityLogTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([RoleSeeder::class, OrganizationSeeder::class, AdminSeeder::class]);
    }

    public function test_organization_admin_sees_only_logs_from_own_organization(): void
    {
        $admin = User::where('email', 'admin@lendora.test')->firstOrFail();
        $operator = User::where('email', 'operator@lendora.test')->firstOrFail();

        $otherOrganization = Organization::create([
            'code' => 'OTH',
            'name' => 'Organisasi Lain',
            'status' => 'active',
        ]);

        $otherUser = User::factory()->create(['organization_id' => $otherOrganization->id]);

        ActivityLog::create([
            'user_id' => $operator->id,
            'action' => 'booking.approved',
            'description' => 'Log organisasi sendiri',
        ]);

        ActivityLog::create([
            'user_id' => $otherUser->id,
            'action' => 'booking.approved',
            'description' => 'Log organisasi lain',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.activity-logs.index'))
            ->assertOk()
            ->assertSee('Log organisasi sendiri')
            ->assertDontSee('Log organisasi lain');
    }

    public function test_operator_cannot_open_activity_logs(): void
    {
        $operator = User::where('email', 'operator@lendora.test')->firstOrFail();

        $this->actingAs($operator)
            ->get(route('admin.activity-logs.index'))
            ->assertForbidden();
    }

    public function test_activity_log_filters_and_export(): void
    {
        $admin = User::where('email', 'admin@lendora.test')->firstOrFail();
        $operator = User::where('email', 'operator@lendora.test')->firstOrFail();

        ActivityLog::create([
            'user_id' => $operator->id,
            'action' => 'booking.approved',
            'description' => 'Pengajuan disetujui.',
        ]);

        ActivityLog::create([
            'user_id' => $operator->id,
            'action' => 'user.suspended',
            'description' => 'Akun ditangguhkan.',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.activity-logs.index', ['action' => 'booking.approved']))
            ->assertOk()
            ->assertSee('Pengajuan disetujui.')
            ->assertDontSee('Akun ditangguhkan.');

        $this->actingAs($admin)
            ->get(route('admin.activity-logs.index', ['search' => 'ditangguhkan']))
            ->assertOk()
            ->assertSee('Akun ditangguhkan.')
            ->assertDontSee('Pengajuan disetujui.');

        $response = $this->actingAs($admin)
            ->get(route('admin.activity-logs.export', ['action' => 'booking.approved']));

        $response->assertOk();
        $content = $response->streamedContent();
        $this->assertStringContainsString('Waktu', $content);
        $this->assertStringContainsString('Alamat IP', $content);
        $this->assertStringContainsString('booking.approved', $content);
        $this->assertStringNotContainsString('user.suspended', $content);
    }
}
