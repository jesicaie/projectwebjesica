<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Transaction;
use Illuminate\View\View;

class KasirController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();

        return view('kasir.dashboard', [
            'user' => $user,
            'todayTransactions' => Transaction::where('user_id', $user->id)
                ->where('status', 'paid')
                ->whereDate('created_at', today())
                ->count(),
            'todayRevenue' => Transaction::where('user_id', $user->id)
                ->where('status', 'paid')
                ->whereDate('created_at', today())
                ->sum('grand_total'),
            'heldTransactions' => Transaction::where('user_id', $user->id)
                ->where('status', 'held')
                ->count(),
            'activeProducts' => Product::where('is_active', true)->where('stock', '>', 0)->count(),
            'recentTransactions' => Transaction::where('user_id', $user->id)
                ->where('status', 'paid')
                ->latest()
                ->take(5)
                ->get(),
        ]);
    }
}
