<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Transaction;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function index(): View
    {
        $today = now()->startOfDay();
        $weekStart = $today->copy()->subDays(6);
        $monthStart = now()->startOfMonth();

        $weeklySales = Transaction::query()
            ->where('status', 'paid')
            ->whereBetween('created_at', [$weekStart, now()])
            ->get(['created_at', 'grand_total'])
            ->groupBy(fn (Transaction $transaction) => $transaction->created_at->toDateString());

        $salesChart = collect(range(0, 6))->map(function (int $offset) use ($weekStart, $weeklySales): array {
            $date = $weekStart->copy()->addDays($offset);

            return [
                'label' => $date->translatedFormat('D'),
                'date' => $date->translatedFormat('d M'),
                'total' => (float) $weeklySales->get($date->toDateString(), collect())->sum('grand_total'),
            ];
        });

        return view('admin.dashboard', [
            'user' => auth()->user(),
            'todayTransactions' => Transaction::where('status', 'paid')
                ->whereDate('created_at', $today)
                ->count(),
            'monthRevenue' => Transaction::where('status', 'paid')
                ->where('created_at', '>=', $monthStart)
                ->sum('grand_total'),
            'activeProducts' => Product::where('is_active', true)->count(),
            'lowStockProducts' => Product::where('is_active', true)->where('stock', '<=', 5)->count(),
            'salesChart' => $salesChart,
            'maxDailySales' => max(1, (float) $salesChart->max('total')),
            'recentTransactions' => Transaction::with('user')
                ->where('status', 'paid')
                ->latest()
                ->take(6)
                ->get(),
            'lowStockList' => Product::where('is_active', true)
                ->where('stock', '<=', 5)
                ->orderBy('stock')
                ->take(5)
                ->get(),
        ]);
    }
}
