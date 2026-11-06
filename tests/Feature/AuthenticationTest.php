<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_and_non_admin_accounts_cannot_manage_catalog_or_orders(): void
    {
        $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
        $this->post(route('admin.products.store'))->assertRedirect(route('login'));

        $this->actingAs(User::factory()->create());
        $this->get(route('admin.dashboard'))->assertForbidden();
        $this->get(route('admin.orders.index'))->assertForbidden();
        $this->post(route('admin.categories.store'))->assertForbidden();
        $this->assertDatabaseCount('categories', 0);
    }

    public function test_login_regenerates_session_and_logout_invalidates_it(): void
    {
        $this->withoutVite();
        $admin = User::factory()->admin()->create(['password' => 'testing-only-strong-passphrase']);
        $this->get(route('login'))->assertOk();
        $before = session()->getId();

        $this->post(route('login.store'), ['email' => $admin->email, 'password' => 'incorrect'])
            ->assertSessionHasErrors('email');
        $this->assertGuest();
        $this->post(route('login.store'), ['email' => $admin->email, 'password' => 'testing-only-strong-passphrase'])
            ->assertRedirect(route('admin.dashboard'));

        $this->assertNotSame($before, session()->getId());
        $this->assertAuthenticatedAs($admin);
        $this->get(route('admin.dashboard'))->assertOk();
        $this->post(route('logout'))->assertRedirect(route('shop.home'));
        $this->assertGuest();
        $this->get(route('admin.orders.index'))->assertRedirect(route('login'));
    }
}
