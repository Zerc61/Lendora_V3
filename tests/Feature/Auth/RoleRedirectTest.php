<?php

namespace Tests\Feature\Auth;

use Database\Seeders\AdminSeeder;
use Database\Seeders\OrganizationSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class RoleRedirectTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([RoleSeeder::class, OrganizationSeeder::class, AdminSeeder::class]);
    }

    #[DataProvider('roleRedirects')]
    public function test_each_role_logs_in_and_reaches_the_correct_area(string $email, string $area): void
    {
        $this->post('/login', [
            'email' => $email,
            'password' => 'password',
        ])->assertRedirect('/dashboard');

        $this->assertAuthenticated();
        $this->get('/dashboard')->assertRedirect($area);
    }

    public static function roleRedirects(): array
    {
        return [
            'borrower' => ['borrower1@lendora.test', '/borrower'],
            'operator' => ['operator@lendora.test', '/admin'],
            'organization admin' => ['admin@lendora.test', '/admin'],
            'admin prodi' => ['adminprodi@lendora.test', '/admin'],
            'technician' => ['technician@lendora.test', '/admin'],
        ];
    }
}
