<?php

namespace App\Http\Controllers;

use App\Models\Broker;
use App\Models\Country;
use App\Models\Rank;
use App\Models\User;
use Illuminate\Http\Request;

class SelectOptionController extends Controller
{
    public function getUsers()
    {
        $users = User::select('id', 'name', 'username')->get();

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
}
