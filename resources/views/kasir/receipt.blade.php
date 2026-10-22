<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk {{ $transaction->transaction_number }}</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; padding: 24px; background: #f1f5f9; color: #111827; font-family: Arial, sans-serif; }
        .receipt { max-width: 420px; margin: 0 auto; padding: 28px; border: 1px solid #e2e8f0; border-radius: 12px; background: white; box-shadow: 0 8px 28px rgb(15 23 42 / 8%); }
        .center { text-align: center; }
        .muted { color: #64748b; }
        .small { font-size: 12px; }
        .meta { display: grid; grid-template-columns: 1fr auto; gap: 7px 12px; font-size: 13px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 7px 0; font-size: 13px; }
        .items th { border-bottom: 1px solid #e2e8f0; color: #64748b; font-size: 11px; text-transform: uppercase; }
        .items td { vertical-align: top; }
        .line { border: 0; border-top: 1px dashed #cbd5e1; margin: 18px 0; }
        .totals td { padding: 5px 0; }
        .grand-total td { padding-top: 10px; border-top: 1px solid #e2e8f0; font-size: 16px; font-weight: 700; }
        .actions { display: flex; justify-content: center; gap: 10px; margin-top: 22px; }
        .actions a, .actions button { padding: 9px 12px; border: 1px solid #d1d5db; border-radius: 8px; background: white; color: #1f2937; font-size: 12px; font-weight: 600; text-decoration: none; cursor: pointer; }
        .notice { margin-bottom: 16px; border-radius: 8px; padding: 10px 12px; font-size: 13px; }
        .success { background: #ecfdf5; color: #047857; }
        .warning { background: #fffbeb; color: #b45309; }
        @media print {
            @page { margin: 8mm; }
            body { padding: 0; background: white; }
            .receipt { max-width: none; padding: 0; border: 0; border-radius: 0; box-shadow: none; }
            .no-print { display: none !important; }
        }
        @media (max-width: 480px) {
            body { padding: 12px; }
            .receipt { padding: 20px; }
        }
    </style>
</head>
<body>
    <main class="receipt">
        @if (session('success'))
            <div class="notice success no-print">{{ session('success') }}</div>
        @endif
        @if (session('warning'))
            <div class="notice warning no-print">{{ session('warning') }}</div>
        @endif
        @if (session('print_status'))
            <div class="notice success no-print">{{ session('print_status') }}</div>
        @endif

        <header class="center">
            <h1 style="margin: 0 0 6px; font-size: 19px;">TOKO RETAIL MAKMUR</h1>
            <p class="small muted" style="margin: 0;">Jl. Mawar No. 12</p>
            <p class="small muted" style="margin: 6px 0 0;">{{ $transaction->created_at->translatedFormat('d F Y, H:i') }}</p>
        </header>

        <hr class="line">

        <div class="meta">
            <span class="muted">No. transaksi</span><strong>{{ $transaction->transaction_number }}</strong>
            <span class="muted">Kasir</span><span>{{ $transaction->user?->name ?? 'Kasir' }}</span>
            @if ($transaction->customer_name)
                <span class="muted">Pelanggan</span><span>{{ $transaction->customer_name }}</span>
            @endif
            @if ($transaction->customer_phone)
                <span class="muted">Telepon</span><span>{{ $transaction->customer_phone }}</span>
            @endif
            <span class="muted">Pembayaran</span>
            <span>{{ ['cash' => 'Tunai', 'qris' => 'QRIS', 'debit' => 'Debit', 'credit' => 'Kredit', 'ewallet' => 'E-Wallet', 'transfer' => 'Transfer'][$transaction->payment_method] ?? 'Tunai' }}</span>
        </div>

        <hr class="line">

        <table class="items">
            <thead>
                <tr><th align="left">Barang</th><th align="right">Jumlah</th><th align="right">Subtotal</th></tr>
            </thead>
            <tbody>
                @foreach ($transaction->details as $detail)
                    <tr>
                        <td>{{ $detail->product_name }}<br><span class="small muted">Rp {{ number_format((float) $detail->price, 0, ',', '.') }} / item</span></td>
                        <td align="right">{{ $detail->qty }}</td>
                        <td align="right">Rp {{ number_format((float) $detail->subtotal, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <hr class="line">

        @php
            $totalDiscount = min(
                (float) $transaction->subtotal,
                ((float) $transaction->subtotal * (float) $transaction->discount_percent / 100) + (float) $transaction->discount_amount
            );
        @endphp
        <table class="totals">
            <tr><td class="muted">Subtotal</td><td align="right">Rp {{ number_format((float) $transaction->subtotal, 0, ',', '.') }}</td></tr>
            @if ($totalDiscount > 0)
                <tr><td class="muted">Diskon</td><td align="right">- Rp {{ number_format($totalDiscount, 0, ',', '.') }}</td></tr>
            @endif
            @if ((float) $transaction->tax > 0)
                <tr><td class="muted">Pajak</td><td align="right">Rp {{ number_format((float) $transaction->tax, 0, ',', '.') }}</td></tr>
            @endif
            @if ((float) $transaction->other_fee > 0)
                <tr><td class="muted">Biaya lain</td><td align="right">Rp {{ number_format((float) $transaction->other_fee, 0, ',', '.') }}</td></tr>
            @endif
            <tr class="grand-total"><td>Total</td><td align="right">Rp {{ number_format((float) $transaction->grand_total, 0, ',', '.') }}</td></tr>
            <tr><td class="muted">Dibayar</td><td align="right">Rp {{ number_format((float) $transaction->paid_amount, 0, ',', '.') }}</td></tr>
            <tr><td class="muted">Kembalian</td><td align="right">Rp {{ number_format((float) $transaction->change_amount, 0, ',', '.') }}</td></tr>
        </table>

        <hr class="line">
        <p class="center small" style="margin: 0;">Terima kasih atas kunjungan Anda.</p>

        <div class="actions no-print">
            <a href="{{ route('kasir.transactions') }}">Riwayat transaksi</a>
            <a href="{{ route('kasir.transaksi') }}">Transaksi baru</a>
            <a href="{{ route('kasir.transaksi.print', $transaction) }}">Cetak struk</a>
            <button type="button" onclick="window.print()">Print</button>
        </div>
    </main>
</body>
</html>
