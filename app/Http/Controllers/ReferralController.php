<?php

namespace App\Http\Controllers;

use App\Models\BrokerConnection;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

use Inertia\Inertia;

class ReferralController extends Controller
{
    public function getReferralList()
    {
        return Inertia::render('Referrals/Listing/ReferralListing');
    }

    public function getReferralData(Request $request)
    {
        if ($request->has('lazyEvent')) {
            $data = json_decode($request->only(['lazyEvent'])['lazyEvent'], true); // Extract parameters in lazyEvent

            $userQuery = User::query()
                ->with([
                    'country:id,name,emoji',
                    'rank:id,rank_name',
                    'upline:id,name,email,upline_id',
                ]);

            $users = collect(); // Initialize an empty collection for filtered users

            // Apply global filter
            if (!empty($data['filters']['global']['value'])) {
                $keyword = $data['filters']['global']['value'];

                // Search for users by name or email
                $searchedUsers = $userQuery->where(function ($q) use ($keyword) {
                    $q->where('name', 'like', '%' . $keyword . '%')
                        ->orWhere('email', 'like', '%' . $keyword . '%');
                })->get();

                if ($searchedUsers->isNotEmpty()) {
                    $userIds = $searchedUsers->pluck('id')->toArray();
                    $allUsers = User::all(); // Fetch all users for hierarchy processing

                    // Include downlines of the matched users
                    foreach ($searchedUsers as $user) {
                        $userIds = array_merge($userIds, $this->getDownlines($allUsers, $user->id));
                    }

                    // Fetch all relevant users based on hierarchy
                    $users = User::whereIn('id', $userIds)
                        ->with([
                            'country:id,name,emoji',
                            'rank:id,rank_name',
                            'upline:id,name,email,upline_id',
                        ])
                        ->get();
                }
            } else {
                // If no global filter, fetch users with a hierarchy only
                $allUsers = User::select(['id', 'name', 'email', 'upline_id', 'hierarchyList'])->get(); // Fetch all users to identify those with hierarchy
                $hierarchyUserIds = [];

                foreach ($allUsers as $user) {
                    if (!is_null($user->upline_id) || $this->hasDownlines($allUsers, $user->id)) {
                        $hierarchyUserIds[] = $user->id;
                    }
                }

                // Fetch users with a valid hierarchy
                $users = User::whereIn('id', $hierarchyUserIds)
                    ->with([
                        'country:id,name,emoji',
                        'rank:id,rank_name',
                        'upline:id,name,email,upline_id',
                    ])
                    ->get();
            }

            // Build the hierarchy tree
            $referrals = $this->buildTree($users);

            $users->each(function ($user) {
                $user->profile_photo = $user->getFirstMediaUrl('profile_photo') ?: null; // Ensure it's a string or null

                if ($user->upline) {
                    $user->upline_profile_photo = $user->upline->getFirstMediaUrl('profile_photo') ?: null;
                } else {
                    $user->upline_profile_photo = null;
                }
            });

            Log::info('referrals:', ['referrals' => $referrals]);

            return response()->json([
                'data' => [
                    'referrals' => $referrals,
                ],
            ]);
        }

        return response()->json(['success' => false, 'referrals' => []]);
    }

    private function buildTree($users)
    {
        $tree = [];

        // Index users by ID for quick look-up
        $userMap = []; // userMap Index is based on user id, e.g: id=[2,4,6,8], usermap index would be the same
        foreach ($users as $user) {
            $userMap[$user['id']] = $user;
        }

        // Identify root users (those whose upline_id is not in the user list)
        $roots = [];
        foreach ($users as $user) {
            if (is_null($user['upline_id']) || !array_key_exists($user['upline_id'], $userMap)) {
                $roots[] = $user;
            }
        }

        // Recursively build tree starting from the roots
        foreach ($roots as $root) {
            $tree[] = $this->buildSubTree($users, $root);
        }

        return $tree;
    }

    private function buildSubTree($users, $currentUser)
    {
        $children = [];

        // Recursively find and build children
        foreach ($users as $user) {
            if ($user['upline_id'] === $currentUser['id']) {
                $child = $this->buildSubTree($users, $user);
                $children[] = $child;
            }
        }

        // Direct downline count (immediate children)
        $directDownlineCount = count($children);

        // Total downline count (direct + all downlines)
        $totalDownlineCount = $directDownlineCount;
        foreach ($children as $child) {
            $totalDownlineCount += $child['total_downlines_count'];
        }

        // Calculate capital funds
        $activeConnections = BrokerConnection::with('broker')
            ->where('status', 'active');

        $total_personal_fund = (clone $activeConnections)
            ->where('user_id', $currentUser['id'])
            ->sum('capital_fund');

        // Get all downline IDs including the current user's ID
        $allDownlineIds = $this->getDownlines($users, $currentUser['id']);
        $total_team_fund = (clone $activeConnections)
            ->whereIn('user_id', $allDownlineIds)
            ->sum('capital_fund');
        $allDownlineIds[] = $currentUser['id']; // Include the current user

        // Get all active connections for the user and their downlines
        $allConnections = (clone $activeConnections)
            ->whereIn('user_id', $allDownlineIds)
            ->get();

        // Organize data by broker
        $brokerData = [];

        foreach ($allConnections as $connection) {
            $brokerName = $connection->broker->name ?? 'Unknown';

            if (!isset($brokerData[$brokerName])) {
                $brokerData[$brokerName] = [
                    'broker_name' => $brokerName,
                    'personal_funds' => 0,
                    'team_funds' => 0
                ];
            }

            if ($connection->user_id === $currentUser['id']) {
                // If the connection belongs to the current user, add to personal funds
                $brokerData[$brokerName]['personal_funds'] += $connection->capital_fund;
            } else {
                // Otherwise, add to team funds
                $brokerData[$brokerName]['team_funds'] += $connection->capital_fund;
            }
        }

        // Convert to indexed array for easier usage in frontend
        $brokerDetails = array_values($brokerData);

        // Assign values
        $currentUser['children'] = $children;
        $currentUser['downlines_count'] = $directDownlineCount;
        $currentUser['total_downlines_count'] = $totalDownlineCount;
        $currentUser['broker_details'] = $brokerDetails;
        $currentUser['total_personal_fund'] = $total_personal_fund;
        $currentUser['total_team_fund'] = $total_team_fund;

        return $currentUser;
    }

    private function getDownlines($allUsers, $userId)
    {
        $downlines = [];
        foreach ($allUsers as $user) {
            if ($user->upline_id === $userId) {
                $downlines[] = $user->id;
                $downlines = array_merge($downlines, $this->getDownlines($allUsers, $user->id));
            }
        }
        return $downlines;
    }

    private function hasDownlines($allUsers, $userId)
    {
        foreach ($allUsers as $user) {
            if ($user->upline_id === $userId) {
                return true;
            }
        }
        return false;
    }
}
