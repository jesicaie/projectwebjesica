<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-medium text-emerald-700">Area kasir</p>
                <h1 class="text-2xl font-bold tracking-tight text-gray-900">JesikaMart</h1>
            </div>
            <p class="text-sm text-gray-500">{{ now()->translatedFormat('l, d F Y') }}</p>
        </div>
    </x-slot>

    <div class="min-h-[calc(100vh-8rem)] bg-slate-50 py-8 sm:py-10">
        <div class="mx-auto max-w-7xl space-y-7 px-4 sm:px-6 lg:px-8">
            <section class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-emerald-950 via-teal-900 to-emerald-700 px-6 py-8 text-white shadow-lg sm:px-9 sm:py-10">
                <div class="relative z-10 max-w-2xl">
                    <p class="text-sm font-semibold text-emerald-200">Selamat bertugas, {{ $user->name }}</p>
                    <h2 class="mt-2 text-2xl font-bold tracking-tight sm:text-3xl">Siap melayani pelanggan?</h2>
                    <p class="mt-2 max-w-xl text-sm leading-6 text-emerald-100 sm:text-base">
                        Mulai transaksi baru atau lanjutkan pesanan yang masih tersimpan.
                    </p>
                    <a href="{{ route('kasir.transaksi') }}" class="mt-6 inline-flex items-center gap-2 rounded-lg bg-white px-4 py-2.5 text-sm font-semibold text-emerald-900 shadow-sm transition hover:bg-emerald-50 focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-emerald-900">
                        Mulai transaksi
                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M3 10a.75.75 0 0 1 .75-.75h10.69L10.22 5.03a.75.75 0 1 1 1.06-1.06l5.5 5.5a.75.75 0 0 1 0 1.06l-5.5 5.5a.75.75 0 1 1-1.06-1.06l4.22-4.22H3.75A.75.75 0 0 1 3 10Z" clip-rule="evenodd"/></svg>
                    </a>
                </div>
                <div class="pointer-events-none absolute -right-10 -top-24 h-80 w-80 rounded-full border-[40px] border-white/5"></div>
                <div class="pointer-events-none absolute -bottom-40 right-28 h-72 w-72 rounded-full bg-teal-400/20 blur-3xl"></div>
            </section>

            <section aria-label="Ringkasan shift" class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <article class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Penjualan hari ini</p>
                            <p class="mt-3 text-2xl font-bold tracking-tight text-gray-900">Rp {{ number_format((float) $todayRevenue, 0, ',', '.') }}</p>
                            <p class="mt-1 text-xs text-gray-500">Total transaksi lunas</p>
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
                            <p class="text-sm font-medium text-gray-500">Pesanan ditahan</p>
                            <p class="mt-3 text-2xl font-bold tracking-tight text-gray-900">{{ number_format($heldTransactions, 0, ',', '.') }}</p>
                            <p class="mt-1 text-xs text-gray-500">Pesanan milik Anda</p>
                        </div>
                        <span class="rounded-lg bg-amber-50 p-2.5 text-amber-600">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M8 4.75h8A2.25 2.25 0 0 1 18.25 7v10A2.25 2.25 0 0 1 16 19.25H8A2.25 2.25 0 0 1 5.75 17V7A2.25 2.25 0 0 1 8 4.75Z"/><path stroke-linecap="round" d="M9 9h6m-6 3h6"/></svg>
                        </span>
                    </div>
                </article>
                <article class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Produk tersedia</p>
                            <p class="mt-3 text-2xl font-bold tracking-tight text-gray-900">{{ number_format($activeProducts, 0, ',', '.') }}</p>
                            <p class="mt-1 text-xs text-gray-500">Produk aktif di toko</p>
                        </div>
                        <span class="rounded-lg bg-sky-50 p-2.5 text-sky-600">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m12 3 8.25 4.5v9L12 21l-8.25-4.5v-9L12 3Z"/><path stroke-linecap="round" stroke-linejoin="round" d="m3.9 7.65 8.1 4.5 8.1-4.5M12 12.15V21"/></svg>
                        </span>
                    </div>
                </article>
            </section>

            <section class="overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm">
                <div class="flex flex-wrap items-center justify-between gap-3 px-5 py-5 sm:px-6">
                    <div>
                        <h3 class="text-base font-bold text-gray-900">Transaksi terakhir</h3>
                        <p class="mt-1 text-sm text-gray-500">Riwayat penjualan Anda</p>
                    </div>
                    <a href="{{ route('kasir.transactions') }}" class="text-sm font-semibold text-emerald-700 hover:text-emerald-900">Lihat riwayat <span aria-hidden="true">→</span></a>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead class="bg-slate-50">
                            <tr>
                                <th scope="col" class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 sm:px-6">No. transaksi</th>
                                <th scope="col" class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Pelanggan</th>
                                <th scope="col" class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Waktu</th>
                                <th scope="col" class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500 sm:px-6">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($recentTransactions as $transaction)
                                <tr class="transition hover:bg-slate-50">
                                    <td class="whitespace-nowrap px-5 py-4 text-sm font-semibold text-gray-900 sm:px-6">
                                        <a href="{{ route('kasir.transaksi.show', $transaction) }}" class="hover:text-emerald-700">{{ $transaction->transaction_number }}</a>
                                    </td>
                                    <td class="whitespace-nowrap px-5 py-4 text-sm text-gray-600">{{ $transaction->customer_name ?: 'Pelanggan umum' }}</td>
                                    <td class="whitespace-nowrap px-5 py-4 text-sm text-gray-500">{{ $transaction->created_at->translatedFormat('d M Y, H:i') }}</td>
                                    <td class="whitespace-nowrap px-5 py-4 text-right text-sm font-semibold text-gray-900 sm:px-6">Rp {{ number_format((float) $transaction->grand_total, 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-10 text-center">
                                        <p class="text-sm font-semibold text-gray-700">Belum ada transaksi hari ini</p>
                                        <p class="mt-1 text-sm text-gray-500">Transaksi Anda akan muncul di sini setelah pembayaran berhasil.</p>
                                        <a href="{{ route('kasir.transaksi') }}" class="mt-4 inline-flex rounded-lg bg-emerald-700 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-800">Mulai transaksi</a>
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
