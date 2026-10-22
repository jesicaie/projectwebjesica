<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use App\Services\ThermalPrinterService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mike42\Escpos\PrintConnectors\DummyPrintConnector;
use Mike42\Escpos\PrintConnectors\PrintConnector;
use Tests\TestCase;

class CashierPaymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_cashier_payment_page_displays_available_products_and_payment_summary(): void
    {
        $cashier = User::factory()->create(['role' => 'kasir']);
        Product::create([
            'name' => 'Kopi Susu',
            'category' => 'Minuman',
            'price' => 18000,
            'stock' => 5,
            'is_active' => true,
        ]);

        $this->actingAs($cashier)
            ->get(route('kasir.transaksi'))
            ->assertOk()
            ->assertSee('Pilih produk')
            ->assertSee('Kopi Susu')
            ->assertSee('Ringkasan pembayaran')
            ->assertSee('Proses pembayaran');
    }

    public function test_struk_page_displays_the_latest_paid_receipt_for_the_signed_in_cashier(): void
    {
        $cashier = User::factory()->create(['role' => 'kasir']);
        $transaction = Transaction::create([
            'transaction_number' => 'TRX-STRUK-001',
            'user_id' => $cashier->id,
            'subtotal' => 15000,
            'grand_total' => 15000,
            'paid_amount' => 20000,
            'change_amount' => 5000,
            'payment_method' => 'cash',
            'status' => 'paid',
        ]);
        $transaction->details()->create([
            'product_name' => 'Produk Struk',
            'price' => 15000,
            'qty' => 1,
            'subtotal' => 15000,
        ]);

        $this->actingAs($cashier)
            ->get('/struk')
            ->assertOk()
            ->assertSee('TRX-STRUK-001')
            ->assertSee('Produk Struk')
            ->assertSee('Rp 15.000')
            ->assertSee('Rp 20.000');
    }

    public function test_cashier_payment_uses_current_product_price_and_updates_stock(): void
    {
        config(['thermal-printer.enabled' => false]);

        $cashier = User::factory()->create(['role' => 'kasir']);
        $product = Product::create([
            'name' => 'Kopi Susu',
            'price' => 18000,
            'stock' => 5,
            'is_active' => true,
        ]);

        $response = $this->actingAs($cashier)->post(route('kasir.transaksi.store'), [
            'cart' => json_encode([[
                'id' => $product->id,
                'name' => 'Harga palsu',
                'price' => 1,
                'qty' => 2,
            ]]),
            'payment_method' => 'cash',
            'paid_amount' => 50000,
        ]);

        $transaction = Transaction::firstOrFail();

        $response->assertRedirect(route('kasir.transaksi.show', $transaction));
        $response->assertSessionHas('warning');
        $this->assertSame('36000.00', $transaction->grand_total);
        $this->assertSame('50000.00', $transaction->paid_amount);
        $this->assertSame('14000.00', $transaction->change_amount);
        $this->assertSame(3, $product->fresh()->stock);
        $this->assertSame('36000.00', $transaction->details()->firstOrFail()->subtotal);
    }

    public function test_cashier_cannot_complete_underpaid_payment(): void
    {
        $cashier = User::factory()->create(['role' => 'kasir']);
        $product = Product::create([
            'name' => 'Kopi Susu',
            'price' => 18000,
            'stock' => 5,
            'is_active' => true,
        ]);

        $response = $this->actingAs($cashier)->from(route('kasir.transaksi'))->post(route('kasir.transaksi.store'), [
            'cart' => json_encode([['id' => $product->id, 'qty' => 1]]),
            'payment_method' => 'cash',
            'paid_amount' => 10000,
        ]);

        $response->assertRedirect(route('kasir.transaksi'))
            ->assertSessionHasErrors('paid_amount');
        $this->assertDatabaseCount('transactions', 0);
        $this->assertSame(5, $product->fresh()->stock);

        $this->get(route('kasir.transaksi'))
            ->assertOk()
            ->assertSee('Kopi Susu')
            ->assertSee('value="10000"', false);
    }

    public function test_cashier_cannot_resume_or_view_another_cashiers_transaction(): void
    {
        $cashier = User::factory()->create(['role' => 'kasir']);
        $otherCashier = User::factory()->create(['role' => 'kasir']);
        $transaction = Transaction::create([
            'transaction_number' => 'TRX-HELD-001',
            'user_id' => $otherCashier->id,
            'status' => 'held',
        ]);

        $this->actingAs($cashier)
            ->get(route('kasir.transaksi.resume', $transaction))
            ->assertNotFound();

        $this->get(route('kasir.transaksi.show', $transaction))
            ->assertNotFound();
    }

    public function test_resuming_and_paying_a_held_order_updates_the_same_transaction(): void
    {
        $cashier = User::factory()->create(['role' => 'kasir']);
        $product = Product::create([
            'name' => 'Roti',
            'price' => 12000,
            'stock' => 4,
            'is_active' => true,
        ]);

        $held = $this->actingAs($cashier)->post(route('kasir.transaksi.hold'), [
            'cart' => json_encode([['id' => $product->id, 'qty' => 1]]),
            'customer_name' => 'Pelanggan',
        ])->assertRedirect(route('kasir.transaksi'));

        $transaction = Transaction::firstOrFail();
        $this->assertSame('held', $transaction->status);
        $this->assertSame(4, $product->fresh()->stock);
        $this->assertSame('12000.00', $transaction->grand_total);

        $this->post(route('kasir.transaksi.store'), [
            'transaction_id' => $transaction->id,
            'cart' => json_encode([['id' => $product->id, 'qty' => 2]]),
            'payment_method' => 'qris',
            'paid_amount' => 24000,
        ])->assertRedirect(route('kasir.transaksi.show', $transaction));

        $this->assertDatabaseCount('transactions', 1);
        $this->assertSame('paid', $transaction->fresh()->status);
        $this->assertSame('24000.00', $transaction->fresh()->grand_total);
        $this->assertSame(2, $product->fresh()->stock);
        $this->assertSame(2, $transaction->details()->firstOrFail()->qty);
    }

    public function test_saving_a_resumed_order_again_updates_instead_of_duplicating_it(): void
    {
        $cashier = User::factory()->create(['role' => 'kasir']);
        $product = Product::create([
            'name' => 'Teh',
            'price' => 5000,
            'stock' => 8,
            'is_active' => true,
        ]);

        $this->actingAs($cashier)->post(route('kasir.transaksi.hold'), [
            'cart' => json_encode([['id' => $product->id, 'qty' => 1]]),
        ]);
        $transaction = Transaction::firstOrFail();

        $this->post(route('kasir.transaksi.hold'), [
            'transaction_id' => $transaction->id,
            'cart' => json_encode([['id' => $product->id, 'qty' => 3]]),
            'customer_name' => 'Dina',
        ])->assertRedirect(route('kasir.transaksi'));

        $this->assertDatabaseCount('transactions', 1);
        $this->assertSame(3, $transaction->details()->firstOrFail()->qty);
        $this->assertSame('Dina', $transaction->fresh()->customer_name);
    }

    public function test_transaction_history_only_shows_the_signed_in_cashiers_transactions(): void
    {
        $cashier = User::factory()->create(['role' => 'kasir']);
        $otherCashier = User::factory()->create(['role' => 'kasir']);

        Transaction::create([
            'transaction_number' => 'TRX-MY-001',
            'user_id' => $cashier->id,
            'customer_name' => 'Andi',
            'status' => 'paid',
        ]);
        Transaction::create([
            'transaction_number' => 'TRX-OTHER-001',
            'user_id' => $otherCashier->id,
            'status' => 'paid',
        ]);

        $this->actingAs($cashier)
            ->get(route('kasir.transactions', ['search' => 'Andi']))
            ->assertOk()
            ->assertSee('TRX-MY-001')
            ->assertDontSee('TRX-OTHER-001');
    }

    public function test_cashier_can_cancel_only_their_own_held_order(): void
    {
        $cashier = User::factory()->create(['role' => 'kasir']);
        $otherCashier = User::factory()->create(['role' => 'kasir']);
        $held = Transaction::create([
            'transaction_number' => 'TRX-HELD-OWN',
            'user_id' => $cashier->id,
            'status' => 'held',
        ]);
        $otherHeld = Transaction::create([
            'transaction_number' => 'TRX-HELD-OTHER',
            'user_id' => $otherCashier->id,
            'status' => 'held',
        ]);

        $this->actingAs($cashier)
            ->delete(route('kasir.transaksi.cancel-held', $otherHeld))
            ->assertNotFound();

        $this->delete(route('kasir.transaksi.cancel-held', $held))
            ->assertRedirect(route('kasir.transaksi'));

        $this->assertDatabaseMissing('transactions', ['id' => $held->id]);
        $this->assertDatabaseHas('transactions', ['id' => $otherHeld->id, 'status' => 'held']);
    }

    public function test_holding_a_cart_rejects_unavailable_products(): void
    {
        $cashier = User::factory()->create(['role' => 'kasir']);
        $product = Product::create([
            'name' => 'Stok Habis',
            'price' => 10000,
            'stock' => 0,
            'is_active' => true,
        ]);

        $this->actingAs($cashier)
            ->from(route('kasir.transaksi'))
            ->post(route('kasir.transaksi.hold'), [
                'cart' => json_encode([['id' => $product->id, 'qty' => 1]]),
            ])
            ->assertRedirect(route('kasir.transaksi'))
            ->assertSessionHasErrors('cart');

        $this->assertDatabaseCount('transactions', 0);
    }

    public function test_successful_payment_automatically_prints_when_escpos_is_enabled(): void
    {
        config(['thermal-printer.enabled' => true]);
        $this->mock(ThermalPrinterService::class, function ($mock): void {
            $mock->shouldReceive('print')->once()->andReturnTrue();
        });

        $cashier = User::factory()->create(['role' => 'kasir']);
        $product = Product::create([
            'name' => 'Susu',
            'price' => 10000,
            'stock' => 2,
            'is_active' => true,
        ]);

        $response = $this->actingAs($cashier)->post(route('kasir.transaksi.store'), [
            'cart' => json_encode([['id' => $product->id, 'qty' => 1]]),
            'payment_method' => 'cash',
            'paid_amount' => 10000,
        ]);

        $response->assertRedirect(route('kasir.transaksi.show', Transaction::firstOrFail()))
            ->assertSessionHas('print_status', 'Struk berhasil dicetak otomatis melalui printer ESC/POS.');
    }

    public function test_escpos_service_can_render_a_receipt_to_a_dummy_connector(): void
    {
        config(['thermal-printer.enabled' => true]);

        $cashier = User::factory()->create(['role' => 'kasir']);
        $transaction = Transaction::create([
            'transaction_number' => 'TRX-PRINT-001',
            'user_id' => $cashier->id,
            'subtotal' => 10000,
            'discount_percent' => 10,
            'grand_total' => 9000,
            'paid_amount' => 10000,
            'change_amount' => 1000,
            'payment_method' => 'cash',
            'status' => 'paid',
        ]);
        $transaction->details()->create([
            'product_name' => 'Produk Cetak',
            'price' => 10000,
            'qty' => 1,
            'subtotal' => 10000,
        ]);

        $printerService = new class extends ThermalPrinterService
        {
            protected function makeConnector(): ?PrintConnector
            {
                return new DummyPrintConnector;
            }
        };

        $this->assertTrue($printerService->print($transaction));
    }
}
