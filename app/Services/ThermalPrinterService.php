<?php

namespace App\Services;

use App\Models\Transaction;
use Illuminate\Support\Facades\Log;
use Mike42\Escpos\PrintConnectors\FilePrintConnector;
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;
use Mike42\Escpos\PrintConnectors\PrintConnector;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\Printer;

class ThermalPrinterService
{
    public function print(Transaction $transaction): bool
    {
        if (! config('thermal-printer.enabled', false)) {
            return false;
        }

        $printer = null;
        try {
            $connector = $this->makeConnector();
            if ($connector === null) {
                throw new \RuntimeException('Unsupported thermal printer driver: '.config('thermal-printer.driver'));
            }

            $printer = new Printer($connector);
            $this->writeReceipt($printer, $transaction);
            $printer->cut();
            $printer->close();

            return true;
        } catch (\Throwable $e) {
            Log::error('Thermal printer failed', [
                'transaction_id' => $transaction->id,
                'message' => $e->getMessage(),
            ]);

            if ($printer !== null) {
                try {
                    $printer->close();
                } catch (\Throwable) {
                    // Preserve the original printer error when cleanup also fails.
                }
            }

            return false;
        }
    }

    protected function makeConnector(): ?PrintConnector
    {
        $driver = config('thermal-printer.driver', 'file');

        return match ($driver) {
            'network' => new NetworkPrintConnector(
                config('thermal-printer.host', '127.0.0.1'),
                (int) config('thermal-printer.port', 9100)
            ),
            'windows' => new WindowsPrintConnector(config('thermal-printer.name', 'POS-58')),
            'file' => new FilePrintConnector(config('thermal-printer.device', '/dev/usb/lp0')),
            default => null,
        };
    }

    protected function writeReceipt(Printer $printer, Transaction $transaction): void
    {
        $transaction->loadMissing('details.product', 'user');

        $printer->initialize();
        $printer->setJustification(Printer::JUSTIFY_CENTER);
        $printer->selectPrintMode(Printer::MODE_DOUBLE_WIDTH | Printer::MODE_DOUBLE_HEIGHT);
        $printer->text("Jesika Mart\n");
        $printer->selectPrintMode();
        $printer->text("Jl. MENIA GANK\n");
        $printer->text($transaction->created_at->translatedFormat('d F Y H:i')."\n");
        $printer->feed();

        $printer->setJustification(Printer::JUSTIFY_LEFT);
        $printer->text("No. Trx : {$transaction->transaction_number}\n");
        $printer->text('Kasir   : '.($transaction->user?->name ?? 'Kasir')."\n");
        $printer->text('Metode  : '.strtoupper((string) ($transaction->payment_method ?? 'cash'))."\n");
        $printer->text("--------------------------------\n");
        $printer->text($this->formatColumn(['ITEM', 'QTY', 'SUB']));
        $printer->text("--------------------------------\n");

        foreach ($transaction->details as $detail) {
            $itemName = $this->truncate($detail->product_name, 18);
            $qty = (string) $detail->qty;
            $subtotal = 'Rp '.number_format((float) $detail->subtotal, 0, ',', '.');

            $printer->text($this->formatColumn([$itemName, $qty, $subtotal]));
        }

        $printer->text("--------------------------------\n");
        $printer->text($this->formatColumn(['Subtotal', '', 'Rp '.number_format((float) $transaction->subtotal, 0, ',', '.')], false));
        $totalDiscount = min(
            (float) $transaction->subtotal,
            ((float) $transaction->subtotal * (float) $transaction->discount_percent / 100) + (float) $transaction->discount_amount
        );
        $printer->text($this->formatColumn(['Diskon', '', 'Rp '.number_format($totalDiscount, 0, ',', '.')], false));
        $printer->text($this->formatColumn(['Pajak', '', 'Rp '.number_format((float) $transaction->tax, 0, ',', '.')], false));
        $printer->text($this->formatColumn(['Biaya', '', 'Rp '.number_format((float) $transaction->other_fee, 0, ',', '.')], false));
        $printer->text($this->formatColumn(['TOTAL', '', 'Rp '.number_format((float) $transaction->grand_total, 0, ',', '.')], false));
        $printer->text($this->formatColumn(['Bayar', '', 'Rp '.number_format((float) $transaction->paid_amount, 0, ',', '.')], false));
        $printer->text($this->formatColumn(['Kembali', '', 'Rp '.number_format((float) $transaction->change_amount, 0, ',', '.')], false));
        $printer->feed();
        $printer->setJustification(Printer::JUSTIFY_CENTER);
        $printer->text("SEMOGA SUKSES.\n");
        $printer->feed(2);
    }

    protected function formatColumn(array $values, bool $compact = true): string
    {
        $left = trim((string) ($values[0] ?? ''));
        $middle = trim((string) ($values[1] ?? ''));
        $right = trim((string) ($values[2] ?? ''));

        if ($compact) {
            return str_pad($left, 18, ' ').str_pad($middle, 5, ' ', STR_PAD_LEFT).str_pad($right, 12, ' ', STR_PAD_LEFT)."\n";
        }

        return str_pad($left, 18, ' ').str_pad($right, 15, ' ', STR_PAD_LEFT)."\n";
    }

    protected function truncate(string $value, int $length): string
    {
        if (function_exists('mb_strimwidth')) {
            return mb_strimwidth($value, 0, $length, '');
        }

        return substr($value, 0, $length);
    }
}
