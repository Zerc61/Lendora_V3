<?php

namespace Tests\Feature\Borrower;

use App\Models\User;
use Database\Seeders\AdminSeeder;
use Database\Seeders\OrganizationSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShellTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([RoleSeeder::class, OrganizationSeeder::class, AdminSeeder::class]);
    }

    public function test_borrower_shell_has_five_navigation_items_and_area_styles(): void
    {
        $user = User::where('email', 'borrower1@lendora.test')->firstOrFail();

        $response = $this->actingAs($user)->get('/borrower');

        $response->assertOk();
        $response->assertSee('css/lendora.css');
        $response->assertSee('css/borrower.css');
        $response->assertSee('Navigasi utama');
        $response->assertSeeInOrder(['Beranda', 'Katalog', 'Peminjaman Saya', 'Notifikasi', 'Profil']);
        $this->assertSame(5, substr_count($response->getContent(), 'ldr-bottom-nav__item'));
    }

    public function test_borrower_home_marks_the_active_navigation_item(): void
    {
        $user = User::where('email', 'borrower1@lendora.test')->firstOrFail();

        $response = $this->actingAs($user)->get('/borrower');

        $response->assertSee('aria-current="page"', false);
        $response->assertSee('is-active');
    }
}
