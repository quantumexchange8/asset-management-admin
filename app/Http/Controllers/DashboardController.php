<?php

namespace App\Http\Controllers;

use App\Models\BrokerConnection;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $total_active_fund = BrokerConnection::where([
            'status' => 'active'
        ])->sum('capital_fund');

        return Inertia::render('Dashboard/Dashboard', [
            'totalActiveFund' => $total_active_fund
        ]);
    }
}
