<?php

namespace App\Http\Controllers;

use App\Exports\RebateBonusExport;
use App\Exports\StandardBonusExport;
use App\Exports\TradeHistoryExport;
use App\Models\BonusHistory;
use App\Models\TradeBrokerHistory;
use App\Models\TradeRebateSummary;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{

    public function profit_sharing()
    {
        return Inertia::render('Reports/Profit/ProfitSharing');
    }

    public function getProfitSharingData(Request $request)
    {
        if ($request->has('lazyEvent')) {
            $data = json_decode($request->only(['lazyEvent'])['lazyEvent'], true);

            $query = BonusHistory::with([
                'subject_user:id,username,hierarchyList',
                'broker',
                'broker.media',
            ])
                ->where('bonus_type', 'personal_share');

            if ($data['filters']['global']['value']) {
                $keyword = $data['filters']['global']['value'];

                $query->where(function ($q) use ($keyword) {
                    $q->whereHas('user', function ($query) use ($keyword) {
                        $query->where(function ($q) use ($keyword) {
                            $q->where('name', 'like', '%' . $keyword . '%')
                                ->orWhere('email', 'like', '%' . $keyword . '%');
                        });
                    })->orWhereHas('subject_user', function ($query) use ($keyword) {
                        $query->where(function ($q) use ($keyword) {
                            $q->where('name', 'like', '%' . $keyword . '%')
                                ->orWhere('email', 'like', '%' . $keyword . '%');
                        });
                    })->orWhere('bonus_type', 'like', '%' . $keyword . '%');
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

            $totalBonusAmount = (clone $query)
                ->sum('bonus_amount');

            $maxBonusAmount = (clone $query)
                ->orderByDesc('bonus_amount')
                ->first()
                ?->bonus_amount;

            $profitSharingCounts = (clone $query)
                ->count();

            $connections = $query->paginate($data['rows']);

            return response()->json([
                'success' => true,
                'data' => $connections,
                'totalBonusAmount' => $totalBonusAmount,
                'maxBonusAmount' => $maxBonusAmount ?? 0,
                'profitSharingCounts' => $profitSharingCounts,
            ]);
        }

        return response()->json(['success' => false, 'data' => []]);
    }

    public function ib_group_incentive()
    {
        return Inertia::render('Reports/Standard/StandardBonus');
    }

    public function getStandardBonusData(Request $request)
    {
        if ($request->has('lazyEvent')) {
            $data = json_decode($request->only(['lazyEvent'])['lazyEvent'], true);

            $query = BonusHistory::with([
                'user:id,name,email,hierarchyList',
                'subject_user:id,name,email,hierarchyList',
                'broker',
                'broker.media',
            ])
                ->whereNot('bonus_type', 'personal_share');

            if ($data['filters']['global']['value']) {
                $keyword = $data['filters']['global']['value'];

                $query->where(function ($q) use ($keyword) {
                    $q->whereHas('user', function ($query) use ($keyword) {
                        $query->where(function ($q) use ($keyword) {
                            $q->where('name', 'like', '%' . $keyword . '%')
                                ->orWhere('email', 'like', '%' . $keyword . '%');
                        });
                    })->orWhereHas('subject_user', function ($query) use ($keyword) {
                        $query->where(function ($q) use ($keyword) {
                            $q->where('name', 'like', '%' . $keyword . '%')
                                ->orWhere('email', 'like', '%' . $keyword . '%');
                        });
                    })->orWhere('bonus_type', 'like', '%' . $keyword . '%');
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

            // Export logic
            if ($request->exportStatus) {
                return Excel::download(new StandardBonusExport($query->clone()), now() . '-standard-bonus-report.xlsx');
            }

            $totalBonusAmount = (clone $query)
                ->sum('bonus_amount');

            $maxBonusAmount = (clone $query)
                ->orderByDesc('bonus_amount')
                ->first()
                ?->bonus_amount;

            $standardBonusCounts = (clone $query)
                ->count();

            $connections = $query->paginate($data['rows']);

            return response()->json([
                'success' => true,
                'data' => $connections,
                'totalBonusAmount' => $totalBonusAmount,
                'maxBonusAmount' => $maxBonusAmount ?? 0,
                'standardBonusCounts' => $standardBonusCounts,
            ]);
        }

        return response()->json(['success' => false, 'data' => []]);
    }

    public function rebate_bonus()
    {
        return Inertia::render('Reports/Rebate/RebateBonus');
    }

    public function getRebateBonusData(Request $request)
    {
        if ($request->has('lazyEvent')) {
            $data = json_decode($request->only(['lazyEvent'])['lazyEvent'], true);

            $query = TradeRebateSummary::with([
                'user:id,name,email,hierarchyList',
                'subject_user:id,name,email,hierarchyList',
                'broker',
                'broker.media',
            ])
                ->where('status', 'approved');

            if ($data['filters']['global']['value']) {
                $keyword = $data['filters']['global']['value'];

                $query->where(function ($q) use ($keyword) {
                    $q->whereHas('user', function ($query) use ($keyword) {
                        $query->where(function ($q) use ($keyword) {
                            $q->where('name', 'like', '%' . $keyword . '%')
                                ->orWhere('email', 'like', '%' . $keyword . '%');
                        });
                    })->orWhereHas('subject_user', function ($query) use ($keyword) {
                        $query->where(function ($q) use ($keyword) {
                            $q->where('name', 'like', '%' . $keyword . '%')
                                ->orWhere('email', 'like', '%' . $keyword . '%');
                        });
                    })->orWhere('symbol', 'like', '%' . $keyword . '%');
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

            // Export logic
            if ($request->exportStatus) {
                return Excel::download(new RebateBonusExport($query->clone()), now() . '-rebate-bonus-report.xlsx');
            }

            $totalBonusAmount = (clone $query)
                ->sum('rebate');

            $maxBonusAmount = (clone $query)
                ->orderByDesc('rebate')
                ->first()
                ?->rebate;

            $rebateBonusCounts = (clone $query)
                ->count();

            $connections = $query->paginate($data['rows']);

            return response()->json([
                'success' => true,
                'data' => $connections,
                'totalBonusAmount' => $totalBonusAmount,
                'maxBonusAmount' => $maxBonusAmount ?? 0,
                'rebateBonusCounts' => $rebateBonusCounts,
            ]);
        }

        return response()->json(['success' => false, 'data' => []]);
    }

    public function trade_history()
    {

        return Inertia::render('Reports/TradeHistory/TradeHistory');
    }

    public function getTradeHistoryData(Request $request)
    {
        if ($request->has('lazyEvent')) {
            $data = json_decode($request->only(['lazyEvent'])['lazyEvent'], true);

            $tabs = $request->input('tab', 'detail');

            if ($tabs === "summary") {
                $query = TradeBrokerHistory::with([
                    'user:id,name,email,hierarchyList',
                    'broker',
                    'broker.media',
                ])
                    ->select(
                        'broker_login',
                        DB::raw('SUM(trade_net_profit) as trade_net_profit'),
                        DB::raw('SUM(volume) as volume'),
                        DB::raw('DATE(created_at) as created_at'), // alias to avoid raw confusion
                        'user_id',
                        'broker_id'
                    )
                    ->where('status', 'approved')
                    ->groupBy([
                        'broker_login',
                        DB::raw('DATE(created_at)'),
                        'user_id',
                        'broker_id'
                    ]);
            } else {
                $query = TradeBrokerHistory::with([
                    'user:id,name,email,hierarchyList',
                    'broker',
                    'broker.media',
                ])
                    ->select([
                        '*',
                        DB::raw('DATE(created_at) as created_at')
                    ])
                    ->where('status', 'approved');
            }

            if (!empty($data['filters']['global']['value'])) {
                $keyword = $data['filters']['global']['value'];

                $query->where(function ($q) use ($keyword) {
                    $q->whereHas('user', function ($query) use ($keyword) {
                        $query->where(function ($q) use ($keyword) {
                            $q->where('name', 'like', '%' . $keyword . '%')
                                ->orWhere('email', 'like', '%' . $keyword . '%');
                        });
                    })->orWhere('symbol', 'like', '%' . $keyword . '%')
                        ->orWhere('broker_login', 'like', '%' . $keyword . '%');
                });
            }

            if (!empty($data['filters']['start_join_date']['value']) && !empty($data['filters']['end_join_date']['value'])) {
                $start_join_date = Carbon::parse($data['filters']['start_join_date']['value'])->addDay()->startOfDay();
                $end_join_date = Carbon::parse($data['filters']['end_join_date']['value'])->addDay()->endOfDay();

                $query->whereBetween('created_at', [$start_join_date, $end_join_date]);
            }

            if (!empty($data['sortField']) && isset($data['sortOrder'])) {
                $order = $data['sortOrder'] == 1 ? 'asc' : 'desc';

                $field = match ($data['sortField']) {
                    'created_at' => DB::raw('DATE(created_at)'),
                    'volume' => DB::raw('SUM(volume)'),
                    'trade_net_profit' => DB::raw('SUM(trade_net_profit)'),
                    default => $data['sortField']
                };

                $query->orderBy($field, $order);
            } else {
                // Default sort: latest records on top using MAX(created_at)
                $query->orderByDesc(DB::raw('DATE(created_at)'));
            }

            // Export logic
            if ($request->exportStatus) {
                $dateLabel = null;

                if (!empty($data['filters']['start_join_date']['value']) && !empty($data['filters']['end_join_date']['value'])) {
                    $start = Carbon::parse($data['filters']['start_join_date']['value'])->format('Y-m-d');
                    $end = Carbon::parse($data['filters']['end_join_date']['value'])->format('Y-m-d');
                    $dateLabel = trans('public.date_range_caption') . ' ' . $start . ' - ' . $end;
                }

                return Excel::download(
                    new TradeHistoryExport($query->clone(), $dateLabel),
                    now() . '-trade-broker-history-report.xlsx'
                );
            }

            if ($tabs === "summary") {
                $clone = clone $query;
                $totalBonusAmount = DB::table(DB::raw("({$clone->toSql()}) as grouped_summary"))
                    ->mergeBindings($clone->getQuery())
                    ->sum('trade_net_profit');
            } else {
                $totalBonusAmount = (clone $query)
                    ->sum('trade_net_profit');
            }

            $maxBonusAmount = (clone $query)
                ->orderByDesc('trade_net_profit')
                ->first()
                ?->trade_net_profit;

            $tradeHistoryCounts = (clone $query)
                ->count();

            $connections = $query->paginate($data['rows']);

            return response()->json([
                'success' => true,
                'data' => $connections,
                'totalBonusAmount' => $totalBonusAmount,
                'maxBonusAmount' => $maxBonusAmount ?? 0,
                'tradeHistoryCounts' => $tradeHistoryCounts,
            ]);
        }

        return response()->json(['success' => false, 'data' => []]);
    }
}
