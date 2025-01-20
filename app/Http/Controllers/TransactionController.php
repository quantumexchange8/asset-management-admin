<?php

namespace App\Http\Controllers;

use App\Exports\DepositExport;
use App\Exports\WithdrawalExport;
use App\Imports\DepositImport;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;

class TransactionController extends Controller
{

    public function getDepositHistory()
    {
        return Inertia::render('Transaction/History/Deposit/DepositHistory');
    }

    public function getDepositHistoryData(Request $request)
    {
        if ($request->has('lazyEvent')) {
            $data = json_decode($request->only(['lazyEvent'])['lazyEvent'], true); //only() extract parameters in lazyEvent

            //user query
            $query = Transaction::query()
                ->with([
                    'user:id,name,email',
                ])
                ->where('transaction_type', 'deposit');


            //global filter
            if (!empty($data['filters']['global']['value'])) {
                $query->whereHas('user', function ($q) use ($data) {
                    $keyword = $data['filters']['global']['value'];

                    // Filter on the 'name' column in the related 'user' table
                    $q->where('name', 'like', '%' . $keyword . '%')
                        ->orWhere('transaction_number', 'like', '%' . $keyword . '%');
                });
            }

            //date filter
            if (!empty($data['filters']['start_date']['value']) && !empty($data['filters']['end_date']['value'])) {
                $start_date = Carbon::parse($data['filters']['start_date']['value'])->addDay()->startOfDay(); //add day to ensure capture entire day
                $end_date = Carbon::parse($data['filters']['end_date']['value'])->addDay()->endOfDay();

                $query->whereBetween('approval_at', [$start_date, $end_date]);
            }

            //fund_type filter
            if ($data['filters']['fund_type']['value']) {
                $query->where('fund_type', $data['filters']['fund_type']['value']);
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
            if ($request->has('exportStatus') && $request->exportStatus) {
                return Excel::download(new DepositExport($query), now() . '-deposit-history-list.xlsx');
            }

            $users = $query->paginate($data['rows']);

            return response()->json([
                'success' => true,
                'data' => $users,
            ]);
        }

        return response()->json(['success' => false, 'data' => []]);
    }

    public function getWithdrawalHistory()
    {
        return Inertia::render('Transaction/History/Withdrawal/WithdrawalHistory');
    }

    public function getWithdrawalHistoryData(Request $request)
    {
        if ($request->has('lazyEvent')) {
            $data = json_decode($request->only(['lazyEvent'])['lazyEvent'], true); //only() extract parameters in lazyEvent

            //user query
            $query = Transaction::query()
                ->with([
                    'user:id,name,email',
                ])
                ->where('transaction_type', 'withdrawal');


            //global filter
            if (!empty($data['filters']['global']['value'])) {
                $query->whereHas('user', function ($q) use ($data) {
                    $keyword = $data['filters']['global']['value'];

                    // Filter on the 'name' column in the related 'user' table
                    $q->where('name', 'like', '%' . $keyword . '%')
                        ->orWhere('transaction_number', 'like', '%' . $keyword . '%');
                });
            }

            //date filter
            if (!empty($data['filters']['start_date']['value']) && !empty($data['filters']['end_date']['value'])) {
                $start_date = Carbon::parse($data['filters']['start_date']['value'])->addDay()->startOfDay(); //add day to ensure capture entire day
                $end_date = Carbon::parse($data['filters']['end_date']['value'])->addDay()->endOfDay();

                $query->whereBetween('approval_at', [$start_date, $end_date]);
            }

            //fund_type filter
            if ($data['filters']['fund_type']['value']) {
                $query->where('fund_type', $data['filters']['fund_type']['value']);
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
            if ($request->has('exportStatus') && $request->exportStatus) {
                return Excel::download(new WithdrawalExport($query), now() . '-withdrawal-history-list.xlsx');
            }

            $users = $query->paginate($data['rows']);

            return response()->json([
                'success' => true,
                'data' => $users,
            ]);
        }

        return response()->json(['success' => false, 'data' => []]);
    }

    public function importDepositHistory(Request $request)
    {
        $request->validate([
            'deposit_history_file' => 'required|mimes:xlsx,xls,csv|max:25000',
        ]);

        $file = $request->file('deposit_history_file');

        Excel::import(new DepositImport, $file);

        return Redirect::route('transaction.history.getDepositHistory');
    }
}
