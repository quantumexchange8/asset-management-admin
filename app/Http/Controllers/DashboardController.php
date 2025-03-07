<?php

namespace App\Http\Controllers;

use App\Models\BrokerConnection;
use App\Models\Transaction;
use App\Services\SidebarService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
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

    public function getTotalDepositByDays(Request $request)
    {
        $query = BrokerConnection::query()
            ->whereIn('status', ['active', 'removed']);

        // Get the selected month/year (default: current month/year)
        $month = (int) $request->input('month', date('m'));
        $year = (int) $request->input('year', date('Y'));
        $days = (int) $request->input('days', cal_days_in_month(CAL_GREGORIAN, $month, $year));

        if ($days <= 14) {
            // Last X days from today
            $endDate = Carbon::now()->endOfDay();
            $startDate = Carbon::now()->subDays($days - 1)->startOfDay();
        } else {
            $startDate = Carbon::create($year, $month, 1)->startOfDay();
            $endDate = Carbon::create($year, $month, date('d'))->endOfDay();
        }

        $filteredQuery = $query->whereBetween('joined_at', [$startDate, $endDate]);

        $chartResults = $filteredQuery->select(
            DB::raw('DATE(joined_at) as date'),
            'status',
            DB::raw('SUM(capital_fund) as amount')
        )->groupBy('date', 'status')->get();

        $connectionsStatus = $chartResults->pluck('status')->unique();

        // Generate Labels:
        if ($days === 7) {
            // Latest 7 weekdays (e.g., Sun, Mon, Tue)
            $labels = collect(range(0, 6))->map(fn($i) => Carbon::now()->subDays(6 - $i)->format('D'))->toArray();
        } elseif ($days === 14) {
            $labels = collect(range(0, 13))->map(fn($i) => Carbon::now()->subDays(13 - $i)->format('j'))->toArray();
        } else {
            $labels = collect(range(1, date('d')))->toArray();
        }

        $chartData = [
            'labels' => $labels,
            'datasets' => [],
        ];

        // Color mapping
        $colors = [
            'active' => ['border' => '#12B76A', 'background' => 'rgba(18, 183, 106, 0.3)'],
            'removed' => ['border' => '#FF2D55', 'background' => 'rgba(255, 45, 85, 0.3)']
        ];

        // Loop through each unique status and create a dataset
        foreach ($connectionsStatus as $status) {
            $data = $chartResults->where('status', $status);

            $dataset = [
                'label' => $status == 'active' ? trans('public.deposit') : trans('public.withdrawal'),
                'data' => array_map(function ($label) use ($month, $year, $chartData, $data, $days) {
                    $date = ($days <= 14)
                        ? Carbon::now()->subDays($days - 1 - array_search($label, $chartData['labels']))->toDateString()
                        : Carbon::create($year, $month, $label)->toDateString();

                    return $data->firstWhere('date', $date)->amount ?? 0;
                }, $chartData['labels']),
                'borderColor' => $colors[$status]['border'],
                'backgroundColor' => $colors[$status]['background'],
                'borderWidth' => 2,
                'pointStyle' => false,
                'fill' => true,
                'tension' => 0.4,
            ];

            $chartData['datasets'][] = $dataset;
        }

        return response()->json([
            'chartData' => $chartData,
        ]);
    }

    public function getPendingCounts()
    {
        return response()->json([
            'pendingKycCount' => (new SidebarService())->getPendingKycCount(),
            'pendingDepositCounts' => (new SidebarService())->getPendingDepositCount(),
            'getPendingAccountCount' => (new SidebarService())->getPendingAccountCount(),
            'pendingWithdrawalCounts' => (new SidebarService())->pendingWithdrawalCounts(),
        ]);
    }


}
