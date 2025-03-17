<?php

namespace App\Http\Controllers;

use App\Exports\BrokerConnectionExport;
use App\Imports\BrokerConnectionImport;
use App\Models\BrokerConnection;
use App\Models\User;
use App\Models\Wallet;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;

class ConnectionController extends Controller
{
    public function broker_connection()
    {
        Gate::authorize('access', BrokerConnection::class);

        return Inertia::render('Connection/BrokerConnection/BrokerConnection');
    }

    public function getConnections(Request $request)
    {
        Gate::authorize('access', BrokerConnection::class);

        if ($request->has('lazyEvent')) {
            $data = json_decode($request->only(['lazyEvent'])['lazyEvent'], true);

            $query = BrokerConnection::with([
                'user:id,name,email,hierarchyList',
                'broker',
                'broker.media'
            ])
                ->where('status', 'success');

            if ($data['filters']['global']['value']) {
                $keyword = $data['filters']['global']['value'];


                $query->where(function ($q) use ($keyword) {
                    $q->whereHas('user', function ($query) use ($keyword) {
                        $query->where(function ($q) use ($keyword) {
                            $q->where('name', 'like', '%' . $keyword . '%')
                                ->orWhere('email', 'like', '%' . $keyword . '%');
                        });
                    })->orWhere('connection_number', 'like', '%' . $keyword . '%');
                });
            }

            if (!empty($data['filters']['start_join_date']['value']) && !empty($data['filters']['end_join_date']['value'])) {
                $start_join_date = \Illuminate\Support\Carbon::parse($data['filters']['start_join_date']['value'])->addDay()->startOfDay();
                $end_join_date = Carbon::parse($data['filters']['end_join_date']['value'])->addDay()->endOfDay();

                $query->whereBetween('joined_at', [$start_join_date, $end_join_date]);
            }

            if ($data['sortField'] && $data['sortOrder']) {
                $order = $data['sortOrder'] == 1 ? 'asc' : 'desc';
                $query->orderBy($data['sortField'], $order);
            } else {
                $query->orderByDesc('joined_at');
            }

            // Export logic
            if ($request->exportStatus) {
                return Excel::download(new BrokerConnectionExport($query->clone()), now() . '-broker-connection-report.xlsx');
            }

            $connections = $query->paginate($data['rows']);

            $totalActiveFund = (clone $query)
                ->sum('capital_fund');

            $totalConnections = (clone $query)
                ->distinct('user_id')
                ->count();

            return response()->json([
                'success' => true,
                'data' => $connections,
                'totalActiveFund' => $totalActiveFund,
                'totalConnections' => $totalConnections,
            ]);
        }

        return response()->json(['success' => false, 'data' => []]);
    }

    public function pending_connection()
    {
        return Inertia::render('Connection/BrokerConnection/Pending/PendingConnection');
    }

    public function pending_connection_data(Request $request)
    {
        if ($request->has('lazyEvent')) {
            $data = json_decode($request->only(['lazyEvent'])['lazyEvent'], true);

            $query = BrokerConnection::with([
                'user:id,name,email,hierarchyList,upline_id',
                'user.upline:id,name,email',
                'broker',
                'broker.media'
            ])
                ->where('status', 'pending');

            if ($data['filters']['global']['value']) {
                $keyword = $data['filters']['global']['value'];

                $query->where(function ($q) use ($keyword) {
                    $q->whereHas('user', function ($query) use ($keyword) {
                        $query->where(function ($q) use ($keyword) {
                            $q->where('name', 'like', '%' . $keyword . '%')
                                ->orWhere('email', 'like', '%' . $keyword . '%');
                        });
                    })->orWhere('connection_number', 'like', '%' . $keyword . '%');
                });
            }

            if (!empty($data['filters']['start_join_date']['value']) && !empty($data['filters']['end_join_date']['value'])) {
                $start_join_date = \Illuminate\Support\Carbon::parse($data['filters']['start_join_date']['value'])->addDay()->startOfDay();
                $end_join_date = Carbon::parse($data['filters']['end_join_date']['value'])->addDay()->endOfDay();

                $query->whereBetween('joined_at', [$start_join_date, $end_join_date]);
            }

            if ($data['sortField'] && $data['sortOrder']) {
                $order = $data['sortOrder'] == 1 ? 'asc' : 'desc';
                $query->orderBy($data['sortField'], $order);
            } else {
                $query->orderByDesc('joined_at');
            }

            $connections = $query->paginate($data['rows']);

            $totalPendingFund = (clone $query)
                ->sum('capital_fund');

            $totalPendingConnections = (clone $query)
                ->distinct('user_id')
                ->count();

            return response()->json([
                'success' => true,
                'data' => $connections,
                'totalPendingFund' => $totalPendingFund,
                'totalPendingConnections' => $totalPendingConnections,
            ]);
        }

        return response()->json(['success' => false, 'data' => []]);
    }

    public function pendingConnectionApproval(Request $request)
    {

        Validator::make($request->all(), [
            'action' => ['required'],
        ])->setAttributeNames([
            'action' => trans('public.action'),
        ])->validate();

        $connections = BrokerConnection::find($request->connection_id);

        if ($request->action == 'approve') {
            $connections->status = 'success';
            $connections->joined_at = now();
        } else {
            if (!$request->remarks) {
                throw ValidationException::withMessages(['remarks' => trans('public.remarks_required_reject')]);
            }
            $connections->status = 'rejected';
            $connections->remarks = $request->remarks;
            $wallet = Wallet::where('user_id', $connections->user_id)
                ->where('type', 'cash_wallet')
                ->first();

            $wallet->balance += $connections->capital_fund;
            $wallet->real_fund += $connections->capital_fund;
            $wallet->save();
        }
        $connections->update();

        return back()->with('toast', 'success');
    }

    public function download_import_example()
    {
        return response()->download(public_path('import_template/broker_connections_import_example.xlsx'));
    }

    public function importBrokerConnection(Request $request)
    {
        Gate::authorize('import', BrokerConnection::class);

        Validator::make($request->all(), [
            'broker_id' => ['required'],
            //'type' => ['required'],
            'import_file' => ['required', 'mimes:xlsx,xls,csv', 'max:25000'],
        ])->setAttributeNames([
            'broker_id' => trans('public.broker'),
            //'type' => trans('public.type'),
            'import_file' => trans('public.file'),
        ])->validate();

        $file = $request->file('import_file');

        $import = new BrokerConnectionImport($request->broker_id, $request->type);
        $import->import($file);

        return back()->with('toast');
    }
}
