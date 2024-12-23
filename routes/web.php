<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TransactionController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    //Transaction
    Route::prefix('transaction')->group(function(){

        //History
        Route::prefix('history')->group(function(){
            Route::get('/get_deposit_history', [TransactionController::class, 'getDepositHistory'])->name('transaction.history.getDepositHistory');
            Route::post('/import-deposit-history', [TransactionController::class, 'importDepositHistory'])->name('transaction.history.importDepositHistory');
            Route::get('/export_deposit_history', [TransactionController::class, 'exportDepositHistory'])->name('transaction.history.exportDepositHistory');
        });
    });
});

require __DIR__.'/auth.php';
