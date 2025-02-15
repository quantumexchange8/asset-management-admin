<?php

namespace App\Http\Controllers;

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

                    // Include descendants of the matched users
                    foreach ($searchedUsers as $user) {
                        $userIds = array_merge($userIds, $this->getDescendants($allUsers, $user->id));
                    }

                    // Fetch all relevant users based on hierarchy
                    $users = User::whereIn('id', $userIds)
                        ->with([
                            'country:id,name,emoji',
                            'rank:id,rank_name',
                            'upline:id,name,email,upline_id',
                        ])
                        ->get();
                    Log::info('users:', ['users' => $users]);
                }
            } else {
                // If no global filter, fetch users with a hierarchy only
                $allUsers = User::all(); // Fetch all users to identify those with hierarchy
                $hierarchyUserIds = [];

                foreach ($allUsers as $user) {
                    if (!is_null($user->upline_id) || $this->hasDescendants($allUsers, $user->id)) {
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

        // Total downline count (direct + all descendants)
        $totalDownlineCount = $directDownlineCount;
        foreach ($children as $child) {
            $totalDownlineCount += $child['total_downlines_count'];
        }

        // Assign values
        $currentUser['children'] = $children;
        $currentUser['downlines_count'] = $directDownlineCount;
        $currentUser['total_downlines_count'] = $totalDownlineCount;

        return $currentUser;
    }

    private function getDescendants($allUsers, $userId)
    {
        $descendants = [];
        foreach ($allUsers as $user) {
            if ($user->upline_id === $userId) {
                $descendants[] = $user->id;
                $descendants = array_merge($descendants, $this->getDescendants($allUsers, $user->id));
            }
        }
        return $descendants;
    }

    private function hasDescendants($allUsers, $userId)
    {
        foreach ($allUsers as $user) {
            if ($user->upline_id === $userId) {
                return true;
            }
        }
        return false;
    }
}
