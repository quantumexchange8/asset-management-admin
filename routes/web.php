<?php

use App\Http\Controllers\BrokerController;
use App\Http\Controllers\ConnectionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReferralController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SelectOptionController;
use App\Http\Controllers\TradingController;
use App\Http\Controllers\TransactionController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Inertia\Inertia;
use SebastianBergmann\CodeCoverage\Report\Html\Dashboard;

Route::get('/', function () {
    return Redirect::route('login');
});

Route::get('locale/{locale}', function ($locale) {
    App::setLocale($locale);
    Session::put("locale", $locale);

    return redirect()->back();
});

Route::middleware('auth')->group(function () {
    //select option
    Route::get('/get_users', [SelectOptionController::class, 'getUsers'])->name('getUsers');
    Route::get('/get_uplines', [SelectOptionController::class, 'getUplines'])->name('getUplines');
    Route::get('/get_countries', [SelectOptionController::class, 'getCountries'])->name('getCountries');
    Route::get('/get_ranks', [SelectOptionController::class, 'getRanks'])->name('getRanks');
    Route::get('/get_brokers', [SelectOptionController::class, 'getBrokers'])->name('getBrokers');

    /**
     * ==============================
     *          Dashboard
     * ==============================
     */
    Route::prefix('dashboard')->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/get_total_deposit_by_days', [DashboardController::class, 'getTotalDepositByDays'])->name('dashboard.getTotalDepositByDays');
    });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    //member
    Route::prefix('member')->group(function () {
        Route::get('/get_member_list', [MemberController::class, 'getMemberList'])->name('member.getMemberList');
        Route::get('/get_member_data', [MemberController::class, 'getMemberData'])->name('member.getMemberData');
        Route::get('/access_portal/{user}', [MemberController::class, 'access_portal'])->name('member.access_portal');

        Route::post('/addNewMember', [MemberController::class, 'addNewMember'])->name('member.addNewMember');
        Route::put('/upgradeRank', [MemberController::class, 'upgradeRank'])->name('member.upgradeRank');
        Route::put('/changeUpline', [MemberController::class, 'changeUpline'])->name('member.changeUpline');


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

    //referrals
    Route::prefix('referral')->group(function () {
        Route::get('/get_referral_list', [ReferralController::class, 'getReferralList'])->name('referral.getReferralList');
        Route::get('/get_referral_data', [ReferralController::class, 'getReferralData'])->name('referral.getReferralData');
    });

    /**
     * ==============================
     *          Marketplace
     * ==============================
     */
    Route::prefix('marketplace')->group(function () {
        /**
         * ==============================
         *            Broker
         * ==============================
         */
        Route::prefix('broker')->group(function () {
            Route::get('/get_broker_list', [BrokerController::class, 'getBrokerList'])->name('broker.getBrokerList');

            Route::put('/updateBrokerStatus', [BrokerController::class, 'updateBrokerStatus'])->name('broker.updateBrokerStatus');

            Route::get('/get_broker_list', [BrokerController::class, 'getBrokerList'])->name('broker.getBrokerList');
            Route::get('/get_broker_data', [BrokerController::class, 'getBrokerData'])->name('broker.getBrokerData');
            Route::post('/addNewBroker', [BrokerController::class, 'addNewBroker'])->name('broker.addNewBroker');

            Route::prefix('detail')->group(function () {
                Route::post('/updateBrokerInfo', [BrokerController::class, 'updateBrokerInfo'])->name('broker.detail.updateBrokerInfo');
            });
        });

        /**
         * ==============================
         *          Connections
         * ==============================
         */
        Route::prefix('connections')->group(function () {
            Route::get('pending_connection');
            Route::get('broker_connection', [ConnectionController::class, 'broker_connection'])->name('connection.broker_connection');
            Route::get('getConnections', [ConnectionController::class, 'getConnections'])->name('connection.getConnections');
            Route::get('/download_import_example', [ConnectionController::class, 'download_import_example'])->name('connection.download_import_example');

            Route::post('/importBrokerConnection', [ConnectionController::class, 'importBrokerConnection'])->name('connection.importBrokerConnection');
        });
    });

    //Transaction
    Route::prefix('transaction')->group(function () {

        //History
        Route::prefix('history')->group(function () {
            Route::get('/get_deposit_history', [TransactionController::class, 'getDepositHistory'])->name('transaction.history.getDepositHistory');
            Route::get('/get_deposit_history_data', [TransactionController::class, 'getDepositHistoryData'])->name('transaction.history.getDepositHistoryData');
            Route::post('/import-deposit-history', [TransactionController::class, 'importDepositHistory'])->name('transaction.history.importDepositHistory');

            Route::get('/get_withdrawal_history', [TransactionController::class, 'getWithdrawalHistory'])->name('transaction.history.getWithdrawalHistory');
            Route::get('/get_withdrawal_history_data', [TransactionController::class, 'getWithdrawalHistoryData'])->name('transaction.history.getWithdrawalHistoryData');

        });

        Route::prefix('pending')->group(function () {
            Route::get('/get_pending_deposit', [TransactionController::class, 'getPendingDeposit'])->name('transaction.pending.getPendingDeposit');
            Route::get('/get_pending_deposit_data', [TransactionController::class, 'getPendingDepositData'])->name('transaction.pending.getPendingDepositData');
            Route::put('/pendingDepositApproval', [TransactionController::class, 'pendingDepositApproval'])->name('transaction.pending.pendingDepositApproval');
            Route::get('/get_pending_withdrawal', [TransactionController::class, 'getPendingWithdrawal'])->name('transaction.pending.getPendingWithdrawal');
            Route::get('/get_pending_withdrawal_data', [TransactionController::class, 'getPendingWithdrawalData'])->name('transaction.pending.getPendingWithdrawalData');
            Route::put('/pendingWithdrawalApproval', [TransactionController::class, 'pendingWithdrawalApproval'])->name('transaction.pending.pendingWithdrawalApproval');
        });
    });

    /**
     * ==============================
     *            Report
     * ==============================
     */
    Route::prefix('report')->group(function () {
        // Standard Bonus
        Route::get('/standard_bonus', [ReportController::class, 'standard_bonus'])->name('report.standard_bonus');
        Route::get('/getStandardBonusData', [ReportController::class, 'getStandardBonusData'])->name('report.getStandardBonusData');

        // Rebate Bonus
        Route::get('/rebate_bonus', [ReportController::class, 'rebate_bonus'])->name('report.rebate_bonus');
        Route::get('/getRebateBonusData', [ReportController::class, 'getRebateBonusData'])->name('report.getRebateBonusData');
    });
});

require __DIR__ . '/auth.php';
