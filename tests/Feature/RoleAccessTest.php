<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_admin_routes_but_not_kasir_routes(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get('/admin/users')
            ->assertOk();

        $this->actingAs($admin)
            ->get('/kasir/transactions')
            ->assertForbidden();
    }

    public function test_kasir_can_access_kasir_routes_but_not_admin_routes(): void
    {
        $kasir = User::factory()->create(['role' => 'kasir']);

        $this->actingAs($kasir)
            ->get('/kasir/transactions')
            ->assertOk();

        $this->actingAs($kasir)
            ->get('/admin/users')
            ->assertForbidden();

        $this->actingAs($kasir)
            ->get('/products')
            ->assertForbidden();
    }

    public function test_role_routes_require_authentication(): void
    {
        $this->get('/admin/users')->assertRedirect('/login');
        $this->get('/kasir/transactions')->assertRedirect('/login');
    }
}
