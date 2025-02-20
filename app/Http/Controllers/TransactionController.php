<?php

namespace App\Http\Controllers;

use App\Exports\DepositExport;
use App\Exports\PendingDepositExport;
use App\Exports\WithdrawalExport;
use App\Imports\DepositImport;
use App\Models\BrokerConnection;
use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;

class TransactionController extends Controller
{
    public function getDepositHistory()
    {
        Gate::authorize('access-deposit-history', Transaction::class);

        $pendingCounts = Transaction::where('transaction_type', 'deposit')
            ->whereNot('status', 'processing')
            ->count();

        return Inertia::render('Transaction/History/Deposit/DepositHistory', [
            'pendingDepositCounts' => $pendingCounts,
        ]);
    }

    public function getDepositHistoryData(Request $request)
    {
        Gate::authorize('access-deposit-history', Transaction::class);

        if ($request->has('lazyEvent')) {
            $data = json_decode($request->only(['lazyEvent'])['lazyEvent'], true); //only() extract parameters in lazyEvent

            //user query
            $query = Transaction::query()
                ->with([
                    'user:id,name,email,upline_id',
                    'from_wallet:id,type,address,currency_symbol',
                    'to_wallet:id,type,address,currency_symbol',
                ])
                ->where('transaction_type', 'deposit')
                ->where('status', 'success');

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
        Gate::authorize('access-withdrawal-history', Transaction::class);

        $pendingCounts = Transaction::where('transaction_type', 'withdrawal')
            ->whereNot('status', 'processing')
            ->count();

        return Inertia::render('Transaction/History/Withdrawal/WithdrawalHistory', [
            'pendingWithdrawalCounts' => $pendingCounts,
        ]);
    }

    public function getWithdrawalHistoryData(Request $request)
    {
        Gate::authorize('access-withdrawal-history', Transaction::class);

        if ($request->has('lazyEvent')) {
            $data = json_decode($request->only(['lazyEvent'])['lazyEvent'], true); //only() extract parameters in lazyEvent

            //user query
            $query = Transaction::query()
                ->with([
                    'user:id,name,email,upline_id',
                    'from_wallet:id,type,address,currency_symbol',
                    'to_wallet:id,type,address,currency_symbol',
                ])
                ->where('transaction_type', 'withdrawal')
                ->where('status', 'success');


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

    public function getPendingDeposit()
    {
        Gate::authorize('access-pending-deposit', Transaction::class);

        return Inertia::render('Transaction/Pending/Deposit/PendingDeposit');
    }

    public function getDepositRecentApproval()
    {
        $recentApprovals = Transaction::query()
            ->select(
                'id',
                'transaction_type',
                'transaction_number',
                'approval_at',
                'status',
                'handle_by'
            )->with('approval_by:id,name')
            ->whereNot('status', 'processing')
            ->where('approval_at', '>=', Carbon::now()->subDay())
            ->where('transaction_type', 'deposit')
            ->orderByDesc('approval_at')
            ->get();

        return response()->json([
            'recentApprovals' => $recentApprovals,
        ]);
    }

    public function getPendingDepositData(Request $request)
    {
        Gate::authorize('access-pending-deposit', Transaction::class);

        if ($request->has('lazyEvent')) {
            $data = json_decode($request->only(['lazyEvent'])['lazyEvent'], true); //only() extract parameters in lazyEvent

            //user query
            $query = Transaction::query()
                ->with([
                    'user:id,name,email,upline_id',
                    'user.upline:id,name,email',
                    'from_wallet:id,type,address,currency_symbol',
                    'to_wallet:id,type,address,currency_symbol',
                    'media',
                ])
                ->where('transaction_type', 'deposit')
                ->where('status', 'processing');


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

            //sort field/order
            if ($data['sortField'] && $data['sortOrder']) {
                $order = $data['sortOrder'] == 1 ? 'asc' : 'desc';
                $query->orderBy($data['sortField'], $order);
            } else {
                $query->latest();
            }

            //export logic
            if ($request->has('exportStatus') && $request->exportStatus) {
                return Excel::download(new PendingDepositExport($query), now() . '-pending-deposit-list.xlsx');
            }

            $users = $query->paginate($data['rows']);

            $users->each(function ($transaction) {
                $transaction->payment_slips = $transaction->getMedia('payment_slips')
                    ->map(function ($media) {
                        return $media->getUrl();
                    });
            });

            $totalPendingAmount = (clone $query)
                ->sum('amount');

            $pendingCounts = (clone $query)
                ->count();


            return response()->json([
                'success' => true,
                'data' => $users,
                'totalPendingAmount' => $totalPendingAmount,
                'pendingDepositCounts' => $pendingCounts,
            ]);
        }

        return response()->json(['success' => false, 'data' => []]);
    }

    public function getPendingWithdrawal()
    {
        Gate::authorize('access-pending-withdrawal', Transaction::class);

        return Inertia::render('Transaction/Pending/Withdrawal/PendingWithdrawal');
    }

    public function getWithdrawalRecentApproval()
    {
        $recentApprovals = Transaction::query()
            ->select(
                'id',
                'transaction_type',
                'transaction_number',
                'approval_at',
                'status',
                'handle_by'
            )->with('approval_by:id,name')
            ->whereNot('status', 'processing')
            ->where('approval_at', '>=', Carbon::now()->subDay())
            ->where('transaction_type', 'withdrawal')
            ->orderByDesc('approval_at')
            ->get();

        return response()->json([
            'recentApprovals' => $recentApprovals,
        ]);
    }

    public function getPendingWithdrawalData(Request $request)
    {
        Gate::authorize('access-pending-withdrawal', Transaction::class);

        if ($request->has('lazyEvent')) {
            $data = json_decode($request->only(['lazyEvent'])['lazyEvent'], true); //only() extract parameters in lazyEvent

            //user query
            $query = Transaction::query()
                ->with([
                    'user:id,name,email,upline_id',
                    'user.upline:id,name,email',
                    'from_wallet:id,type,address,currency_symbol',
                    'to_wallet:id,type,address,currency_symbol',
                ])
                ->where('transaction_type', 'withdrawal')
                ->where('status', 'processing');;


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

            $totalPendingAmount = (clone $query)
                ->sum('amount');

            $pendingCounts = (clone $query)
                ->count();

            return response()->json([
                'success' => true,
                'data' => $users,
                'totalPendingAmount' => $totalPendingAmount,
                'pendingWithdrawalCounts' => $pendingCounts,
            ]);
        }

        return response()->json(['success' => false, 'data' => []]);
    }

    public function pendingDepositApproval(Request $request)
    {
        Gate::authorize('edit-pending-deposit', Transaction::class);

        $validatedData = $request->validate([
            'remarks' => ['required_if:action,reject_transaction'],
        ], [
            'remarks.required_if' => trans('validation.required_if', [
                'attribute' => trans('public.remarks'),
                'other' => trans('public.action'),
                'value' => trans('public.reject_transaction')
            ])
        ]);

        $transaction = Transaction::find($request->transaction_id);
        $wallet = Wallet::find($transaction->to_wallet_id);

        if ($request->action == 'approve_transaction') {
            $transaction->status = 'success';

            $wallet->balance += $transaction->amount;
            $wallet->save();
        } else {
            $transaction->status = 'rejected';
            $transaction->remarks = $validatedData['remarks'];
        }
        $transaction->new_wallet_amount = $wallet->balance;
        $transaction->approval_at = now();
        $transaction->handle_by = \Auth::id();
        $transaction->update();

        return back()->with('toast', 'success');
    }

    public function pendingWithdrawalApproval(Request $request)
    {
        Gate::authorize('edit-pending-withdrawal', Transaction::class);

        $validatedData = $request->validate([
            'remarks' => ['required_if:action,reject_transaction'],
        ], [
            'remarks.required_if' => trans('validation.required_if', [
                'attribute' => trans('public.remarks'),
                'other' => trans('public.action'),
                'value' => trans('public.reject_transaction')
            ])
        ], [
            'remarks' => trans('public.remarks')
        ]);

        $transaction = Transaction::find($request->transaction_id);

        if ($request->action == 'approve') {
            $transaction->status = 'success';
            $transaction->update();
        } else {
            $transaction->status = 'rejected';
            $transaction->remarks = $validatedData['remarks'];
            $transaction->update();
        }
    }
}
