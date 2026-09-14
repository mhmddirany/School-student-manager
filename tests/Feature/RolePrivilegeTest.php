<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Proves privileges are enforced server-side (route middleware + component
 * mount() checks), not just by hiding nav links in the UI.
 */
class RolePrivilegeTest extends TestCase
{
    use RefreshDatabase;

    public function test_viewer_is_forbidden_from_the_users_page(): void
    {
        $viewer = User::factory()->create(['role' => 'viewer']);

        $this->actingAs($viewer)
            ->get('/users')
            ->assertForbidden();
    }

    public function test_viewer_is_forbidden_from_the_logs_page(): void
    {
        $viewer = User::factory()->create(['role' => 'viewer']);

        $this->actingAs($viewer)
            ->get('/logs')
            ->assertForbidden();
    }

    public function test_admin_can_reach_the_users_and_logs_pages(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get('/users')->assertOk();
        $this->actingAs($admin)->get('/logs')->assertOk();
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
    }
}
