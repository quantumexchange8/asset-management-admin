<?php

use App\Http\Controllers\BrokerAccountController;
use App\Http\Controllers\BrokerController;
use App\Http\Controllers\ConnectionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReferralController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SelectOptionController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\TransactionController;
use App\Models\BrokerAccount;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use SebastianBergmann\CodeCoverage\Report\Html\Dashboard;

Route::get('/', function () {
    return Redirect::route('login');
});

Route::get('locale/{locale}', function ($locale) {
    App::setLocale($locale);
    Session::put("locale", $locale);

    return redirect()->back();
});

Route::middleware(['auth', 'role:super_admin|admin'])->group(function () {
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
    Route::prefix('dashboard')->middleware('role_and_permission:admin,access_dashboard')->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/get_total_deposit_by_days', [DashboardController::class, 'getTotalDepositByDays'])->name('dashboard.getTotalDepositByDays');
        Route::get('get_payouts', [DashboardController::class, 'getPayouts'])->name('dashboard.getPayouts');
        Route::get('/getPendingCounts', [DashboardController::class, 'getPendingCounts'])->name('dashboard.getPendingCounts');
    });

    Route::prefix('profile')->group(function () {
        Route::get('/', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::post('/update', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/delete', [ProfileController::class, 'destroy'])->name('profile.destroy');
    });

    /**
     * ==============================
     *            Member
     * ==============================
     */
    Route::prefix('member')->group(function () {
        Route::get('/get_member_list', [MemberController::class, 'getMemberList'])->name('member.getMemberList');
        Route::get('/get_member_data', [MemberController::class, 'getMemberData'])->name('member.getMemberData');
        Route::get('/access_portal/{user}', [MemberController::class, 'access_portal'])->name('member.access_portal');

        Route::post('/addNewMember', [MemberController::class, 'addNewMember'])->name('member.addNewMember');
        Route::put('/deleteMember', [MemberController::class, 'deleteMember'])->name('member.deleteMember');
        Route::put('/upgradeRank', [MemberController::class, 'upgradeRank'])->name('member.upgradeRank');
        Route::put('/changeUpline', [MemberController::class, 'changeUpline'])->name('member.changeUpline');

        //kyc status
        Route::get('/get_pending_kyc', [MemberController::class, 'getPendingKyc'])->name('member.getPendingKyc');
        Route::get('/get_pending_kyc_data', [MemberController::class, 'getPendingKycData'])->name('member.getPendingKycData');
        Route::post('/kycPendingApproval', [MemberController::class, 'kycPendingApproval'])->name('member.kycPendingApproval');

        Route::prefix('detail')->group(function () {
            Route::get('/{id_number}', [MemberController::class, 'memberDetail'])->name('member.detail.memberDetail');
            Route::put('/{id_number}/updateMemberProfile', [MemberController::class, 'updateMemberProfile'])->name('member.detail.updateMemberProfile');

            //Finance
            Route::get('/{id_number}/getWalletData', [MemberController::class, 'getWalletData'])->name('member.detail.getWalletData');
            Route::post('walletAdjustment', [MemberController::class, 'walletAdjustment'])->name('member.detail.walletAdjustment');
            Route::get('/{id_number}/financeDetail', [MemberController::class, 'financeDetail'])->name('member.detail.financeDetail');

            // Investment
            Route::get('/{id_number}/get_broker_accounts', [MemberController::class, 'getBrokerAccounts'])->name('member.detail.getBrokerAccounts');
        });
    });

    //referrals
    Route::prefix('referral')->group(function () {
        Route::get('/', [ReferralController::class, 'index'])->name('referral');
        Route::get('/getDownlineData', [ReferralController::class, 'getDownlineData'])->name('referral.getDownlineData');

        Route::get('/get_referral', [ReferralController::class, 'getReferral'])->name('referral.getReferral');
        Route::get('/get_referral_listing_data', [ReferralController::class, 'getReferralListingData'])->name('referral.getReferralListingData');
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
            Route::get('/get_broker_data', [BrokerController::class, 'getBrokerData'])->name('broker.getBrokerData');
            Route::post('/addNewBroker', [BrokerController::class, 'addNewBroker'])->name('broker.addNewBroker');
            Route::put('/updateBrokerStatus', [BrokerController::class, 'updateBrokerStatus'])->name('broker.updateBrokerStatus');

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
            //pending connections
            Route::get('/pending_connection', [ConnectionController::class, 'pending_connection'])->name('connection.pending_connection');
            Route::get('/pending_connection_data', [ConnectionController::class, 'pending_connection_data'])->name('connection.pending_connection_data');
            Route::put('/pendingConnectionApproval', [ConnectionController::class, 'pendingConnectionApproval'])->name('connection.pendingConnectionApproval');

            //connections
            Route::get('broker_connection', [ConnectionController::class, 'broker_connection'])->name('connection.broker_connection');
            Route::get('getConnections', [ConnectionController::class, 'getConnections'])->name('connection.getConnections');
            Route::get('/download_import_example', [ConnectionController::class, 'download_import_example'])->name('connection.download_import_example');

            Route::post('/importBrokerConnection', [ConnectionController::class, 'importBrokerConnection'])->name('connection.importBrokerConnection');
        });

        /**
         * ==============================
         *          Broker Account
         * ==============================
         */

         Route::prefix('broker_accounts')->group(function () {
            Route::get('/account_listing', [BrokerAccountController::class, 'account_listing'])->name('broker_accounts.account_listing');
            Route::get('/get_account_listing_data', [BrokerAccountController::class, 'getAccountListingData'])->name('broker_accounts.getAccountListingData');

            Route::get('/pending_account', [BrokerAccountController::class, 'pending_account'])->name('broker_accounts.pending_account');
            Route::get('/get_pending_account_data', [BrokerAccountController::class, 'getPendingAccountData'])->name('broker_accounts.getPendingAccountData');
            Route::post('/pendingAccountApproval', [BrokerAccountController::class, 'pendingAccountApproval'])->name('broker_accounts.pendingAccountApproval');
         });
    });

    //Transaction
    Route::prefix('transaction')->group(function () {

        //History
        Route::prefix('history')->group(function () {
            Route::get('/get_deposit_history', [TransactionController::class, 'getDepositHistory'])->name('transaction.history.getDepositHistory');
            Route::get('/get_deposit_history_data', [TransactionController::class, 'getDepositHistoryData'])->name('transaction.history.getDepositHistoryData');
            Route::post('/import-deposit-history', [TransactionController::class, 'importDepositHistory'])->name('transaction.history.importDepositHistory');
            Route::get('/get_highest_deposit', [TransactionController::class, 'getHighestDeposit'])->name('transaction.history.getHighestDeposit');

            Route::get('/get_withdrawal_history', [TransactionController::class, 'getWithdrawalHistory'])->name('transaction.history.getWithdrawalHistory');
            Route::get('/get_withdrawal_history_data', [TransactionController::class, 'getWithdrawalHistoryData'])->name('transaction.history.getWithdrawalHistoryData');
        });

        Route::prefix('pending')->group(function () {
            Route::get('/get_pending_deposit', [TransactionController::class, 'getPendingDeposit'])->name('transaction.pending.getPendingDeposit');
            Route::get('/get_deposit_recent_approval', [TransactionController::class, 'getDepositRecentApproval'])->name('transaction.pending.getDepositRecentApproval');
            Route::get('/get_pending_deposit_data', [TransactionController::class, 'getPendingDepositData'])->name('transaction.pending.getPendingDepositData');
            Route::put('/pendingDepositApproval', [TransactionController::class, 'pendingDepositApproval'])->name('transaction.pending.pendingDepositApproval');

            Route::get('/get_pending_withdrawal', [TransactionController::class, 'getPendingWithdrawal'])->name('transaction.pending.getPendingWithdrawal');
            Route::get('/get_withdrawal_recent_approval', [TransactionController::class, 'getWithdrawalRecentApproval'])->name('transaction.pending.getWithdrawalRecentApproval');
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
        //profit sharing
        Route::get('/profit_sharing', [ReportController::class, 'profit_sharing'])->name('report.profit_sharing')->middleware('permission:access_profit_sharing');
        Route::get('/getProfitSharingData', [ReportController::class, 'getProfitSharingData'])->name('report.getProfitSharingData')->middleware('permission:access_profit_sharing');

        // Standard Bonus
        Route::get('/ib_group_incentive', [ReportController::class, 'ib_group_incentive'])->name('report.ib_group_incentive')->middleware('permission:access_group_incentive');
        Route::get('/getStandardBonusData', [ReportController::class, 'getStandardBonusData'])->name('report.getStandardBonusData')->middleware('permission:access_group_incentive');

        // Rebate Bonus
        Route::get('/rebate_bonus', [ReportController::class, 'rebate_bonus'])->name('report.rebate_bonus')->middleware('permission:access_rebate_bonus');
        Route::get('/getRebateBonusData', [ReportController::class, 'getRebateBonusData'])->name('report.getRebateBonusData')->middleware('permission:access_rebate_bonus');

        // Trade History
        Route::get('/trade_history', [ReportController::class, 'trade_history'])->name('report.trade_history')->middleware('permission:access_trade_history');
        Route::get('/getTradeHistoryData', [ReportController::class, 'getTradeHistoryData'])->name('report.getTradeHistoryData')->middleware('permission:access_trade_history');
    });

    /**
     * ==============================
     *           Settings
     * ==============================
     */
    Route::prefix('settings')->middleware('role:super_admin')->group(function () {
        // Admin
        Route::get('/admin_listing', [SettingController::class, 'admin_listing'])->name('settings.admin_listing');
        Route::get('/getAdminListingData', [SettingController::class, 'getAdminListingData'])->name('settings.getAdminListingData');
        Route::post('addAdmin', [SettingController::class, 'addAdmin'])->name('settings.addAdmin');
        Route::put('/updateAdminInfo', [SettingController::class, 'updateAdminInfo'])->name('settings.updateAdminInfo');
        Route::put('/deleteAdmin', [SettingController::class, 'deleteAdmin'])->name('settings.deleteAdmin');

        //default profile
        Route::get('/deposit_profile', [SettingController::class, 'depositProfile'])->name('settings.depositProfile');
        Route::get('/get_deposit_profile_data', [SettingController::class, 'getDepositProfileData'])->name('settings.getDepositProfileData');
        Route::post('/addDepositProfile', [SettingController::class, 'addDepositProfile'])->name('settings.addDepositProfile');
        Route::put('/updateDepositProfileStatus', [SettingController::class, 'updateDepositProfileStatus'])->name('settings.updateDepositProfileStatus');
        Route::put('/updateDepositProfile', [SettingController::class, 'updateDepositProfile'])->name('settings.updateDepositProfile');
        Route::put('/deleteDepositProfile', [SettingController::class, 'deleteDepositProfile'])->name('settings.deleteDepositProfile');
    });
});

require __DIR__ . '/auth.php';
