<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class StrukController extends Controller
{
    public function index(Request $request): View
    {
        $transaction = $this->resolveTransaction($request);

        return view('kasir.receipt', compact('transaction'));
    }

    public function show(Request $request, Transaction $transaction): View
    {
        abort_unless($transaction->user_id === Auth::id(), 404);

        $transaction->load('details.product', 'user');

        return view('kasir.receipt', compact('transaction'));
    }

    protected function resolveTransaction(Request $request): Transaction
    {
        $transactionId = $request->query('transaction_id')
            ?? $request->route('transaction_id')
            ?? $request->route('transaction');

        if (empty($transactionId)) {
            return Transaction::with('details.product', 'user')
                ->where('user_id', Auth::id())
                ->where('status', 'paid')
                ->latest()
                ->firstOrFail();
        }

        $transaction = Transaction::with('details.product', 'user')->findOrFail($transactionId);

        abort_unless($transaction->user_id === Auth::id() && $transaction->status === 'paid', 404);

        return $transaction;
    }
}

if (! class_exists('App\\Http\\Controllers\\strukcontroller')) {
    class_alias(StrukController::class, 'App\\Http\\Controllers\\strukcontroller');
}
