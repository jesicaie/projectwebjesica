<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-medium text-emerald-700">Kasir / Pembayaran</p>
                <h1 class="text-2xl font-bold tracking-tight text-gray-900">Transaksi baru</h1>
            </div>
            <div class="text-sm text-gray-500">{{ now()->translatedFormat('l, d F Y') }}</div>
        </div>
    </x-slot>

    <div class="min-h-[calc(100vh-8rem)] bg-slate-50 py-7 sm:py-9">
        <div class="mx-auto max-w-7xl space-y-5 px-4 sm:px-6 lg:px-8">
            @if (session('success'))
                <div role="status" class="flex items-center gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">
                    <svg class="h-5 w-5 shrink-0" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.78-9.22a.75.75 0 0 0-1.06-1.06L9 11.44 7.28 9.72a.75.75 0 0 0-1.06 1.06l2.25 2.25a.75.75 0 0 0 1.06 0l4.25-4.25Z" clip-rule="evenodd"/></svg>
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div role="alert" class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
                    <p class="font-semibold">Transaksi belum dapat diproses</p>
                    <ul class="mt-1 list-inside list-disc">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="flex items-start gap-3 rounded-xl border px-4 py-3 text-sm {{ $thermalPrinterEnabled ? 'border-emerald-200 bg-emerald-50 text-emerald-800' : 'border-sky-200 bg-sky-50 text-sky-800' }}">
                <span class="mt-0.5 h-2 w-2 shrink-0 rounded-full {{ $thermalPrinterEnabled ? 'bg-emerald-500' : 'bg-sky-500' }}"></span>
                <p>
                    @if ($thermalPrinterEnabled)
                        Cetak otomatis ESC/POS aktif. Struk akan dikirim ke printer setelah pembayaran berhasil.
                    @else
                        Cetak otomatis ESC/POS belum diaktifkan. Pembayaran tetap berjalan dan struk dapat dicetak melalui browser.
                    @endif
                </p>
            </div>

            @if ($resumeTransaction)
                <div class="flex flex-col gap-2 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900 sm:flex-row sm:items-center sm:justify-between">
                    <p><span class="font-semibold">Melanjutkan pesanan {{ $resumeTransaction->transaction_number }}</span> — periksa kembali ketersediaan barang sebelum membayar.</p>
                    <a href="{{ route('kasir.transaksi') }}" class="shrink-0 font-semibold underline underline-offset-2">Mulai transaksi baru</a>
                </div>
            @endif

            <div class="grid items-start gap-5 xl:grid-cols-[minmax(0,1fr)_380px]">
                <div class="space-y-5">
                    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                        <div class="border-b border-slate-100 px-5 py-5 sm:px-6">
                            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <h2 class="text-base font-bold text-gray-900">Pilih produk</h2>
                                    <p class="mt-1 text-sm text-gray-500">Cari nama, kode, atau pindai barcode produk.</p>
                                </div>
                                <span class="inline-flex w-fit items-center gap-2 rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-800">
                                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                    {{ $products->count() }} produk tersedia
                                </span>
                            </div>
                            <div class="relative mt-4">
                                <label for="product-search" class="sr-only">Cari produk atau barcode</label>
                                <svg class="pointer-events-none absolute left-3.5 top-1/2 h-5 w-5 -translate-y-1/2 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="10.8" cy="10.8" r="6.8"/><path stroke-linecap="round" d="m16 16 4.5 4.5"/></svg>
                                <input id="product-search" type="search" autocomplete="off" placeholder="Cari nama, kode, atau barcode..." class="block w-full rounded-xl border-slate-200 py-3 pl-11 pr-4 text-sm shadow-sm placeholder:text-gray-400 focus:border-emerald-500 focus:ring-emerald-500">
                            </div>
                        </div>
                        <div id="product-results" class="grid max-h-[440px] grid-cols-1 gap-3 overflow-y-auto p-4 sm:grid-cols-2 sm:p-5 lg:grid-cols-3" aria-live="polite"></div>
                    </section>

                    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                        <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4 sm:px-6">
                            <div>
                                <h2 class="text-base font-bold text-gray-900">Keranjang belanja</h2>
                                <p class="mt-1 text-sm text-gray-500"><span id="cart-item-count">0</span> barang dipilih</p>
                            </div>
                            <span class="rounded-lg bg-slate-100 p-2 text-slate-500">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 4h2l2.2 11.2a2 2 0 0 0 2 1.6h7.9a2 2 0 0 0 2-1.6L21 8H6"/><circle cx="10" cy="20" r="1"/><circle cx="18" cy="20" r="1"/></svg>
                            </span>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-slate-100">
                                <thead class="bg-slate-50">
                                    <tr>
                                        <th scope="col" class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Produk</th>
                                        <th scope="col" class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500">Harga</th>
                                        <th scope="col" class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500">Jumlah</th>
                                        <th scope="col" class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500">Subtotal</th>
                                        <th scope="col" class="px-4 py-3"><span class="sr-only">Hapus</span></th>
                                    </tr>
                                </thead>
                                <tbody id="cart-body" class="divide-y divide-slate-100">
                                    <tr><td colspan="5" class="px-5 py-12 text-center">
                                        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                                            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 4h2l2.2 11.2a2 2 0 0 0 2 1.6h7.9a2 2 0 0 0 2-1.6L21 8H6"/><circle cx="10" cy="20" r="1"/><circle cx="18" cy="20" r="1"/></svg>
                                        </div>
                                        <p class="mt-3 text-sm font-semibold text-gray-700">Keranjang masih kosong</p>
                                        <p class="mt-1 text-sm text-gray-500">Pilih produk di atas untuk memulai transaksi.</p>
                                    </td></tr>
                                </tbody>
                            </table>
                        </div>
                    </section>

                    @if ($heldTransactions->isNotEmpty())
                        <section class="rounded-2xl border border-amber-200 bg-white shadow-sm">
                            <div class="flex items-center justify-between border-b border-amber-100 px-5 py-4">
                                <div>
                                    <h2 class="text-sm font-bold text-gray-900">Pesanan ditahan</h2>
                                    <p class="mt-1 text-xs text-gray-500">Lanjutkan pesanan yang disimpan sebelumnya.</p>
                                </div>
                                <span class="rounded-full bg-amber-100 px-2.5 py-1 text-xs font-semibold text-amber-800">{{ $heldTransactions->count() }}</span>
                            </div>
                            <ul class="divide-y divide-slate-100">
                                @foreach ($heldTransactions as $held)
                                    <li class="flex items-center justify-between gap-3 px-5 py-3">
                                        <div class="min-w-0">
                                            <p class="truncate text-sm font-semibold text-gray-800">{{ $held->transaction_number }}</p>
                                            <p class="mt-0.5 text-xs text-gray-500">{{ $held->details->sum('qty') }} barang · {{ $held->created_at->translatedFormat('d M, H:i') }}</p>
                                        </div>
                                        <div class="flex shrink-0 items-center gap-2">
                                            <a href="{{ route('kasir.transaksi.resume', $held) }}" class="rounded-lg border border-amber-200 px-3 py-2 text-xs font-semibold text-amber-800 transition hover:bg-amber-50">Lanjutkan</a>
                                            <form method="POST" action="{{ route('kasir.transaksi.cancel-held', $held) }}" onsubmit="return confirm('Batalkan pesanan ditahan ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="rounded-lg px-2 py-2 text-xs font-semibold text-rose-600 transition hover:bg-rose-50">Batalkan</button>
                                            </form>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        </section>
                    @endif
                </div>

                <aside class="xl:sticky xl:top-6">
                    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                        <div class="bg-slate-900 px-5 py-4 text-white">
                            <p class="text-sm font-semibold text-slate-300">Ringkasan pembayaran</p>
                            <p id="summary-grand-total" class="mt-1 text-3xl font-bold tracking-tight">Rp 0</p>
                        </div>

                        <div class="space-y-5 p-5">
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label for="customer_name" class="block text-xs font-semibold text-gray-600">Nama pelanggan <span class="font-normal text-gray-400">(opsional)</span></label>
                                    <input type="text" id="customer_name" maxlength="255" value="{{ old('customer_name') }}" placeholder="Pelanggan umum" class="mt-1.5 block w-full rounded-lg border-slate-200 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                                </div>
                                <div>
                                    <label for="customer_phone" class="block text-xs font-semibold text-gray-600">No. telepon</label>
                                    <input type="text" id="customer_phone" maxlength="255" value="{{ old('customer_phone') }}" placeholder="08xx..." class="mt-1.5 block w-full rounded-lg border-slate-200 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                                </div>
                            </div>

                            <div class="space-y-3 border-y border-slate-100 py-4 text-sm">
                                <div class="flex items-center justify-between text-gray-600"><span>Jumlah item</span><strong id="summary-item-count" class="font-semibold text-gray-900">0</strong></div>
                                <div class="flex items-center justify-between text-gray-600"><span>Subtotal</span><strong id="summary-subtotal" class="font-semibold text-gray-900">Rp 0</strong></div>
                                <div class="grid grid-cols-[1fr_112px] items-center gap-3">
                                    <label for="discount-percent" class="text-gray-600">Diskon (%)</label>
                                    <div class="relative">
                                        <input type="number" id="discount-percent" min="0" max="100" step="0.01" value="{{ old('discount_percent', 0) }}" class="block w-full rounded-lg border-slate-200 pr-8 text-right text-sm focus:border-emerald-500 focus:ring-emerald-500">
                                        <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-xs text-gray-400">%</span>
                                    </div>
                                </div>
                                <div class="grid grid-cols-[1fr_112px] items-center gap-3">
                                    <label for="discount-amount" class="text-gray-600">Diskon tambahan</label>
                                    <input type="number" id="discount-amount" min="0" step="1" value="{{ old('discount_amount', 0) }}" class="block w-full rounded-lg border-slate-200 text-right text-sm focus:border-emerald-500 focus:ring-emerald-500">
                                </div>
                                <div class="grid grid-cols-[1fr_112px] items-center gap-3">
                                    <label for="tax" class="text-gray-600">Pajak</label>
                                    <input type="number" id="tax" min="0" step="1" value="{{ old('tax', 0) }}" class="block w-full rounded-lg border-slate-200 text-right text-sm focus:border-emerald-500 focus:ring-emerald-500">
                                </div>
                                <div class="grid grid-cols-[1fr_112px] items-center gap-3">
                                    <label for="other-fee" class="text-gray-600">Biaya lain</label>
                                    <input type="number" id="other-fee" min="0" step="1" value="{{ old('other_fee', 0) }}" class="block w-full rounded-lg border-slate-200 text-right text-sm focus:border-emerald-500 focus:ring-emerald-500">
                                </div>
                            </div>

                            <div>
                                <p class="text-sm font-semibold text-gray-800">Metode pembayaran</p>
                                <div class="mt-2 grid grid-cols-3 gap-2" id="payment-methods">
                                    <button type="button" aria-pressed="true" class="payment-method-btn rounded-lg border border-emerald-300 bg-emerald-50 px-2 py-2.5 text-xs font-semibold text-emerald-800 transition" data-method="cash">Tunai</button>
                                    <button type="button" aria-pressed="false" class="payment-method-btn rounded-lg border border-slate-200 px-2 py-2.5 text-xs font-semibold text-gray-600 transition hover:bg-slate-50" data-method="qris">QRIS</button>
                                    <button type="button" aria-pressed="false" class="payment-method-btn rounded-lg border border-slate-200 px-2 py-2.5 text-xs font-semibold text-gray-600 transition hover:bg-slate-50" data-method="debit">Debit</button>
                                    <button type="button" aria-pressed="false" class="payment-method-btn rounded-lg border border-slate-200 px-2 py-2.5 text-xs font-semibold text-gray-600 transition hover:bg-slate-50" data-method="credit">Kredit</button>
                                    <button type="button" aria-pressed="false" class="payment-method-btn rounded-lg border border-slate-200 px-2 py-2.5 text-xs font-semibold text-gray-600 transition hover:bg-slate-50" data-method="ewallet">E-wallet</button>
                                    <button type="button" aria-pressed="false" class="payment-method-btn rounded-lg border border-slate-200 px-2 py-2.5 text-xs font-semibold text-gray-600 transition hover:bg-slate-50" data-method="transfer">Transfer</button>
                                </div>
                            </div>

                            <div>
                                <label for="paid-amount" class="block text-sm font-semibold text-gray-800">Jumlah dibayar</label>
                                <div class="relative mt-1.5">
                                    <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-sm font-medium text-gray-400">Rp</span>
                                    <input type="number" id="paid-amount" min="0" step="1" value="{{ old('paid_amount', 0) }}" class="block w-full rounded-lg border-slate-200 py-3 pl-10 pr-3 text-lg font-semibold focus:border-emerald-500 focus:ring-emerald-500">
                                </div>
                                <div id="quick-payment-options" class="mt-2 grid grid-cols-3 gap-2">
                                    <button type="button" class="quick-payment-btn rounded-lg border border-slate-200 px-2 py-2 text-xs font-semibold text-gray-600 transition hover:border-emerald-300 hover:bg-emerald-50" data-amount="0">Uang pas</button>
                                    <button type="button" class="quick-payment-btn rounded-lg border border-slate-200 px-2 py-2 text-xs font-semibold text-gray-600 transition hover:border-emerald-300 hover:bg-emerald-50" data-amount="50000">50.000</button>
                                    <button type="button" class="quick-payment-btn rounded-lg border border-slate-200 px-2 py-2 text-xs font-semibold text-gray-600 transition hover:border-emerald-300 hover:bg-emerald-50" data-amount="100000">100.000</button>
                                </div>
                            </div>

                            <div class="flex items-center justify-between rounded-xl bg-emerald-50 px-4 py-3">
                                <div>
                                    <p class="text-xs font-medium text-emerald-800">Uang kembalian</p>
                                    <p id="payment-status" class="mt-0.5 text-xs text-emerald-700">Masukkan jumlah pembayaran</p>
                                </div>
                                <strong id="summary-change" class="text-lg font-bold text-emerald-900">Rp 0</strong>
                            </div>

                            <div class="grid grid-cols-[1fr_auto] gap-2">
                                <button type="submit" form="cashier-form" id="pay-button" disabled class="inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-700 px-4 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-emerald-800 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:bg-slate-300">
                                    Proses pembayaran
                                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M3 10a.75.75 0 0 1 .75-.75h10.69L10.22 5.03a.75.75 0 1 1 1.06-1.06l5.5 5.5a.75.75 0 0 1 0 1.06l-5.5 5.5a.75.75 0 1 1-1.06-1.06l4.22-4.22H3.75A.75.75 0 0 1 3 10Z" clip-rule="evenodd"/></svg>
                                </button>
                                <button type="submit" form="hold-form" id="hold-button" title="Simpan pesanan untuk dilanjutkan nanti" class="rounded-xl border border-amber-200 px-3 py-3 text-sm font-semibold text-amber-800 transition hover:bg-amber-50 disabled:cursor-not-allowed disabled:opacity-50" disabled>Tahan</button>
                            </div>
                            <button type="button" id="cancel-button" class="w-full rounded-lg py-2 text-sm font-semibold text-gray-500 transition hover:bg-rose-50 hover:text-rose-700">Kosongkan transaksi</button>
                        </div>
                    </section>
                    <p class="mt-3 text-center text-xs text-gray-400">Pembayaran diproses aman · Stok diperbarui setelah transaksi berhasil</p>
                </aside>
            </div>

            <form id="cashier-form" action="{{ route('kasir.transaksi.store') }}" method="POST" class="hidden">
                @csrf
                <input type="hidden" name="cart" id="cart-input">
                <input type="hidden" name="transaction_id" id="transaction-id-input" value="{{ $resumeTransaction?->id }}">
                <input type="hidden" name="customer_name" id="customer-name-input">
                <input type="hidden" name="customer_phone" id="customer-phone-input">
                <input type="hidden" name="discount_percent" id="discount-percent-input">
                <input type="hidden" name="discount_amount" id="discount-amount-input">
                <input type="hidden" name="tax" id="tax-input">
                <input type="hidden" name="other_fee" id="other-fee-input">
                <input type="hidden" name="paid_amount" id="paid-amount-input">
                <input type="hidden" name="payment_method" id="payment-method-input" value="cash">
            </form>
            <form id="hold-form" action="{{ route('kasir.transaksi.hold') }}" method="POST" class="hidden">
                @csrf
                <input type="hidden" name="cart" id="hold-cart-input">
                <input type="hidden" name="transaction_id" id="hold-transaction-id-input" value="{{ $resumeTransaction?->id }}">
                <input type="hidden" name="customer_name" id="hold-customer-name-input">
                <input type="hidden" name="customer_phone" id="hold-customer-phone-input">
            </form>
        </div>
    </div>

    @php
        $resumeCart = $resumeTransaction
            ? json_decode($resumeTransaction->cart_snapshot ?? '[]', true)
            : (is_string(old('cart')) ? json_decode(old('cart'), true) : []);

        $productPayload = $products->map(function ($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'code' => $product->code,
                'barcode' => $product->barcode,
                'category' => $product->category,
                'price' => (float) $product->price,
                'stock' => (int) $product->stock,
            ];
        })->values();
    @endphp
    <script>
        const products = @json($productPayload);
        const resumeCart = @json($resumeCart ?? []);
        let cart = Array.isArray(resumeCart)
            ? resumeCart.reduce((items, savedItem) => {
                const product = products.find((item) => Number(item.id) === Number(savedItem.id));
                if (Number(savedItem.qty) > 0) {
                    items.push(product
                        ? { ...product, qty: Number(savedItem.qty) }
                        : { ...savedItem, qty: Number(savedItem.qty), stock: 0, unavailable: true });
                }
                return items;
            }, [])
            : [];
        let selectedPaymentMethod = 'cash';

        const currency = (value) => 'Rp ' + Number(value || 0).toLocaleString('id-ID');
        const escapeHtml = (value) => String(value ?? '').replace(/[&<>"']/g, (character) => ({
            '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;',
        })[character]);
        const getProductById = (productId) => products.find((product) => Number(product.id) === Number(productId));
        const getItemIndex = (productId) => cart.findIndex((item) => Number(item.id) === Number(productId));

        function recalculateTotals() {
            const subtotal = cart.reduce((sum, item) => sum + Number(item.price) * Number(item.qty), 0);
            const discountPercent = Math.min(100, Math.max(0, Number(document.getElementById('discount-percent').value || 0)));
            const discountAmount = Math.max(0, Number(document.getElementById('discount-amount').value || 0));
            const tax = Math.max(0, Number(document.getElementById('tax').value || 0));
            const otherFee = Math.max(0, Number(document.getElementById('other-fee').value || 0));
            const totalDiscount = Math.min(subtotal, subtotal * (discountPercent / 100) + discountAmount);
            const grandTotal = Math.max(0, subtotal - totalDiscount + tax + otherFee);
            const paidInput = document.getElementById('paid-amount');
            const stockAvailable = cart.every((item) => !item.unavailable && Number(item.stock) > 0 && Number(item.qty) <= Number(item.stock));

            if (selectedPaymentMethod !== 'cash') {
                paidInput.value = grandTotal;
            }

            const paidAmount = Math.max(0, Number(paidInput.value || 0));
            const change = Math.max(0, paidAmount - grandTotal);
            const isPaid = cart.length > 0 && stockAvailable && paidAmount >= grandTotal;

            document.getElementById('summary-item-count').textContent = cart.reduce((sum, item) => sum + Number(item.qty), 0);
            document.getElementById('cart-item-count').textContent = cart.reduce((sum, item) => sum + Number(item.qty), 0);
            document.getElementById('summary-subtotal').textContent = currency(subtotal);
            document.getElementById('summary-grand-total').textContent = currency(grandTotal);
            document.getElementById('summary-change').textContent = currency(change);
            document.getElementById('payment-status').textContent = cart.length === 0
                ? 'Tambahkan produk untuk memulai'
                : !stockAvailable ? 'Periksa kembali ketersediaan stok'
                    : paidAmount < grandTotal ? 'Pembayaran masih kurang' : 'Pembayaran mencukupi';
            document.getElementById('pay-button').disabled = !isPaid;
            document.getElementById('hold-button').disabled = cart.length === 0;
            paidInput.readOnly = selectedPaymentMethod !== 'cash';
            document.getElementById('quick-payment-options').classList.toggle('opacity-40', selectedPaymentMethod !== 'cash');

            document.getElementById('discount-percent-input').value = discountPercent;
            document.getElementById('discount-amount-input').value = discountAmount;
            document.getElementById('tax-input').value = tax;
            document.getElementById('other-fee-input').value = otherFee;
            document.getElementById('paid-amount-input').value = paidAmount;
            document.getElementById('payment-method-input').value = selectedPaymentMethod;
        }

        function renderProducts(searchTerm = '') {
            const query = searchTerm.toLowerCase().trim();
            const matches = products.filter((product) => {
                const haystack = `${product.name} ${product.code || ''} ${product.barcode || ''} ${product.category || ''}`.toLowerCase();
                return haystack.includes(query);
            }).slice(0, 18);
            const results = document.getElementById('product-results');

            if (!matches.length) {
                results.innerHTML = `<div class="col-span-full rounded-xl bg-slate-50 px-4 py-10 text-center text-sm text-gray-500">${query ? 'Produk tidak ditemukan.' : 'Belum ada produk aktif yang tersedia.'}</div>`;
                return;
            }

            results.innerHTML = matches.map((product) => `
                <button type="button" class="product-item group flex min-h-[104px] flex-col justify-between rounded-xl border border-slate-200 p-4 text-left transition hover:border-emerald-300 hover:bg-emerald-50/50 hover:shadow-sm focus:outline-none focus:ring-2 focus:ring-emerald-500" data-id="${Number(product.id)}">
                    <span class="flex w-full items-start justify-between gap-2">
                        <span class="min-w-0">
                            <span class="block truncate text-sm font-semibold text-gray-800">${escapeHtml(product.name)}</span>
                            <span class="mt-1 block truncate text-xs text-gray-500">${escapeHtml(product.code || product.barcode || 'Tanpa kode')}</span>
                        </span>
                        <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-slate-500 transition group-hover:bg-emerald-100 group-hover:text-emerald-700" aria-hidden="true">+</span>
                    </span>
                    <span class="mt-3 flex items-center justify-between gap-2">
                        <span class="truncate text-xs text-gray-400">${escapeHtml(product.category || 'Produk')}</span>
                        <span class="whitespace-nowrap text-sm font-bold text-emerald-700">${currency(product.price)}</span>
                    </span>
                </button>
            `).join('');
        }

        function renderCart() {
            const tbody = document.getElementById('cart-body');
            if (!cart.length) {
                tbody.innerHTML = `<tr><td colspan="5" class="px-5 py-12 text-center">
                    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 4h2l2.2 11.2a2 2 0 0 0 2 1.6h7.9a2 2 0 0 0 2-1.6L21 8H6"/><circle cx="10" cy="20" r="1"/><circle cx="18" cy="20" r="1"/></svg>
                    </div>
                    <p class="mt-3 text-sm font-semibold text-gray-700">Keranjang masih kosong</p>
                    <p class="mt-1 text-sm text-gray-500">Pilih produk di atas untuk memulai transaksi.</p>
                </td></tr>`;
                recalculateTotals();
                return;
            }

            tbody.innerHTML = cart.map((item) => `
                <tr>
                    <td class="px-5 py-4">
                        <p class="max-w-[180px] truncate text-sm font-semibold text-gray-800">${escapeHtml(item.name)}</p>
                        <p class="mt-0.5 text-xs ${item.unavailable || Number(item.qty) > Number(item.stock) ? 'text-rose-600' : 'text-gray-500'}">${item.unavailable ? 'Produk tidak tersedia' : Number(item.qty) > Number(item.stock) ? `Stok tersisa ${Number(item.stock)}` : escapeHtml(item.code || item.barcode || 'Tanpa kode')}</p>
                    </td>
                    <td class="whitespace-nowrap px-4 py-4 text-right text-sm text-gray-600">${currency(item.price)}</td>
                    <td class="px-4 py-4">
                        <div class="flex items-center justify-center gap-1.5">
                            <button type="button" aria-label="Kurangi ${escapeHtml(item.name)}" class="quantity-button flex h-7 w-7 items-center justify-center rounded-md border border-slate-200 text-sm font-semibold text-gray-600 hover:bg-slate-50" data-action="minus" data-id="${Number(item.id)}">−</button>
                            <input type="number" min="1" max="${Number(item.stock)}" value="${Number(item.qty)}" aria-label="Jumlah ${escapeHtml(item.name)}" data-id="${Number(item.id)}" class="qty-input h-8 w-12 rounded-md border-slate-200 p-1 text-center text-sm focus:border-emerald-500 focus:ring-emerald-500">
                            <button type="button" aria-label="Tambah ${escapeHtml(item.name)}" class="quantity-button flex h-7 w-7 items-center justify-center rounded-md border border-slate-200 text-sm font-semibold text-gray-600 hover:bg-slate-50" data-action="plus" data-id="${Number(item.id)}">+</button>
                        </div>
                    </td>
                    <td class="whitespace-nowrap px-4 py-4 text-right text-sm font-semibold text-gray-800">${currency(Number(item.price) * Number(item.qty))}</td>
                    <td class="px-4 py-4 text-right">
                        <button type="button" aria-label="Hapus ${escapeHtml(item.name)}" class="remove-item rounded-md p-1 text-gray-400 transition hover:bg-rose-50 hover:text-rose-600" data-id="${Number(item.id)}">
                            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M8.5 2a1 1 0 0 0-.9.55L7.2 3H4a.75.75 0 0 0 0 1.5h.6l.7 11.05A2 2 0 0 0 7.3 17.5h5.4a2 2 0 0 0 2-1.95l.7-11.05h.6a.75.75 0 0 0 0-1.5h-3.2l-.4-.45a1 1 0 0 0-.9-.55h-3Zm1 4a.75.75 0 0 1 .75.75v7a.75.75 0 0 1-1.5 0v-7A.75.75 0 0 1 9.5 6Zm3 0a.75.75 0 0 1 .75.75v7a.75.75 0 0 1-1.5 0v-7a.75.75 0 0 1 .75-.75Z" clip-rule="evenodd"/></svg>
                        </button>
                    </td>
                </tr>
            `).join('');
            recalculateTotals();
        }

        function addToCart(productId) {
            const product = getProductById(productId);
            if (!product) return;

            const index = getItemIndex(productId);
            if (index >= 0) {
                cart[index].qty = Math.min(Number(cart[index].qty) + 1, Number(product.stock));
            } else {
                cart.push({ ...product, qty: 1 });
            }
            renderCart();
        }

        function syncCustomerFields(prefix) {
            document.getElementById(`${prefix}customer-name-input`).value = document.getElementById('customer_name').value;
            document.getElementById(`${prefix}customer-phone-input`).value = document.getElementById('customer_phone').value;
        }

        document.getElementById('product-search').addEventListener('input', (event) => renderProducts(event.target.value));
        document.getElementById('product-search').addEventListener('keydown', (event) => {
            if (event.key !== 'Enter') return;
            event.preventDefault();
            const query = event.target.value.trim().toLowerCase();
            const exactMatch = products.find((product) => [product.name, product.code, product.barcode]
                .some((value) => value && String(value).toLowerCase() === query));
            if (exactMatch) {
                addToCart(exactMatch.id);
                event.target.value = '';
                renderProducts();
            }
        });

        document.getElementById('discount-percent').addEventListener('input', recalculateTotals);
        document.getElementById('discount-amount').addEventListener('input', recalculateTotals);
        document.getElementById('tax').addEventListener('input', recalculateTotals);
        document.getElementById('other-fee').addEventListener('input', recalculateTotals);
        document.getElementById('paid-amount').addEventListener('input', recalculateTotals);

        document.addEventListener('click', (event) => {
            const productButton = event.target.closest('.product-item');
            if (productButton) addToCart(Number(productButton.dataset.id));

            const quantityButton = event.target.closest('.quantity-button');
            if (quantityButton) {
                const itemIndex = getItemIndex(Number(quantityButton.dataset.id));
                if (itemIndex < 0) return;
                const item = cart[itemIndex];
                item.qty = quantityButton.dataset.action === 'plus'
                    ? Math.min(Number(item.qty) + 1, Number(item.stock))
                    : Math.max(Number(item.qty) - 1, 1);
                renderCart();
            }

            const removeButton = event.target.closest('.remove-item');
            if (removeButton) {
                cart = cart.filter((item) => Number(item.id) !== Number(removeButton.dataset.id));
                renderCart();
            }

            const paymentButton = event.target.closest('.payment-method-btn');
            if (paymentButton) {
                selectedPaymentMethod = paymentButton.dataset.method;
                document.querySelectorAll('.payment-method-btn').forEach((button) => {
                    const selected = button === paymentButton;
                    button.setAttribute('aria-pressed', String(selected));
                    button.classList.toggle('border-emerald-300', selected);
                    button.classList.toggle('bg-emerald-50', selected);
                    button.classList.toggle('text-emerald-800', selected);
                    button.classList.toggle('border-slate-200', !selected);
                    button.classList.toggle('text-gray-600', !selected);
                });
                recalculateTotals();
            }

            const quickPaymentButton = event.target.closest('.quick-payment-btn');
            if (quickPaymentButton && selectedPaymentMethod === 'cash') {
                const grandTotal = Number(document.getElementById('summary-grand-total').textContent.replace(/[^\d]/g, '')) || 0;
                document.getElementById('paid-amount').value = Number(quickPaymentButton.dataset.amount) || grandTotal;
                recalculateTotals();
            }
        });

        document.addEventListener('input', (event) => {
            if (!event.target.classList.contains('qty-input')) return;
            const itemIndex = getItemIndex(Number(event.target.dataset.id));
            if (itemIndex < 0) return;
            const item = cart[itemIndex];
            item.qty = Math.max(1, Math.min(Number(event.target.value || 1), Number(item.stock)));
            renderCart();
        });

        document.getElementById('cashier-form').addEventListener('submit', (event) => {
            const subtotal = cart.reduce((sum, item) => sum + Number(item.price) * Number(item.qty), 0);
            const discount = Math.min(subtotal, subtotal * (Number(document.getElementById('discount-percent').value || 0) / 100) + Number(document.getElementById('discount-amount').value || 0));
            const total = Math.max(0, subtotal - discount + Number(document.getElementById('tax').value || 0) + Number(document.getElementById('other-fee').value || 0));
            if (!cart.length || Number(document.getElementById('paid-amount').value || 0) < total) {
                event.preventDefault();
                return;
            }

            document.getElementById('cart-input').value = JSON.stringify(cart);
            document.getElementById('transaction-id-input').value = new URLSearchParams(window.location.search).get('resume_id') || '';
            syncCustomerFields('');
            document.getElementById('paid-amount-input').value = document.getElementById('paid-amount').value;
            document.getElementById('payment-method-input').value = selectedPaymentMethod;
        });

        document.getElementById('hold-form').addEventListener('submit', (event) => {
            if (!cart.length) {
                event.preventDefault();
                return;
            }
            document.getElementById('hold-cart-input').value = JSON.stringify(cart);
            document.getElementById('hold-transaction-id-input').value = new URLSearchParams(window.location.search).get('resume_id') || '';
            syncCustomerFields('hold-');
        });

        document.getElementById('cancel-button').addEventListener('click', () => {
            if (!cart.length && !document.getElementById('customer_name').value) return;
            if (!confirm('Kosongkan semua produk dan informasi transaksi ini?')) return;
            cart = [];
            document.getElementById('customer_name').value = '';
            document.getElementById('customer_phone').value = '';
            document.getElementById('discount-percent').value = 0;
            document.getElementById('discount-amount').value = 0;
            document.getElementById('tax').value = 0;
            document.getElementById('other-fee').value = 0;
            document.getElementById('paid-amount').value = 0;
            renderCart();
        });

        renderProducts();
        renderCart();
    </script>
</x-app-layout>
