<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_dashboard_shows_storewide_summary(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Product::create([
            'name' => 'Kopi',
            'price' => 15000,
            'stock' => 3,
            'is_active' => true,
        ]);

        Transaction::create([
            'transaction_number' => 'TRX-ADMIN-001',
            'user_id' => $admin->id,
            'grand_total' => 25000,
            'status' => 'paid',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertOk()
            ->assertSee('Dashboard')
            ->assertSee('Kopi')
            ->assertSee('TRX-ADMIN-001')
            ->assertViewHas('activeProducts', 1)
            ->assertViewHas('lowStockProducts', 1)
            ->assertViewHas('todayTransactions', 1)
            ->assertViewHas('monthRevenue', fn ($revenue) => (float) $revenue === 25000.0);
    }

    public function test_kasir_dashboard_shows_only_their_own_transactions(): void
    {
        $kasir = User::factory()->create(['role' => 'kasir']);
        $otherKasir = User::factory()->create(['role' => 'kasir']);

        Transaction::create([
            'transaction_number' => 'TRX-KASIR-001',
            'user_id' => $kasir->id,
            'grand_total' => 12000,
            'status' => 'paid',
        ]);

        Transaction::create([
            'transaction_number' => 'TRX-KASIR-002',
            'user_id' => $kasir->id,
            'grand_total' => 0,
            'status' => 'held',
        ]);

        Transaction::create([
            'transaction_number' => 'TRX-OTHER-001',
            'user_id' => $otherKasir->id,
            'grand_total' => 50000,
            'status' => 'paid',
        ]);

        $response = $this->actingAs($kasir)->get(route('kasir.dashboard'));

        $response->assertOk()
            ->assertSee('TRX-KASIR-001')
            ->assertDontSee('TRX-OTHER-001')
            ->assertViewHas('todayTransactions', 1)
            ->assertViewHas('heldTransactions', 1)
            ->assertViewHas('todayRevenue', fn ($revenue) => (float) $revenue === 12000.0);
    }
}
