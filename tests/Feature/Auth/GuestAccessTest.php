<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuestAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_from_borrower_area(): void
    {
        $this->get('/borrower')->assertRedirect('/login');
    }

    public function test_guest_is_redirected_to_login_from_admin_area(): void
    {
        $this->get('/admin')->assertRedirect('/login');
    }

    public function test_auth_pages_render(): void
    {
        $this->get('/login')->assertOk();
        $this->get('/register')->assertOk();
    }
}
