<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-medium text-emerald-700">Kasir</p>
                <h1 class="text-2xl font-bold tracking-tight text-gray-900">Riwayat transaksi</h1>
            </div>
            <a href="{{ route('kasir.transaksi') }}" class="inline-flex w-fit items-center gap-2 rounded-lg bg-emerald-700 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-800">
                <span aria-hidden="true">+</span> Transaksi baru
            </a>
        </div>
    </x-slot>

    <div class="min-h-[calc(100vh-8rem)] bg-slate-50 py-8 sm:py-10">
        <div class="mx-auto max-w-7xl space-y-5 px-4 sm:px-6 lg:px-8">
            @if (session('success'))
                <div role="status" class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">{{ session('success') }}</div>
            @endif

            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                <form method="GET" action="{{ route('kasir.transactions') }}" class="grid gap-3 sm:grid-cols-[minmax(0,1fr)_190px_auto_auto] sm:items-end">
                    <div>
                        <label for="search" class="block text-xs font-semibold text-gray-600">Cari transaksi atau pelanggan</label>
                        <input id="search" name="search" type="search" value="{{ $filters['search'] ?? '' }}" placeholder="Contoh: TRX-20261004-001" class="mt-1.5 block w-full rounded-lg border-slate-200 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                    </div>
                    <div>
                        <label for="status" class="block text-xs font-semibold text-gray-600">Status</label>
                        <select id="status" name="status" class="mt-1.5 block w-full rounded-lg border-slate-200 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                            <option value="">Semua status</option>
                            <option value="paid" @selected(($filters['status'] ?? '') === 'paid')>Lunas</option>
                            <option value="held" @selected(($filters['status'] ?? '') === 'held')>Ditahan</option>
                        </select>
                    </div>
                    <button type="submit" class="rounded-lg bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-700">Cari</button>
                    <a href="{{ route('kasir.transactions') }}" class="rounded-lg border border-slate-200 px-4 py-2.5 text-center text-sm font-semibold text-gray-600 transition hover:bg-slate-50">Reset</a>
                </form>
            </section>

            <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4 sm:px-6">
                    <div>
                        <h2 class="text-base font-bold text-gray-900">Transaksi Anda</h2>
                        <p class="mt-1 text-sm text-gray-500">{{ $transactions->total() }} catatan transaksi</p>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-100">
                        <thead class="bg-slate-50">
                            <tr>
                                <th scope="col" class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 sm:px-6">No. transaksi</th>
                                <th scope="col" class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Pelanggan</th>
                                <th scope="col" class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Tanggal</th>
                                <th scope="col" class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500">Total</th>
                                <th scope="col" class="px-5 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500">Status</th>
                                <th scope="col" class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500 sm:px-6">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($transactions as $transaction)
                                <tr class="transition hover:bg-slate-50">
                                    <td class="whitespace-nowrap px-5 py-4 text-sm font-semibold text-gray-900 sm:px-6">{{ $transaction->transaction_number }}</td>
                                    <td class="whitespace-nowrap px-5 py-4 text-sm text-gray-600">{{ $transaction->customer_name ?: 'Pelanggan umum' }}</td>
                                    <td class="whitespace-nowrap px-5 py-4 text-sm text-gray-500">{{ $transaction->created_at->translatedFormat('d M Y, H:i') }}</td>
                                    <td class="whitespace-nowrap px-5 py-4 text-right text-sm font-semibold text-gray-900">Rp {{ number_format((float) $transaction->grand_total, 0, ',', '.') }}</td>
                                    <td class="whitespace-nowrap px-5 py-4 text-center">
                                        <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $transaction->status === 'paid' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
                                            {{ $transaction->status === 'paid' ? 'Lunas' : 'Ditahan' }}
                                        </span>
                                    </td>
                                    <td class="whitespace-nowrap px-5 py-4 text-right text-sm sm:px-6">
                                        @if ($transaction->status === 'paid')
                                            <a href="{{ route('kasir.transaksi.show', $transaction) }}" class="font-semibold text-emerald-700 hover:text-emerald-900">Lihat struk</a>
                                        @else
                                            <div class="flex items-center justify-end gap-3">
                                                <a href="{{ route('kasir.transaksi.resume', $transaction) }}" class="font-semibold text-amber-700 hover:text-amber-900">Lanjutkan</a>
                                                <form method="POST" action="{{ route('kasir.transaksi.cancel-held', $transaction) }}" onsubmit="return confirm('Batalkan pesanan ditahan ini?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="font-semibold text-rose-600 hover:text-rose-800">Batalkan</button>
                                                </form>
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center">
                                        <p class="text-sm font-semibold text-gray-700">Transaksi tidak ditemukan</p>
                                        <p class="mt-1 text-sm text-gray-500">Coba ubah kata kunci atau buat transaksi baru.</p>
                                        <a href="{{ route('kasir.transaksi') }}" class="mt-4 inline-flex rounded-lg bg-emerald-700 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-800">Transaksi baru</a>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($transactions->hasPages())
                    <div class="border-t border-slate-100 px-5 py-4 sm:px-6">{{ $transactions->links() }}</div>
                @endif
            </section>
        </div>
    </div>
</x-app-layout>
