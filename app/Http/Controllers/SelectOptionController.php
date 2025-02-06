<?php

namespace App\Http\Controllers;

use App\Models\Broker;
use App\Models\Country;
use App\Models\Rank;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SelectOptionController extends Controller
{
    public function getUsers()
    {
        $users = User::select('id', 'name', 'username', 'upline_id')->get();

        return response()->json([
            'users' => $users,
        ]);
    }

    public function getCountries()
    {
        $countries = Country::select('id', 'name', 'phone_code', 'iso2', 'emoji', 'translations', 'currency', 'currency_symbol')
            ->get();

        return response()->json([
            'countries' => $countries,
        ]);
    }

    public function getRanks()
    {
        $ranks = Rank::select('id', 'rank_name', 'rank_position')->get();

        return response()->json([
            'ranks' => $ranks,
        ]);
    }

    public function getBrokers()
    {
        $brokers = Broker::select('id', 'name')->get();

        return response()->json([
            'brokers' => $brokers,
        ]);
    }

    public function getUplines()
    {
        // Get all users
        $users = User::all();

        // Store IDs that are in any hierarchy list
        $userIdsInHierarchyList = [];

        // Loop through all users to check hierarchy lists
        foreach ($users as $user) {
            // If user has a hierarchy list, split by the delimiter `-`
            if ($user->hierarchyList) {
                // Split the hierarchy list into individual IDs, remove empty values
                $hierarchyIds = array_filter(explode('-', $user->hierarchyList));

                // Merge into the userIdsInHierarchyList array
                $userIdsInHierarchyList = array_merge($userIdsInHierarchyList, $hierarchyIds);
            }
        }

        // Get unique IDs that are in any hierarchy list
        $userIdsInHierarchyList = array_unique($userIdsInHierarchyList);

        // Now, fetch users whose ID is in the hierarchy list
        $result = User::whereIn('id', $userIdsInHierarchyList)->get();

        return response()->json([
            'uplines' => $result,
        ]);
    }
}
