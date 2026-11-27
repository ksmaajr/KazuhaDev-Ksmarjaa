<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminRoleTest extends TestCase
{
    use RefreshDatabase;

    private function validAdmin(): array
    {
        return ['name' => 'Second Admin', 'email' => 'second@example.com', 'password' => 'LongSecurePass123', 'password_confirmation' => 'LongSecurePass123'];
    }

    public function test_admin_can_add_another_admin_without_changing_own_login(): void
    {
        $admin = User::factory()->create();
        $this->actingAs($admin)->get('/admins')->assertOk()->assertSee('Kelola Admin');
        $this->post('/admins', $this->validAdmin())->assertRedirect('/admins')->assertSessionHasNoErrors();
        $new = User::where('email', 'second@example.com')->firstOrFail();
        $this->assertSame('admin', $new->role);
        $this->assertTrue(Hash::check('LongSecurePass123', $new->password));
        $this->assertAuthenticatedAs($admin);
        $this->assertDatabaseHas('license_events', ['action' => 'admin_created', 'user_id' => $admin->id]);
    }

    public function test_member_role_cannot_access_any_admin_route_even_if_authenticated(): void
    {
        $member = User::factory()->create(['role' => 'member']);
        $this->actingAs($member);
        foreach (['/dashboard', '/licenses', '/activity', '/settings', '/admins', '/api-console'] as $url) {
            $this->get($url)->assertForbidden();
        }
        $this->post('/admins', $this->validAdmin())->assertForbidden();
        $this->post('/licenses', ['label' => 'attack', 'days' => 30])->assertForbidden();
        $this->assertDatabaseCount('license_keys', 0);
        $this->assertDatabaseCount('users', 1);
    }

    public function test_member_role_cannot_use_admin_login_with_correct_password(): void
    {
        $member = User::factory()->create(['role' => 'member', 'password' => 'CorrectPassword123']);
        $this->post('/login', ['email' => $member->email, 'password' => 'CorrectPassword123'])->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_guests_cannot_create_administrators(): void
    {
        $this->get('/admins')->assertRedirect('/login');
        $this->post('/admins', $this->validAdmin())->assertRedirect('/login');
        $this->assertDatabaseCount('users', 0);
    }

    public function test_weak_password_duplicate_email_and_role_override_are_rejected(): void
    {
        $admin = User::factory()->create();
        $this->actingAs($admin);
        $this->post('/admins', [...$this->validAdmin(), 'password' => 'weak', 'password_confirmation' => 'weak'])->assertSessionHasErrors('password');
        $this->post('/admins', [...$this->validAdmin(), 'email' => $admin->email])->assertSessionHasErrors('email');
        $this->post('/admins', [...$this->validAdmin(), 'role' => 'member'])->assertSessionHasErrors('role');
        $this->assertDatabaseCount('users', 1);
    }

    public function test_new_user_is_member_by_default_and_role_is_not_mass_assignable(): void
    {
        $user = User::create(['name' => 'Default', 'email' => 'default@example.com', 'password' => 'Password12345']);
        $this->assertSame('member', $user->fresh()->role);
        $this->assertFalse($user->isFillable('role'));
    }

    public function test_admin_listing_excludes_member_users(): void
    {
        $admin = User::factory()->create();
        $member = User::factory()->create(['role' => 'member', 'email' => 'member-only@example.com']);
        $this->actingAs($admin)->get('/admins')->assertOk()->assertSee($admin->email)->assertDontSee($member->email);
    }

    public function test_command_creates_admin_role_explicitly(): void
    {
        $this->artisan('admin:create', ['email' => 'cli@example.com'])->assertSuccessful();
        $this->assertDatabaseHas('users', ['email' => 'cli@example.com', 'role' => 'admin']);
    }
}
