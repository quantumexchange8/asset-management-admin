<?php

namespace App\Http\Controllers;

use App\Exports\BrokerConnectionExport;
use App\Imports\BrokerConnectionImport;
use App\Models\BrokerConnection;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;

class ConnectionController extends Controller
{
    public function broker_connection()
    {
        return Inertia::render('Connection/BrokerConnection/BrokerConnection');
    }

    public function getConnections(Request $request)
    {
        if ($request->has('lazyEvent')) {
            $data = json_decode($request->only(['lazyEvent'])['lazyEvent'], true);

            $query = BrokerConnection::with([
                'user:id,name,email,hierarchyList',
                'broker',
                'broker.media'
            ]);

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

    public function download_import_example()
    {
        return response()->download(public_path('import_template/broker_connections_import_example.xlsx'));
    }

    public function importBrokerConnection(Request $request)
    {
        Validator::make($request->all(), [
            'broker_id' => ['required'],
            'import_file' => ['required', 'mimes:xlsx,xls,csv', 'max:25000'],
        ])->setAttributeNames([
            'broker_id' => trans('public.broker'),
            'import_file' => trans('public.file'),
        ])->validate();

        $file = $request->file('import_file');

        $import = new BrokerConnectionImport($request->broker_id);
        $import->import($file);

        return back()->with('toast');
    }
}
