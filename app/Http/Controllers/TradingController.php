<?php

namespace App\Http\Controllers;

use App\Exports\CommissionsExport;
use App\Imports\CommissionsImport;
use App\Models\TradeBrokerHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;

class TradingController extends Controller
{
    public function getCommissionsList()
    {
        return Inertia::render('Reports/Commissions/Listing/CommissionListing');
    }

    public function getCommissionData(Request $request)
    {
        if ($request->has('lazyEvent')) {
            $data = json_decode($request->only(['lazyEvent'])['lazyEvent'], true);

            //commission query
            $query = TradeBrokerHistory::query()
                ->with([
                    'broker:id,name',
                ]);

            //global filter
            if ($data['filters']['global']['value']) {
                $query->where(function ($q) use ($data) {
                    $keyword = $data['filters']['global']['value'];

                    $q->where('email', 'like', '%' . $keyword . '%');
                });
            }

            //date filter
            if (!empty($data['filters']['start_date']['value']) && !empty($data['filters']['end_date']['value'])) {
                $start_date = Carbon::parse($data['filters']['start_date']['value'])->addDay()->startOfDay(); //add day to ensure capture entire day
                $end_date = Carbon::parse($data['filters']['end_date']['value'])->addDay()->endOfDay();

                $query->whereBetween('date', [$start_date, $end_date]);
            }

            //status filter
            if ($data['filters']['status']['value']) {
                $query->where('status', $data['filters']['status']['value']);
            }

            //sort field/order
            if ($data['sortField'] && $data['sortOrder']) {
                $order = $data['sortOrder'] == 1 ? 'asc' : 'desc';
                $query->orderBy($data['sortField'], $order);
            } else {
                $query->latest();
            }

            //export logic
            if($request->has('exportStatus') && $request->exportStatus) {
                return Excel::download(new CommissionsExport($query), now() . '-commission-list.xlsx');
            }

            $commissions = $query->paginate($data['rows']);

            return response()->json([
                'success' => true,
                'data' => $commissions,
            ]);
        }
    }

    public function importCommissions(Request $request)
    {

        $request->validate([
            'broker' => ['required'],
            'commission_file' => ['required', 'mimes:xlsx,xls,csv', 'max:25000'],
        ]);

        $file = $request->file('commission_file');

        $broker_id = $request->broker['id'];

        Excel::import(new CommissionsImport($broker_id), $file);

        return Redirect::route('report.getCommissionsList');
    }
    
}
