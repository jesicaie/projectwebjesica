<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Transaction;
use App\Services\ThermalPrinterService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TransactionController extends Controller
{
    public function index(Request $request): View
    {
        $products = Product::where('is_active', true)
            ->where('stock', '>', 0)
            ->orderBy('name')
            ->get();

        $heldTransactions = Transaction::with('details')
            ->where('user_id', auth()->id())
            ->where('status', 'held')
            ->latest()
            ->get();

        $resumeTransaction = null;

        if ($request->has('resume_id')) {
            $resumeTransaction = Transaction::where('user_id', auth()->id())
                ->where('status', 'held')
                ->findOrFail($request->resume_id);
        }

        return view('kasir.index', [
            'products' => $products,
            'heldTransactions' => $heldTransactions,
            'resumeTransaction' => $resumeTransaction,
            'thermalPrinterEnabled' => (bool) config('thermal-printer.enabled'),
        ]);
    }

    public function history(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'in:paid,held'],
        ]);

        $transactions = Transaction::where('user_id', auth()->id())
            ->when($filters['search'] ?? null, function ($query, string $search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('transaction_number', 'like', '%'.$search.'%')
                        ->orWhere('customer_name', 'like', '%'.$search.'%');
                });
            })
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('kasir.history', compact('transactions', 'filters'));
    }

    public function store(Request $request, ThermalPrinterService $printerService): RedirectResponse
    {
        $validated = $request->validate([
            'cart' => ['required', 'string', 'max:20000'],
            'transaction_id' => ['nullable', 'integer', 'min:1'],
            'payment_method' => ['required', 'string', 'in:cash,qris,debit,credit,ewallet,transfer'],
            'paid_amount' => ['required', 'numeric', 'min:0'],
            'discount_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'tax' => ['nullable', 'numeric', 'min:0'],
            'other_fee' => ['nullable', 'numeric', 'min:0'],
            'customer_name' => ['nullable', 'string', 'max:255'],
            'customer_phone' => ['nullable', 'string', 'max:255'],
        ]);

        $cart = json_decode($validated['cart'], true);
        if (! is_array($cart) || ! array_is_list($cart) || $cart === []) {
            return back()->withInput()->withErrors(['cart' => 'Keranjang masih kosong.']);
        }

        $items = collect($cart)->map(function ($item): ?array {
            if (! is_array($item)) {
                return null;
            }

            $productId = filter_var($item['id'] ?? null, FILTER_VALIDATE_INT);
            $quantity = filter_var($item['qty'] ?? null, FILTER_VALIDATE_INT);

            if ($productId === false || $productId < 1 || $quantity === false || $quantity < 1) {
                return null;
            }

            return ['id' => $productId, 'qty' => $quantity];
        });

        if ($items->contains(null) || $items->pluck('id')->unique()->count() !== $items->count()) {
            return back()->withInput()->withErrors(['cart' => 'Daftar produk atau jumlah barang tidak valid.']);
        }

        $result = DB::transaction(function () use ($items, $validated) {
            $transaction = null;
            if (! empty($validated['transaction_id'])) {
                $transaction = Transaction::where('user_id', auth()->id())
                    ->where('status', 'held')
                    ->lockForUpdate()
                    ->findOrFail($validated['transaction_id']);
            }

            $products = Product::whereIn('id', $items->pluck('id'))
                ->where('is_active', true)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            if ($products->count() !== $items->count()) {
                return back()->withInput()->withErrors(['cart' => 'Satu atau lebih produk tidak tersedia.']);
            }

            foreach ($items as $item) {
                $product = $products->get($item['id']);

                if ($item['qty'] > $product->stock) {
                    return back()->withInput()->withErrors(['cart' => 'Stok produk '.$product->name.' tidak mencukupi.']);
                }
            }

            $subtotal = $items->sum(fn (array $item): float => (float) $products->get($item['id'])->price * $item['qty']);
            $discountPercent = (float) ($validated['discount_percent'] ?? 0);
            $discountAmount = (float) ($validated['discount_amount'] ?? 0);
            $tax = (float) ($validated['tax'] ?? 0);
            $otherFee = (float) ($validated['other_fee'] ?? 0);
            $totalDiscount = min($subtotal, $subtotal * ($discountPercent / 100) + $discountAmount);
            $grandTotal = max(0, $subtotal - $totalDiscount + $tax + $otherFee);
            $paidAmount = (float) $validated['paid_amount'];

            if ($paidAmount < $grandTotal) {
                return back()->withInput()->withErrors(['paid_amount' => 'Uang dibayar kurang dari total transaksi.']);
            }

            $changeAmount = $paidAmount - $grandTotal;
            $transactionData = [
                'user_id' => auth()->id(),
                'customer_name' => $validated['customer_name'] ?? null,
                'customer_phone' => $validated['customer_phone'] ?? null,
                'subtotal' => $subtotal,
                'discount_percent' => $discountPercent,
                'discount_amount' => $discountAmount,
                'tax' => $tax,
                'other_fee' => $otherFee,
                'grand_total' => $grandTotal,
                'paid_amount' => $paidAmount,
                'change_amount' => $changeAmount,
                'payment_method' => $validated['payment_method'],
                'status' => 'paid',
                'cart_snapshot' => $items->toJson(),
            ];

            if ($transaction) {
                $transaction->update($transactionData);
                $transaction->details()->delete();
            } else {
                $transaction = Transaction::create([
                    ...$transactionData,
                    'transaction_number' => $this->generateTransactionNumber(),
                ]);
            }

            foreach ($items as $item) {
                $product = $products->get($item['id']);
                $qty = $item['qty'];
                $price = (float) $product->price;
                $lineSubtotal = $price * $qty;

                $transaction->details()->create([
                    'product_id' => $product->id,
                    'product_code' => $product->code,
                    'product_name' => $product->name,
                    'price' => $price,
                    'qty' => $qty,
                    'discount' => 0,
                    'subtotal' => $lineSubtotal,
                ]);

                $product->decrement('stock', $qty);
            }

            return $transaction;
        });

        if ($result instanceof RedirectResponse) {
            return $result;
        }

        $redirect = redirect()->route('kasir.transaksi.show', $result)
            ->with('success', 'Pembayaran berhasil.');

        if (! config('thermal-printer.enabled')) {
            return $redirect->with('warning', 'Printer ESC/POS belum diaktifkan. Anda tetap dapat mencetak struk dari browser.');
        }

        if (! $printerService->print($result)) {
            return $redirect->with('warning', 'Pembayaran tersimpan, tetapi printer gagal mencetak. Periksa konfigurasi lalu coba cetak ulang.');
        }

        return $redirect->with('print_status', 'Struk berhasil dicetak otomatis melalui printer ESC/POS.');
    }

    public function show(Transaction $transaction): View
    {
        abort_unless($transaction->user_id === auth()->id() && $transaction->status === 'paid', 404);

        $transaction->load('details.product', 'user');

        return view('kasir.receipt', compact('transaction'));
    }

    public function print(Transaction $transaction, ThermalPrinterService $printerService): View
    {
        abort_unless($transaction->user_id === auth()->id() && $transaction->status === 'paid', 404);

        $transaction->load('details.product', 'user');

        $printed = $printerService->print($transaction);

        if ($printed) {
            return view('kasir.receipt', compact('transaction'))->with('success', 'Struk berhasil dikirim ke printer.');
        }

        $message = config('thermal-printer.enabled')
            ? 'Printer gagal mencetak. Periksa koneksi dan konfigurasi printer.'
            : 'Printer ESC/POS belum diaktifkan. Gunakan tombol Print untuk mencetak melalui browser.';

        return view('kasir.receipt', compact('transaction'))->with('warning', $message);
    }

    public function hold(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'cart' => ['required', 'string', 'max:20000'],
            'transaction_id' => ['nullable', 'integer', 'min:1'],
            'customer_name' => ['nullable', 'string', 'max:255'],
            'customer_phone' => ['nullable', 'string', 'max:255'],
        ]);

        $cart = json_decode($validated['cart'], true);
        if (! is_array($cart) || ! array_is_list($cart) || $cart === []) {
            return back()->withInput()->withErrors(['cart' => 'Keranjang kosong tidak dapat ditahan.']);
        }

        $items = collect($cart)->map(function ($item): ?array {
            if (! is_array($item)) {
                return null;
            }

            $productId = filter_var($item['id'] ?? null, FILTER_VALIDATE_INT);
            $quantity = filter_var($item['qty'] ?? null, FILTER_VALIDATE_INT);

            if ($productId === false || $productId < 1 || $quantity === false || $quantity < 1) {
                return null;
            }

            return ['id' => $productId, 'qty' => $quantity];
        });

        if ($items->contains(null) || $items->pluck('id')->unique()->count() !== $items->count()) {
            return back()->withInput()->withErrors(['cart' => 'Daftar produk atau jumlah barang tidak valid.']);
        }

        return DB::transaction(function () use ($items, $validated) {
            $transaction = null;
            if (! empty($validated['transaction_id'])) {
                $transaction = Transaction::where('user_id', auth()->id())
                    ->where('status', 'held')
                    ->lockForUpdate()
                    ->findOrFail($validated['transaction_id']);
            }

            $products = Product::whereIn('id', $items->pluck('id'))
                ->where('is_active', true)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            if ($products->count() !== $items->count()) {
                return back()->withInput()->withErrors(['cart' => 'Satu atau lebih produk tidak tersedia.']);
            }

            foreach ($items as $item) {
                $product = $products->get($item['id']);
                if ($item['qty'] > $product->stock) {
                    return back()->withInput()->withErrors(['cart' => 'Stok produk '.$product->name.' tidak mencukupi.']);
                }
            }

            $subtotal = $items->sum(fn (array $item): float => (float) $products->get($item['id'])->price * $item['qty']);
            $transactionData = [
                'user_id' => auth()->id(),
                'customer_name' => $validated['customer_name'] ?? null,
                'customer_phone' => $validated['customer_phone'] ?? null,
                'subtotal' => $subtotal,
                'discount_percent' => 0,
                'discount_amount' => 0,
                'tax' => 0,
                'other_fee' => 0,
                'grand_total' => $subtotal,
                'paid_amount' => 0,
                'change_amount' => 0,
                'payment_method' => null,
                'status' => 'held',
                'cart_snapshot' => $items->toJson(),
            ];

            if ($transaction) {
                $transaction->update($transactionData);
                $transaction->details()->delete();
            } else {
                $transaction = Transaction::create([
                    ...$transactionData,
                    'transaction_number' => $this->generateTransactionNumber(),
                ]);
            }

            foreach ($items as $item) {
                $product = $products->get($item['id']);
                $price = (float) $product->price;

                $transaction->details()->create([
                    'product_id' => $product->id,
                    'product_code' => $product->code,
                    'product_name' => $product->name,
                    'price' => $price,
                    'qty' => $item['qty'],
                    'discount' => 0,
                    'subtotal' => $price * $item['qty'],
                ]);
            }

            return redirect()->route('kasir.transaksi')->with('success', 'Pesanan berhasil ditahan.');
        });
    }

    public function resume(Transaction $transaction): RedirectResponse
    {
        abort_unless($transaction->user_id === auth()->id() && $transaction->status === 'held', 404);

        return redirect()->route('kasir.transaksi', ['resume_id' => $transaction->id]);
    }

    public function cancelHeld(Transaction $transaction): RedirectResponse
    {
        DB::transaction(function () use ($transaction): void {
            $heldTransaction = Transaction::whereKey($transaction->id)
                ->where('user_id', auth()->id())
                ->where('status', 'held')
                ->lockForUpdate()
                ->firstOrFail();

            $heldTransaction->delete();
        });

        return redirect()->route('kasir.transaksi')->with('success', 'Pesanan ditahan berhasil dibatalkan.');
    }

    protected function generateTransactionNumber(): string
    {
        $date = now()->format('Ymd');
        $lastTransaction = Transaction::whereDate('created_at', today())->orderByDesc('id')->first();
        $sequence = $lastTransaction ? ((int) substr($lastTransaction->transaction_number, -3)) + 1 : 1;

        return 'TRX-'.$date.'-'.str_pad((string) $sequence, 3, '0', STR_PAD_LEFT);
    }
}
