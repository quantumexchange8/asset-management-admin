<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Exceptions\UnauthorizedException;

class RoleAndPermissionMiddleware
{
    public function handle($request, Closure $next, $role, ...$permissions)
    {
        // Get the authenticated user
        $user = Auth::user();

        // Check if the user is authenticated
        if (!$user) {
            throw UnauthorizedException::notLoggedIn();
        }

        // If the user has the specified role
        if ($user->hasRole($role)) {
            if (!empty($permissions)) {
                $hasPermission = false;

                // Check if the user has at least one of the specified permissions
                foreach ($permissions as $permission) {
                    if ($user->can($permission)) {
                        $hasPermission = true;
                        break;
                    }
                }

                // Handle the case where the user lacks access to the dashboard
                if (!$hasPermission && in_array('access_dashboard', $permissions)) {
                    // Redirect based on the user's available permissions
                    return $this->redirectToAvailableRoute();
                }

                if (!$hasPermission) {
                    throw UnauthorizedException::forPermissions($permissions);
                }
            }
        }

        // Proceed if the user role is matched or has sufficient permissions
        return $next($request);
    }

    // Method to determine and redirect to the appropriate route based on available permissions
    private function redirectToAvailableRoute(): RedirectResponse
    {
        $user = Auth::user();

        $routeMapping = [
            'access_member_listing' => 'member.getMemberList',
            'access_broker' => 'broker.getBrokerList',
            'access_connections' => 'connection.broker_connection',
            'access_pending_deposit' => 'transaction.pending.getPendingDeposit',
            'access_pending_withdrawal' => 'transaction.pending.getPendingWithdrawal',
            'access_deposit_history' => 'transaction.history.getDepositHistory',
            'access_withdrawal_history' => 'transaction.history.getWithdrawalHistory',
            'access_profit_sharing' => 'report.profit_sharing',
            'access_group_incentive' => 'report.ib_group_incentive',
            'access_rebate_bonus' => 'report.rebate_bonus',
            'access_trade_history' => 'report.trade_history',
            'access_admin_listing' => 'settings.admin_listing',
            'access_pending_kyc' => 'member.getPendingKyc',
            'access_member_referrals' => 'referral.getReferralList',
            'access_account_listing' => 'broker_accounts.account_listing',
            'access_pending_account' => 'broker_accounts.pending_account',
        ];

        // Find the first permission the user has and redirect to its route
        foreach ($routeMapping as $permission => $route) {
            if ($user->can($permission)) {
                return redirect()->route($route);
            }
        }

        // If no matching permission, throw an UnauthorizedException
        throw UnauthorizedException::forPermissions(array_keys($routeMapping));
    }
}
