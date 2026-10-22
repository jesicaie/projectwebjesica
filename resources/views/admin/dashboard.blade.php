<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-medium text-indigo-600">Ringkasan toko</p>
                <h1 class="text-2xl font-bold tracking-tight text-gray-900">JesikaMart</h1>
            </div>
            <p class="text-sm text-gray-500">{{ now()->translatedFormat('l, d F Y') }}</p>
        </div>
    </x-slot>

    <div class="min-h-[calc(100vh-8rem)] bg-slate-50 py-8 sm:py-10">
        <div class="mx-auto max-w-7xl space-y-7 px-4 sm:px-6 lg:px-8">
            <section class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-slate-900 via-indigo-950 to-indigo-800 px-6 py-8 text-white shadow-lg sm:px-9 sm:py-10">
                <div class="relative z-10 max-w-2xl">
                    <p class="text-sm font-semibold text-indigo-200">Halo, {{ $user->name }} 👋</p>
                    <h2 class="mt-2 text-2xl font-bold tracking-tight sm:text-3xl">Semua toko dalam kendali.</h2>
                    <p class="mt-2 max-w-xl text-sm leading-6 text-indigo-100 sm:text-base">
                        Pantau performa penjualan, aktivitas transaksi, dan kondisi stok hari ini dari satu tempat.
                    </p>
                    <div class="mt-6 flex flex-wrap gap-3">
                        <a href="{{ route('products.index') }}" class="inline-flex items-center gap-2 rounded-lg bg-white px-4 py-2.5 text-sm font-semibold text-indigo-900 shadow-sm transition hover:bg-indigo-50 focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-indigo-900">
                            Kelola produk
                            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M3 10a.75.75 0 0 1 .75-.75h10.69L10.22 5.03a.75.75 0 1 1 1.06-1.06l5.5 5.5a.75.75 0 0 1 0 1.06l-5.5 5.5a.75.75 0 1 1-1.06-1.06l4.22-4.22H3.75A.75.75 0 0 1 3 10Z" clip-rule="evenodd"/></svg>
                        </a>
                    </div>
                </div>
                <div class="pointer-events-none absolute -right-10 -top-24 h-80 w-80 rounded-full border-[40px] border-white/5"></div>
                <div class="pointer-events-none absolute -bottom-40 right-28 h-72 w-72 rounded-full bg-indigo-500/20 blur-3xl"></div>
            </section>

            <section aria-label="Statistik toko" class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <article class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Pendapatan bulan ini</p>
                            <p class="mt-3 text-2xl font-bold tracking-tight text-gray-900">Rp {{ number_format((float) $monthRevenue, 0, ',', '.') }}</p>
                            <p class="mt-1 text-xs text-gray-500">Dari transaksi lunas</p>
                        </div>
                        <span class="rounded-lg bg-emerald-50 p-2.5 text-emerald-600">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 17l6-6 4 4 8-8M15 7h6v6"/></svg>
                        </span>
                    </div>
                </article>
                <article class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Transaksi hari ini</p>
                            <p class="mt-3 text-2xl font-bold tracking-tight text-gray-900">{{ number_format($todayTransactions, 0, ',', '.') }}</p>
                            <p class="mt-1 text-xs text-gray-500">Transaksi berhasil</p>
                        </div>
                        <span class="rounded-lg bg-indigo-50 p-2.5 text-indigo-600">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M7 3.75h10A2.25 2.25 0 0 1 19.25 6v15.25L12 17.5l-7.25 3.75V6A2.25 2.25 0 0 1 7 3.75Z"/><path stroke-linecap="round" d="M9 8h6m-6 4h6"/></svg>
                        </span>
                    </div>
                </article>
                <article class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Produk aktif</p>
                            <p class="mt-3 text-2xl font-bold tracking-tight text-gray-900">{{ number_format($activeProducts, 0, ',', '.') }}</p>
                            <p class="mt-1 text-xs text-gray-500">Tersedia untuk dijual</p>
                        </div>
                        <span class="rounded-lg bg-sky-50 p-2.5 text-sky-600">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m12 3 8.25 4.5v9L12 21l-8.25-4.5v-9L12 3Z"/><path stroke-linecap="round" stroke-linejoin="round" d="m3.9 7.65 8.1 4.5 8.1-4.5M12 12.15V21"/></svg>
                        </span>
                    </div>
                </article>
                <article class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Stok menipis</p>
                            <p class="mt-3 text-2xl font-bold tracking-tight {{ $lowStockProducts > 0 ? 'text-amber-600' : 'text-gray-900' }}">{{ number_format($lowStockProducts, 0, ',', '.') }}</p>
                            <p class="mt-1 text-xs text-gray-500">Produk dengan stok ≤ 5</p>
                        </div>
                        <span class="rounded-lg bg-amber-50 p-2.5 text-amber-600">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 3h.01M10.3 3.9 2.9 17a2 2 0 0 0 1.75 3h14.7a2 2 0 0 0 1.75-3l-7.4-13.1a2 2 0 0 0-3.4 0Z"/></svg>
                        </span>
                    </div>
                </article>
            </section>

            <section class="grid gap-6 lg:grid-cols-3">
                <article class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm sm:p-6 lg:col-span-2">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <h3 class="text-base font-bold text-gray-900">Performa penjualan</h3>
                            <p class="mt-1 text-sm text-gray-500">Pendapatan harian selama 7 hari terakhir</p>
                        </div>
                        <span class="rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-700">7 hari terakhir</span>
                    </div>
                    <div class="mt-8 grid h-48 grid-cols-7 gap-2 sm:gap-4" role="img" aria-label="Grafik pendapatan 7 hari terakhir">
                        @foreach ($salesChart as $day)
                            <div class="flex min-w-0 flex-col items-center justify-end">
                                <span class="mb-2 hidden text-[11px] font-medium text-gray-500 sm:block">Rp {{ number_format($day['total'], 0, ',', '.') }}</span>
                                <div class="flex h-32 w-full items-end justify-center rounded-t-md bg-slate-50">
                                    <div class="w-5/6 rounded-t-md bg-gradient-to-t from-indigo-600 to-violet-400 transition-all sm:max-w-10" style="height: {{ $day['total'] > 0 ? max(8, ($day['total'] / $maxDailySales) * 100) : 3 }}%" title="{{ $day['date'] }}: Rp {{ number_format($day['total'], 0, ',', '.') }}"></div>
                                </div>
                                <span class="mt-2 text-[11px] font-medium text-gray-500">{{ $day['label'] }}</span>
                                <span class="text-[10px] text-gray-400">{{ $day['date'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </article>

                <article class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm sm:p-6">
                    <div class="flex items-start justify-between">
                        <div>
                            <h3 class="text-base font-bold text-gray-900">Perlu ditambah</h3>
                            <p class="mt-1 text-sm text-gray-500">Stok produk hampir habis</p>
                        </div>
                        <span class="rounded-full bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-700">{{ $lowStockProducts }} produk</span>
                    </div>
                    <div class="mt-5 space-y-4">
                        @forelse ($lowStockList as $product)
                            <div class="flex items-center justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-semibold text-gray-800">{{ $product->name }}</p>
                                    <p class="mt-0.5 text-xs text-gray-500">{{ $product->category ?: 'Tanpa kategori' }}</p>
                                </div>
                                <span class="shrink-0 rounded-md px-2.5 py-1 text-xs font-bold {{ $product->stock === 0 ? 'bg-rose-50 text-rose-700' : 'bg-amber-50 text-amber-700' }}">
                                    {{ $product->stock }} tersisa
                                </span>
                            </div>
                        @empty
                            <div class="rounded-lg bg-emerald-50 px-4 py-6 text-center">
                                <p class="text-sm font-semibold text-emerald-800">Stok aman</p>
                                <p class="mt-1 text-xs text-emerald-700">Belum ada produk yang perlu ditambah.</p>
                            </div>
                        @endforelse
                    </div>
                    <a href="{{ route('products.index') }}" class="mt-5 inline-flex items-center gap-1 text-sm font-semibold text-indigo-700 hover:text-indigo-900">
                        Lihat semua produk <span aria-hidden="true">→</span>
                    </a>
                </article>
            </section>

            <section class="overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm">
                <div class="flex flex-wrap items-center justify-between gap-3 px-5 py-5 sm:px-6">
                    <div>
                        <h3 class="text-base font-bold text-gray-900">Transaksi terbaru</h3>
                        <p class="mt-1 text-sm text-gray-500">Aktivitas penjualan yang baru masuk</p>
                    </div>
                    <a href="{{ route('admin.transactions') }}" class="text-sm font-semibold text-indigo-700 hover:text-indigo-900">Lihat semua</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead class="bg-slate-50">
                            <tr>
                                <th scope="col" class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 sm:px-6">No. transaksi</th>
                                <th scope="col" class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Kasir</th>
                                <th scope="col" class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Waktu</th>
                                <th scope="col" class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500 sm:px-6">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($recentTransactions as $transaction)
                                <tr class="transition hover:bg-slate-50">
                                    <td class="whitespace-nowrap px-5 py-4 text-sm font-semibold text-gray-900 sm:px-6">{{ $transaction->transaction_number }}</td>
                                    <td class="whitespace-nowrap px-5 py-4 text-sm text-gray-600">{{ $transaction->user?->name ?? 'Pengguna dihapus' }}</td>
                                    <td class="whitespace-nowrap px-5 py-4 text-sm text-gray-500">{{ $transaction->created_at->translatedFormat('d M Y, H:i') }}</td>
                                    <td class="whitespace-nowrap px-5 py-4 text-right text-sm font-semibold text-gray-900 sm:px-6">Rp {{ number_format((float) $transaction->grand_total, 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-10 text-center">
                                        <p class="text-sm font-semibold text-gray-700">Belum ada transaksi</p>
                                        <p class="mt-1 text-sm text-gray-500">Transaksi yang berhasil akan muncul di sini.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
