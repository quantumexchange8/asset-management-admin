<?php

namespace App\Http\Controllers;

use App\Models\BrokerConnection;
use App\Models\Transaction;
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
        $query = Transaction::query()
            ->where('status', '=', 'success')
            ->where('fund_type', '=', 'real_fund')
            ->whereIn('transaction_type', ['deposit', 'withdrawal']);

        $totalDeposit = (clone $query)->where('transaction_type', 'deposit')->sum('transaction_amount');
        $totalWithdrawal = (clone $query)->where('transaction_type', 'withdrawal')->sum('transaction_amount');

        // Apply filters only if values are selected
        $filteredQuery = $query->when($request->filled('days'), function ($dayQuery) use ($request) {
            $days = (int) $request->input('days'); // Convert to integer
            $month = (int) $request->input('month'); // Selected month
            $year = (int) $request->input('year'); // Selected year

            // Start of the selected month
            $startDate = Carbon::create($year, $month, 1)->startOfDay();
            // End of the selected period (first X days)
            $endDate = Carbon::create($year, $month, min($days, cal_days_in_month(CAL_GREGORIAN, $month, $year)))->endOfDay();

            $dayQuery->whereBetween('created_at', [$startDate, $endDate]);
        })->when($request->filled('month') && $request->filled('year'), function ($dayQuery) use ($request) {
            $month = $request->input('month');
            $year = $request->input('year');

            $dayQuery->whereYear('created_at', $year)
                ->whereMonth('created_at', $month);
        });

        $totalMonthDeposit = (clone $filteredQuery)->where('transaction_type', 'deposit')->sum('transaction_amount');
        $totalMonthWithdrawal = (clone $filteredQuery)->where('transaction_type', 'withdrawal')->sum('transaction_amount');

        $chartResults = $filteredQuery->select(
            DB::raw('DAY(created_at) as day'),
            'transaction_type',
            DB::raw('SUM(transaction_amount) as amount')
        )->groupBy('day', 'transaction_type')->get();

        $uniqueTransactionType = $chartResults->pluck('transaction_type')->unique();
        $year = $request->year ?? Carbon::now()->year;
        $month = $request->month ?? Carbon::now()->month;

        // Initialize the chart data structure
        $chartData = [
            'labels' => range(1, $request->input('days', cal_days_in_month(CAL_GREGORIAN, $month, $year))), // Generate an array of days
            'datasets' => [],
        ];

        //color
        $colors = [
            'deposit' => ['border' => '#12B76A', 'background' => 'rgba(18, 183, 106, 0.3)'],
            'withdrawal' => ['border' => '#FF2D55', 'background' => 'rgba(255, 45, 85, 0.3)']
        ];

        // Loop through each unique type and create a dataset
        foreach ($uniqueTransactionType as $transactionType) {
            $transactionData = $chartResults->where('transaction_type', $transactionType);

            $dataset = [
                'label' => trans("public.$transactionType"), // Capitalize first letter (Deposit, Withdrawal)
                'data' => array_map(function ($day) use ($transactionData) {
                    return $transactionData->firstWhere('day', $day)->amount ?? 0;
                }, $chartData['labels']),
                'borderColor' => $colors[$transactionType]['border'],
                'backgroundColor' => $colors[$transactionType]['background'],
                'borderWidth' => 2,
                'pointStyle' => false,
                'fill' => true,
                'tension' => 0.4, // Make the line smooth
            ];

            $chartData['datasets'][] = $dataset;
        }

        return response()->json([
            'chartData' => $chartData,
            'totalDeposit' => $totalDeposit,
            'totalMonthDeposit' => $totalMonthDeposit,
            'totalWithdrawal' => $totalWithdrawal,
            'totalMonthWithdrawal' => $totalMonthWithdrawal,
        ]);
    }
}
