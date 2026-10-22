<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\KasirController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StrukController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', function () {
        $user = auth()->user();

        if ($user->role === 'admin') {
            return redirect()->route('admin.dashboard');
        }

        if ($user->role === 'kasir') {
            return redirect()->route('kasir.dashboard');
        }

        return view('dashboard');
    })->name('dashboard');

    Route::get('/shared-access', function () {
        return 'Halaman ini bisa diakses admin dan kasir.';
    })->middleware('role:admin,kasir');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'index'])->name('admin.dashboard');
    Route::get('/products', function () {
        return 'Halaman Products untuk Admin';
    })->name('admin.products');
    Route::get('/categories', function () {
        return 'Halaman Categories untuk Admin';
    })->name('admin.categories');
    Route::get('/transactions', function () {
        return 'Halaman Transactions untuk Admin';
    })->name('admin.transactions');
    Route::get('/users', function () {
        return 'Halaman Users untuk Admin';
    })->name('admin.users');
    Route::get('/reports', function () {
        return 'Halaman Reports untuk Admin';
    })->name('admin.reports');
});

Route::middleware(['auth', 'admin'])->group(function () {
    Route::resource('products', ProductController::class);
});

Route::middleware(['auth', 'kasir'])->prefix('kasir')->group(function () {
    Route::get('/', [TransactionController::class, 'index'])->name('kasir.index');
    Route::get('/dashboard', [KasirController::class, 'index'])->name('kasir.dashboard');
    Route::get('/transaksi', [TransactionController::class, 'index'])->name('kasir.transaksi');
    Route::post('/transaksi', [TransactionController::class, 'store'])->name('kasir.transaksi.store');
    Route::post('/transaksi/hold', [TransactionController::class, 'hold'])->name('kasir.transaksi.hold');
    Route::get('/transaksi/{transaction}', [TransactionController::class, 'show'])->name('kasir.transaksi.show');
    Route::get('/transaksi/{transaction}/print', [TransactionController::class, 'print'])->name('kasir.transaksi.print');
    Route::get('/transaksi/{transaction}/resume', [TransactionController::class, 'resume'])->name('kasir.transaksi.resume');
    Route::delete('/transaksi/{transaction}/hold', [TransactionController::class, 'cancelHeld'])->name('kasir.transaksi.cancel-held');
    Route::get('/transactions', [TransactionController::class, 'history'])->name('kasir.transactions');
});

Route::get('/struk', [StrukController::class, 'index'])
    ->middleware(['auth', 'kasir'])
    ->name('struk');

require __DIR__.'/auth.php';
