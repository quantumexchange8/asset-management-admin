<?php

namespace App\Http\Controllers;

use App\Exports\RebateBonusExport;
use App\Exports\StandardBonusExport;
use App\Models\BonusHistory;
use App\Models\TradeRebateSummary;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public function standard_bonus()
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
            ]);

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

            $connections = $query->paginate($data['rows']);

            return response()->json([
                'success' => true,
                'data' => $connections,
                'totalBonusAmount' => $totalBonusAmount,
                'maxBonusAmount' => $maxBonusAmount ?? 0,
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

            $connections = $query->paginate($data['rows']);

            return response()->json([
                'success' => true,
                'data' => $connections,
                'totalBonusAmount' => $totalBonusAmount,
                'maxBonusAmount' => $maxBonusAmount ?? 0,
            ]);
        }

        return response()->json(['success' => false, 'data' => []]);
    }
}
