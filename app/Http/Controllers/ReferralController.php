<?php

namespace App\Http\Controllers;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use Inertia\Inertia;

class ReferralController extends Controller
{
    public function index()
    {
        $parentCount = User::whereNull('upline_id') // No upline
            ->whereHas('directs') // Must have at least one direct downline
            ->count();

        return Inertia::render('Referrals/ReferralProgramme', [
            'parentCount' => $parentCount,
        ]);
    }

    public function getDownlineData(Request $request)
    {
        $upline_id = $request->upline_id;
        $parent_id = $request->parent_id;

        if ($request->filled('search')) {
            $search = '%' . $request->input('search') . '%';
            $parent = User::query()
                ->where(function ($query) use ($search) {
                    $query->where('id_number', 'LIKE', $search)
                        ->orWhere('username', 'LIKE', $search)
                        ->orWhere('email', 'LIKE', $search);
                })
                ->first();

            if (empty($parent)) {
                return response()->json([
                    'success' => false,
                    'message' => 'user not found'
                ]);
            }

            $parent_id = $parent->id;
            $upline_id = $parent->upline_id;
        }

        if ($parent_id) {
            $parents = User::select([
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

            $children = User::select([
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
                ->where('upline_id', $parent_id)
                ->get();
        } else {

            $parents = User::select([
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

        $upline = User::select([
            'users.*',
            DB::raw("(SELECT COUNT(*) FROM users AS u WHERE u.hierarchyList LIKE CONCAT('%-', users.id, '-%')) as total_downlines"),
            DB::raw("COALESCE((SELECT SUM(capital_fund) FROM broker_connections WHERE broker_connections.user_id = users.id AND broker_connections.deleted_at is null AND broker_connections.connection_type != 'withdrawal' AND broker_connections.status = 'success'), 0) as capital_fund_sum"),
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
            'children' => isset($children) ? $children->map(fn($child) => $this->formatUserData($child)) : [],
        ]);
    }

    private function formatUserData($user)
    {
        if (!$user) return null;

        if ($user->upline) {
            $upper_upline = $user->upline->upline;
        }

        return array_merge(
            $user->only(['id', 'username', 'id_number', 'upline_id', 'role']),
            [
                'upper_upline_id' => $upper_upline->id ?? null,
                'level' => $this->calculateLevel($user->hierarchyList),
                'total_directs' => count($user->directs) ?? 0,
                'total_downlines' => $user->total_downlines ?? 0,
                'capital_fund_sum' => $user->capital_fund_sum ?? 0,
                'total_downline_capital_fund' => $user->total_downline_capital_fund ?? 0
            ]
        );
    }

    private function calculateLevel($hierarchyList)
    {
        // If hierarchyList is null or empty, return level 0
        if (is_null($hierarchyList) || $hierarchyList === '') {
            return 0;
        }

        // Remove leading/trailing hyphens and split by '-'
        $levels = array_filter(explode('-', trim($hierarchyList, '-')));

        return count($levels);
    }

    public function getReferral()
    {
        $parents = User::select([
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

        return response()->json([
            'parents' => $parents->map(fn($parent) => $this->formatUserData($parent)),
        ]);
    }

    public function getReferralListingData(Request $request)
    {
        if ($request->has('lazyEvent')) {
            $data = json_decode($request->only(['lazyEvent'])['lazyEvent'], true);
            $user_id = $data['filters']['id'];
            $user = User::find($user_id);
            $childrenIds = $user ? $user->getChildrenIds() : [];

            $query = User::with([
                'upline',
                'active_connections',
                'rank',
                'country'
            ])
                ->whereIn('id', $childrenIds)
                ->withCount('directs') // This counts direct referrals
                ->withSum('active_connections', 'capital_fund')
                ->addSelect([
                    DB::raw("(SELECT COUNT(*) FROM users AS u WHERE u.hierarchyList LIKE CONCAT('%-', users.id, '-%')) as total_downlines"), // This counts total downlines
                    DB::raw(
                        "COALESCE((SELECT SUM(bc.capital_fund)
                                    FROM broker_connections AS bc
                                    JOIN users AS u ON bc.user_id = u.id
                                    WHERE u.hierarchyList LIKE CONCAT('%-', users.id, '-%')
                                    AND bc.deleted_at is null
                                    AND bc.connection_type != 'withdrawal'
                                    AND bc.status = 'success'), 0) as total_downline_capital_fund"
                    )
                ]);

            if ($data['filters']['global']['value']) {
                $keyword = $data['filters']['global']['value'];

                $query->where(function ($q) use ($keyword) {
                    $q->where('name', 'like', '%' . $keyword . '%')
                        ->orWhere('email', 'like', '%' . $keyword . '%')
                        ->orWhere('username', 'like', '%' . $keyword . '%')
                        ->orWhereHas('upline', function ($q) use ($keyword) {
                            $q->where('name', 'like', '%' . $keyword . '%')
                                ->orWhere('username', 'like', '%' . $keyword . '%')
                                ->orWhere('email', 'like', '%' . $keyword . '%');
                        });
                });
            }

            if (!empty($data['filters']['start_join_date']['value']) && !empty($data['filters']['end_join_date']['value'])) {
                $start_join_date = Carbon::parse($data['filters']['start_join_date']['value'])->addDay()->startOfDay();
                $end_join_date = Carbon::parse($data['filters']['end_join_date']['value'])->addDay()->endOfDay();

                $query->whereBetween('created_at', [$start_join_date, $end_join_date]);
            }

            if ($data['sortField'] && $data['sortOrder']) {
                $order = $data['sortOrder'] == 1 ? 'asc' : 'desc';
                $query->orderBy($data['sortField'], $order);
            } else {
                $query->orderByDesc('created_at');
            }

            $connections = $query->paginate($data['rows']);

            $connections->each(function ($user) {
                $user->level = $this->calculateLevel($user->hierarchyList);

                // Handle the profile photo URL check and fallback to null if not found
                $user->profile_photo = $user->getFirstMediaUrl('profile_photo') ?: null;

                // Handle the upline's profile photo, ensuring it's also null if no media found
                if ($user->upline) {
                    $user->upline_profile_photo = $user->upline->getFirstMediaUrl('profile_photo') ?: null;
                } else {
                    $user->upline_profile_photo = null;
                }
            });

            $connectionCount = (clone $query)->count();

            return response()->json([
                'success' => true,
                'data' => $connections,
                'connectionCount' => $connectionCount,
            ]);
        }
        return response()->json(['success' => false, 'data' => []]);
    }
}
