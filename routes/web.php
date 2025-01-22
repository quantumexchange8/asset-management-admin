<?php

use App\Http\Controllers\MemberController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SelectOptionController;
use App\Http\Controllers\TradingController;
use App\Http\Controllers\TransactionController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Redirect::route('login');
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    //select option
    Route::get('/get_users', [SelectOptionController::class, 'getUsers'])->name('getUsers');
    Route::get('/get_countries', [SelectOptionController::class, 'getCountries'])->name('getCountries');
    Route::get('/get_ranks', [SelectOptionController::class, 'getRanks'])->name('getRanks');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    //Customer
    Route::prefix('member')->group(function () {
        Route::get('/get_member_list', [MemberController::class, 'getMemberList'])->name('member.getMemberList');
        Route::get('/get_member_overview', [MemberController::class, 'getMemberOverview'])->name('member.getMemberOverview');
        Route::get('/get_member_data', [MemberController::class, 'getMemberData'])->name('member.getMemberData');
        Route::post('/addNewMember', [MemberController::class, 'addNewMember'])->name('member.addNewMember');
        Route::put('/upgradeRank', [MemberController::class, 'upgradeRank'])->name('member.upgradeRank');

        //kyc status
        Route::get('/get_pending_kyc', [MemberController::class, 'getPendingKyc'])->name('member.getPendingKyc');
        Route::get('/get_pending_kyc_data', [MemberController::class, 'getPendingKycData'])->name('member.getPendingKycData');
        Route::put('/kycPendingApproval', [MemberController::class, 'kycPendingApproval'])->name('member.kycPendingApproval');

        Route::prefix('detail')->group(function () {
            Route::get('/{id_number}', [MemberController::class, 'memberDetail'])->name('member.detail.memberDetail');
            Route::put('/{id_number}/updateMemberProfile', [MemberController::class, 'updateMemberProfile'])->name('member.detail.updateMemberProfile');

            //Finance
            Route::get('/{id_number}/getWalletData', [MemberController::class, 'getWalletData'])->name('member.detail.getWalletData');
            Route::post('walletAdjustment', [MemberController::class, 'walletAdjustment'])->name('member.detail.walletAdjustment');
        });
    });

    //Transaction
    Route::prefix('transaction')->group(function () {

        //History
        Route::prefix('history')->group(function () {
            Route::get('/get_deposit_history', [TransactionController::class, 'getDepositHistory'])->name('transaction.history.getDepositHistory');
            Route::get('/get_deposit_history_data', [TransactionController::class, 'getDepositHistoryData'])->name('transaction.history.getDepositHistoryData');
            Route::post('/import-deposit-history', [TransactionController::class, 'importDepositHistory'])->name('transaction.history.importDepositHistory');
            Route::get('/download-template', function () {
                $filePath = public_path('/VoltAsia_Deposit_History_Import_Template.xlsx');
                return response()->download($filePath);
            });

            Route::get('/get_withdrawal_history', [TransactionController::class, 'getWithdrawalHistory'])->name('transaction.history.getWithdrawalHistory');
            Route::get('/get_withdrawal_history_data', [TransactionController::class, 'getWithdrawalHistoryData'])->name('transaction.history.getWithdrawalHistoryData');
          
        });
    });

    Route::prefix('report')->group(function () {
        Route::get('/get_commissions_list', [TradingController::class, 'getCommissionsList'])->name('report.getCommissionsList');
        Route::post('/import-commissions', [TradingController::class, 'importCommissions'])->name('report.importCommissions');
        Route::get('/get_commission_data', [TradingController::class, 'getCommissionData'])->name('report.getCommissionData');

        Route::get('/download-template', function () {
            $filePath = public_path('/VoltAsia_Commission_Import_Template.xlsx');
            return response()->download($filePath);
        });
    });
});

require __DIR__ . '/auth.php';
