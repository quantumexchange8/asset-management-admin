<?php

namespace App\Enums;

enum Permission: string
{
    // Dashboard
    case ACCESS_DASHBOARD = 'access_dashboard';

    // Member Listing
    case ACCESS_MEMBER_LISTING = 'access_member_listing';
    case ADD_MEMBER = 'add_member';
    case EDIT_MEMBER = 'edit_member';
    case DELETE_MEMBER = 'delete_member';

    // Pending KYC
    case ACCESS_PENDING_KYC = 'access_pending_kyc';
    case EDIT_PENDING_KYC = 'edit_pending_kyc';

    // Member referrals
    case ACCESS_MEMBER_REFERRALS = 'access_member_referrals';

    // Broker
    case ACCESS_BROKER = 'access_broker';
    case ADD_BROKER = 'add_broker';
    case EDIT_BROKER = 'edit_broker';
    case DELETE_BROKER = 'delete_broker';

    // Broker Accounts
    case ACCESS_ACCOUNT_LISTING = 'access_account_listing';
    case ACCESS_PENDING_ACCOUNT = 'access_pending_account';
    case EDIT_PENDING_ACCOUNT = 'edit_pending_account';

    // Connections
    case ACCESS_CONNECTIONS = 'access_connections';
    case IMPORT_CONNECTIONS = 'import_connections';

    // Pending Deposit
    case ACCESS_PENDING_DEPOSIT = 'access_pending_deposit';
    case EDIT_PENDING_DEPOSIT = 'edit_pending_deposit';

    // Pending Withdrawal
    case ACCESS_PENDING_WITHDRAWAL = 'access_pending_withdrawal';
    case EDIT_PENDING_WITHDRAWAL = 'edit_pending_withdrawal';

    // Deposit History
    case ACCESS_DEPOSIT_HISTORY = 'access_deposit_history';

    // Withdrawal History
    case ACCESS_WITHDRAWAL_HISTORY = 'access_withdrawal_history';

    // Profit Sharing
    case ACCESS_PROFIT_SHARING = 'access_profit_sharing';

    // Profit Sharing
    case ACCESS_GROUP_INCENTIVE = 'access_group_incentive';

    // Profit Sharing
    case ACCESS_REBATE_BONUS = 'access_rebate_bonus';

    // Trade History
    case ACCESS_TRADE_HISTORY = 'access_trade_history';

    // Admin Listing
    case ACCESS_ADMIN_LISTING = 'access_admin_listing';
}
