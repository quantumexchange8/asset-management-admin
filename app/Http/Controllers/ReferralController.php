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

                    // Include upline hierarchy for the searched users
                    foreach ($searchedUsers as $user) {
                        $userIds = array_merge($userIds, $this->getUplineHierarchy($allUsers, $user->upline_id));
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

            return response()->json([
                'data' => [
                    'referrals' => $referrals,
                ],
            ]);
        }

        return response()->json(['success' => false, 'referrals' => []]);
    }

    private function buildTree($users, $parentId = null)
    {
        $tree = [];

        foreach ($users as $user) {
            // Check if the current user belongs to the given parent (upline)
            if ($user['upline_id'] === $parentId) {
                // Build tree recursively for the children of the current user
                $children = $this->buildTree($users, $user['id']);

                // If the user has children, add them under 'children' and set downline count
                if (!empty($children)) {
                    $user['children'] = $children;
                    $user['downlines_count'] = count($children);  // Direct downlines only
                } else {
                    // If no children, set downline count to 0
                    $user['downlines_count'] = 0;
                }

                // Add this user to the tree
                $tree[] = $user;
            }
        }
        return $tree;
    }

    private function getUplineHierarchy($allUsers, $uplineId)
    {
        $uplineHierarchy = [];
        while (!is_null($uplineId)) {
            $uplineUser = $allUsers->firstWhere('id', $uplineId);
            if ($uplineUser) {
                $uplineHierarchy[] = $uplineUser->id;
                $uplineId = $uplineUser->upline_id;
            } else {
                break;
            }
        }
        return $uplineHierarchy;
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

    public function referralDetail($id_number)
    {
        $referral = User::where('id_number', $id_number)
            ->with([
                'country:id,name,emoji',
                'upline:id,name,email,upline_id',
                'rank:id,rank_name',

            ])->first();

        $refereeCount = User::where('upline_id', $referral->id)->count();

        // Count all descendants (recursively)
        $totalDownline = $this->countDownline($referral->id);

        return Inertia::render('Referrals/Listing/Detail/ReferralDetail', [
            'referral' => $referral,
            'refereeCount' => $refereeCount,
            'totalDownline' => $totalDownline,
        ]);
    }

    private function countDownline($uplineId)
    {
        // Retrieve all direct descendants
        $directDownline = User::where('upline_id', $uplineId)->pluck('id');

        // Base case: if no more descendants, return 0
        if ($directDownline->isEmpty()) {
            return 0;
        }

        // Count direct descendants
        $count = $directDownline->count();

        // Recursively count the descendants of each direct descendant
        foreach ($directDownline as $downlineId) {
            $count += $this->countDownline($downlineId);
        }

        return $count;
    }
}
