<?php

namespace App\Http\Controllers;

use App\Models\AccumulatedAmountLogs;
use App\Models\BonusHistory;
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
            'status' => 'success',
        ])->sum('capital_fund');

        return Inertia::render('Dashboard/Dashboard', [
            'totalActiveFund' => $total_active_fund
        ]);
    }

    public function getTotalDepositByDays(Request $request)
    {

        $query = BrokerConnection::query()
            ->where('status', 'success');

        $month = (int) $request->input('month', date('m'));
        $year = (int) $request->input('year', date('Y'));
        $days = (int) $request->input('days', cal_days_in_month(CAL_GREGORIAN, $month, $year));

        $currentMonth = (int) date('m');
        $currentYear = (int) date('Y');
        $currentDay = (int) date('d');

        if ($year === $currentYear && $month === $currentMonth) {
            if ($days <= 14) {
                $endDate = Carbon::now()->endOfDay();
                $startDate = Carbon::now()->subDays($days - 1)->startOfDay();
            } else {
                $startDate = Carbon::create($year, $month, 1)->startOfDay();
                $endDate = Carbon::create($year, $month, $currentDay)->endOfDay();
            }
        } else {
            $startDate = Carbon::create($year, $month, 1)->startOfDay();
            $endDate = Carbon::create($year, $month, min($days, cal_days_in_month(CAL_GREGORIAN, $month, $year)))->endOfDay();
        }

        // Fetch deposits (joined_at only)
        $depositData = (clone $query)
            ->where('connection_type', 'deposit')
            ->whereNotNull('joined_at') // Ensure joined_at exists
            ->whereBetween('joined_at', [$startDate, $endDate])
            ->select(
                DB::raw('DATE(joined_at) as date'),
                DB::raw('SUM(capital_fund) as amount'),
                DB::raw("'deposit' as connection_type")
            )
            ->groupBy('date');

        // Fetch withdrawals (removed_at only)
        $withdrawalData = (clone $query)
            ->where('connection_type', 'withdrawal')
            ->whereNotNull('removed_at') // Ensure removed_at exists
            ->whereBetween('removed_at', [$startDate, $endDate])
            ->select(
                DB::raw('DATE(removed_at) as date'),
                DB::raw('SUM(capital_fund) as amount'),
                DB::raw("'withdrawal' as connection_type")
            )
            ->groupBy('date');

        // Combine both datasets
        $chartResults = $depositData->union($withdrawalData)->get();

        // Generate labels
        if ($year === $currentYear && $month === $currentMonth) {
            if ($days === 7) {
                $labels = collect(range(0, 6))->map(fn($i) => Carbon::now()->subDays(6 - $i)->format('D'))->toArray();
            } elseif ($days === 14) {
                $labels = collect(range(0, 13))->map(fn($i) => Carbon::now()->subDays(13 - $i)->format('j'))->toArray();
            } else {
                $labels = collect(range($startDate->day, $currentDay))->toArray();
            }
        } else {
            $labels = collect(range(1, min($days, cal_days_in_month(CAL_GREGORIAN, $month, $year))))->toArray();
        }

        $chartData = [
            'labels' => $labels,
            'datasets' => [],
        ];

        // Color mapping
        $colors = [
            'deposit' => ['border' => '#12B76A', 'background' => 'rgba(18, 183, 106, 0.3)'],
            'withdrawal' => ['border' => '#FF2D55', 'background' => 'rgba(255, 45, 85, 0.3)']
        ];

        foreach (['deposit', 'withdrawal'] as $connectionType) {
            $data = $chartResults->where('connection_type', $connectionType);

            $dataset = [
                'label' => trans("public.$connectionType"),
                'data' => array_map(function ($label) use ($month, $year, $chartData, $data, $days) {
                    $date = ($days <= 14)
                        ? Carbon::now()->subDays($days - 1 - array_search($label, $chartData['labels']))->toDateString()
                        : Carbon::create($year, $month, $label)->toDateString();

                    return $data->firstWhere('date', $date)->amount ?? 0;
                }, $chartData['labels']),
                'borderColor' => $colors[$connectionType]['border'],
                'backgroundColor' => $colors[$connectionType]['background'],
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

    public function getPayouts(Request $request)
    {
        $year = (int) $request->input('year', date('Y'));

        // Apply all filters before executing the query
        $query = AccumulatedAmountLogs::query()
            ->whereRaw('WEEKDAY(created_at) = 5') // 5 = Saturday
            ->whereRaw('YEAR(created_at) = ?', [$year]) // Filter by year
            ->select(
                DB::raw('MONTH(created_at) as month'),
                DB::raw('SUM(amount) as total_amount')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->get(); // Execute the query here

        // Generate Labels
        $labels = collect(range(1, 12))->map(function ($month) {
            return Carbon::createFromDate(null, $month, 1)->format('F');
        })->toArray();

        // Define 12 distinct colors for each month
        $colors = [
            1 => ['border' => '#FF5733', 'background' => 'rgba(255, 87, 51, 0.3)'],
            2 => ['border' => '#33FF57', 'background' => 'rgba(51, 255, 87, 0.3)'],
            3 => ['border' => '#3357FF', 'background' => 'rgba(51, 87, 255, 0.3)'],
            4 => ['border' => '#FF33A8', 'background' => 'rgba(255, 51, 168, 0.3)'],
            5 => ['border' => '#33FFF5', 'background' => 'rgba(51, 255, 245, 0.3)'],
            6 => ['border' => '#F5FF33', 'background' => 'rgba(245, 255, 51, 0.3)'],
            7 => ['border' => '#A833FF', 'background' => 'rgba(168, 51, 255, 0.3)'],
            8 => ['border' => '#FF8C33', 'background' => 'rgba(255, 140, 51, 0.3)'],
            9 => ['border' => '#33FFA8', 'background' => 'rgba(51, 255, 168, 0.3)'],
            10 => ['border' => '#8C33FF', 'background' => 'rgba(140, 51, 255, 0.3)'],
            11 => ['border' => '#FF338C', 'background' => 'rgba(255, 51, 140, 0.3)'],
            12 => ['border' => '#33A8FF', 'background' => 'rgba(51, 168, 255, 0.3)'],
        ];

        $chartData = [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Total Amount',
                    'data' => array_map(function ($month) use ($query) {
                        return $query->firstWhere('month', $month)?->total_amount ?? 0;
                    }, range(1, 12)),
                    'backgroundColor' => array_map(fn($month) => $colors[$month]['background'], range(1, 12)),
                    'borderColor' => array_map(fn($month) => $colors[$month]['border'], range(1, 12)),
                    'borderWidth' => 2,
                    'fill' => true,
                    'tension' => 0.4,
                ],
            ],
        ];

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
