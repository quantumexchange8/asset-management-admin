<?php

namespace App\Http\Controllers;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use Inertia\Inertia;

class ReferralController extends Controller
{
    public function getReferralList()
    {
        return Inertia::render('Referrals/Listing/ReferralListing');
    }

    public function getDownlineData(Request $request)
    {
        $upline_id = $request->upline_id;
        $parent_id = $request->parent_id;
        $selectedChildren = json_decode($request->selected_children, true) ?? [];
        $parents = collect();
        $children = collect();

        

        if ($parent_id) {
            // If parent_id is provided, get its direct downlines
            $parents = User::with([
                'directs' => function ($query) {
                    $query->select([
                        'users.*',
                        DB::raw("(SELECT COUNT(*) FROM users AS u WHERE u.hierarchyList LIKE CONCAT('%-', users.id, '-%')) as total_downlines"),
                        DB::raw("COALESCE((SELECT SUM(capital_fund) FROM broker_connections WHERE broker_connections.user_id = users.id AND broker_connections.deleted_at is null AND broker_connections.connection_type != 'withdrawal' AND broker_connections.status = 'success'), 0) as capital_fund_sum"),
                        DB::raw(
                            "COALESCE((SELECT SUM(bc.capital_fund)
                            FROM broker_connections AS bc
                            JOIN users AS u ON bc.user_id = u.id
                            WHERE u.hierarchyList LIKE CONCAT('%-', users.id, '-%')
                            AND u.id != users.id
                            AND bc.deleted_at is null
                            AND bc.connection_type != 'withdrawal'
                            AND bc.status = 'success'), 0) as total_downline_capital_fund"
                        )
                    ]);
                }
            ])
                ->select([
                    'users.*',
                    DB::raw("(SELECT COUNT(*) FROM users AS u WHERE u.hierarchyList LIKE CONCAT('%-', users.id, '-%')) as total_downlines"),
                    DB::raw("COALESCE((SELECT SUM(capital_fund) FROM broker_connections WHERE broker_connections.user_id = users.id AND broker_connections.deleted_at is null AND broker_connections.connection_type != 'withdrawal' AND broker_connections.status = 'success'), 0) as capital_fund_sum"),
                    DB::raw(
                        "COALESCE((SELECT SUM(bc.capital_fund)
                        FROM broker_connections AS bc
                        JOIN users AS u ON bc.user_id = u.id
                        WHERE u.hierarchyList LIKE CONCAT('%-', users.id, '-%')
                        AND bc.deleted_at is null
                        AND bc.connection_type != 'withdrawal'
                        AND bc.status = 'success'), 0) as total_downline_capital_fund"
                    )
                ])
                ->where('id', $parent_id)
                ->get();
        } else {
            // default display parents only
            if (!$upline_id && !$selectedChildren) {

                $parents = User::with([
                    'directs' => function ($query) {
                        $query->select([
                            'users.*',
                            DB::raw("(SELECT COUNT(*) FROM users AS u WHERE u.hierarchyList LIKE CONCAT('%-', users.id, '-%')) as total_downlines"),
                            DB::raw("COALESCE((SELECT SUM(capital_fund) FROM broker_connections WHERE broker_connections.user_id = users.id AND broker_connections.deleted_at is null AND broker_connections.connection_type != 'withdrawal' AND broker_connections.status = 'success'), 0) as capital_fund_sum"),
                            DB::raw(
                                "COALESCE((SELECT SUM(bc.capital_fund)
                                FROM broker_connections AS bc
                                JOIN users AS u ON bc.user_id = u.id
                                WHERE u.hierarchyList LIKE CONCAT('%-', users.id, '-%')
                                AND u.id != users.id
                                AND bc.deleted_at is null
                                AND bc.connection_type != 'withdrawal'
                                AND bc.status = 'success'), 0) as total_downline_capital_fund"
                            )
                        ]);
                    }
                ])
                    ->select([
                        'users.*',
                        DB::raw("(SELECT COUNT(*) FROM users AS u WHERE u.hierarchyList LIKE CONCAT('%-', users.id, '-%')) as total_downlines"),
                        DB::raw("COALESCE((SELECT SUM(capital_fund) FROM broker_connections WHERE broker_connections.user_id = users.id AND broker_connections.deleted_at is null AND broker_connections.connection_type != 'withdrawal' AND broker_connections.status = 'success'), 0) as capital_fund_sum"),
                        DB::raw(
                            "COALESCE((SELECT SUM(bc.capital_fund)
                            FROM broker_connections AS bc
                            JOIN users AS u ON bc.user_id = u.id
                            WHERE u.hierarchyList LIKE CONCAT('%-', users.id, '-%')
                            AND bc.deleted_at is null
                            AND bc.connection_type != 'withdrawal'
                            AND bc.status = 'success'), 0) as total_downline_capital_fund"
                        )
                    ])
                    ->whereNull('upline_id') // No upline
                    ->whereHas('directs') // Must have at least one direct downline
                    ->get();
            }
        }

        // If `selected_children` is passed, fetch their details
        if (!empty($selectedChildren)) {
            $children = User::whereIn('id', $selectedChildren)
                ->with([
                    'directs' => function ($query) {
                        $query->select([
                            'id',
                            'username',
                            'email',
                            'upline_id', // Select necessary fields
                            DB::raw("(SELECT COUNT(*) FROM users AS u WHERE u.hierarchyList LIKE CONCAT('%-', users.id, '-%')) as total_downlines"),
                            DB::raw("COALESCE((SELECT SUM(capital_fund) FROM broker_connections WHERE broker_connections.user_id = users.id AND broker_connections.deleted_at is null AND broker_connections.connection_type != 'withdrawal' AND broker_connections.status = 'success'), 0) as capital_fund_sum"),
                            DB::raw("
                                COALESCE((SELECT SUM(bc.capital_fund)
                                FROM broker_connections AS bc
                                JOIN users AS u ON bc.user_id = u.id
                                WHERE u.hierarchyList LIKE CONCAT('%-', users.id, '-%')
                                AND u.id != users.id
                                AND bc.deleted_at is null
                                AND bc.connection_type != 'withdrawal'
                                AND bc.status = 'success'), 0) as total_downline_capital_fund
                            ")
                        ]);
                    }
                ])
                ->select([
                    'id',
                    'username',
                    'email',
                    'upline_id', // Only necessary columns
                    DB::raw("(SELECT COUNT(*) FROM users AS u WHERE u.hierarchyList LIKE CONCAT('%-', users.id, '-%')) as total_downlines"),
                    DB::raw("COALESCE((SELECT SUM(capital_fund) FROM broker_connections WHERE broker_connections.user_id = users.id AND broker_connections.deleted_at is null AND broker_connections.connection_type != 'withdrawal' AND broker_connections.status = 'success'), 0) as capital_fund_sum"),
                    DB::raw("
                        COALESCE((SELECT SUM(bc.capital_fund)
                        FROM broker_connections AS bc
                        JOIN users AS u ON bc.user_id = u.id
                        WHERE u.hierarchyList LIKE CONCAT('%-', users.id, '-%')
                        AND u.id != users.id
                        AND bc.deleted_at is null
                        AND bc.connection_type != 'withdrawal'
                        AND bc.status = 'success'), 0) as total_downline_capital_fund
                    ")
                ])
                ->get();
        }

        // Fetch upline (if provided)
        $upline = User::select([
            'users.*',

            // Count total downlines
            DB::raw("(SELECT COUNT(*) FROM users AS u WHERE u.hierarchyList LIKE CONCAT('%-', users.id, '-%')) as total_downlines"),

            // Sum capital_fund from broker_connections where status is active
            DB::raw("COALESCE((SELECT SUM(capital_fund) FROM broker_connections WHERE broker_connections.user_id = users.id AND broker_connections.deleted_at is null AND broker_connections.connection_type != 'withdrawal' AND broker_connections.status = 'success'), 0) as capital_fund_sum"),

            // Sum total capital_fund of all downlines
            DB::raw("COALESCE((SELECT SUM(bc.capital_fund)
                FROM broker_connections AS bc
                JOIN users AS u ON bc.user_id = u.id
                WHERE u.hierarchyList LIKE CONCAT('%-', users.id, '-%')
                AND u.id != users.id
                AND bc.deleted_at is null
                AND bc.connection_type != 'withdrawal'
                AND bc.status = 'success'), 0) as total_downline_capital_fund")
        ])
            ->where('id', $upline_id)
            ->first();

        return response()->json([
            'success' => true,
            'upline' => $upline ? $this->formatUserData($upline) : null,
            'parents' => $parents->map(fn($parent) => $this->formatUserData($parent)),
            'children' => $children,
        ]);
    }

    private function formatUserData($user)
    {
        if (!$user) return null;

        // Ensure `$upper_upline` is defined properly
        $upper_upline = $user->upline ? $user->upline->upline : null;

        return array_merge(
            $user->only(['id', 'username', 'id_number', 'upline_id', 'role']),
            [
                'upper_upline_id' => $upper_upline ? $upper_upline->id : null,
                // 'level' => $this->calculateLevel($user->hierarchyList, $user->id),
                'total_directs' => count($user->directs ?? []), // Ensure it's countable
                'total_downlines' => $user->total_downlines ?? 0,
                'capital_fund_sum' => $user->capital_fund_sum ?? 0,
                'total_downline_capital_fund' => $user->total_downline_capital_fund ?? 0,
                'children' => $user->directs ? $user->directs->map(fn($child) => $this->formatUserData($child)) : []
            ]
        );
    }

    // private function calculateLevel($hierarchyList, $userId)
    // {
    //     if (empty($hierarchyList)) {
    //         return 0;
    //     }

    //     // Ensure the user ID exists in the hierarchy list before splitting
    //     if (!str_contains($hierarchyList, '-' . $userId . '-')) {
    //         return 1; // If the user is at the root level, return 1
    //     }

    //     $split = explode('-' . $userId . '-', $hierarchyList);

    //     // Ensure there is a second part before accessing index 1
    //     if (!isset($split[1])) {
    //         return 1; // If the user is at the root level, return 1
    //     }

    //     return substr_count($split[1], '-') + 1;
    // }
}
