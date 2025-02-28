<?php

namespace App\Http\Controllers;

use App\Models\BrokerAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class BrokerAccountController extends Controller
{
    public function account_listing()
    {
        return Inertia::render('Account/Listing/AccountListing');
    }

    public function getAccountListingData(Request $request)
    {
        if ($request->has('lazyEvent')) {
            $data = json_decode($request->only(['lazyEvent'])['lazyEvent'], true); //only() extract parameters in lazyEvent

            //user query
            $query = BrokerAccount::query()
                ->with([
                    'user:id,name,email,upline_id',
                    'user.upline:id,name,email',
                    'broker',
                    'broker.media'
                ])->whereNotIn('status', ['pending', 'rejected']);


            //global filter
            if ($data['filters']['global']['value']) {
                $keyword = $data['filters']['global']['value'];

                $query->where(function ($q) use ($keyword) {
                    $q->whereHas('user', function ($query) use ($keyword) {
                        $query->where(function ($q) use ($keyword) {
                            $q->where('name', 'like', '%' . $keyword . '%')
                                ->orWhere('email', 'like', '%' . $keyword . '%');
                        });
                    });
                });
            }

            //date filter
            if (!empty($data['filters']['start_request_date']['value']) && !empty($data['filters']['end_request_date']['value'])) {
                $start_date = Carbon::parse($data['filters']['start_request_date']['value'])->addDay()->startOfDay(); //add day to ensure capture entire day
                $end_date = Carbon::parse($data['filters']['end_request_date']['value'])->addDay()->endOfDay();

                $query->whereBetween('created_at', [$start_date, $end_date]);
            }

            //sort field/order
            if ($data['sortField'] && $data['sortOrder']) {
                $order = $data['sortOrder'] == 1 ? 'asc' : 'desc';
                $query->orderBy($data['sortField'], $order);
            } else {
                $query->orderByDesc('created_at');
            }

            $accounts = $query->paginate($data['rows']);

            $accounts->each(function ($user) {
                $user->profile_photo = $user->getMedia('account_image')
                    ->map(function ($media) {
                        return $media->getUrl();
                    });
            });

            $accountCounts = (clone $query)
                ->distinct('user_id')
                ->count();

            $totalBrokerCapital = (clone $query)
                ->sum('broker_capital');

            // $accounts->each(function ($account) {
            //     if (!$account->master_password == null) {
            //         $account->decrypted_master_password = Crypt::decrypt($account->master_password);
            //     }
            // });

            return response()->json([
                'success' => true,
                'data' => $accounts,
                'accountCounts' => $accountCounts,
                'totalBrokerCapital' => $totalBrokerCapital,
            ]);
        }

        return response()->json(['success' => false, 'data' => []]);
    }

    public function pending_account()
    {
        return Inertia::render('Account/Pending/PendingAccountListing');
    }

    public function getPendingAccountData(Request $request)
    {
        if ($request->has('lazyEvent')) {
            $data = json_decode($request->only(['lazyEvent'])['lazyEvent'], true); //only() extract parameters in lazyEvent

            //user query
            $query = BrokerAccount::query()
                ->with([
                    'user:id,name,email,upline_id',
                    'user.upline:id,name,email',
                    'broker',
                    'broker.media',
                    'media',
                ])->where('status', 'pending');


            //global filter
            if ($data['filters']['global']['value']) {
                $keyword = $data['filters']['global']['value'];

                $query->where(function ($q) use ($keyword) {
                    $q->whereHas('user', function ($query) use ($keyword) {
                        $query->where(function ($q) use ($keyword) {
                            $q->where('name', 'like', '%' . $keyword . '%')
                                ->orWhere('email', 'like', '%' . $keyword . '%');
                        });
                    });
                });
            }

            //date filter
            if (!empty($data['filters']['start_request_date']['value']) && !empty($data['filters']['end_request_date']['value'])) {
                $start_date = Carbon::parse($data['filters']['start_request_date']['value'])->addDay()->startOfDay(); //add day to ensure capture entire day
                $end_date = Carbon::parse($data['filters']['end_request_date']['value'])->addDay()->endOfDay();

                $query->whereBetween('created_at', [$start_date, $end_date]);
            }

            //sort field/order
            if ($data['sortField'] && $data['sortOrder']) {
                $order = $data['sortOrder'] == 1 ? 'asc' : 'desc';
                $query->orderBy($data['sortField'], $order);
            } else {
                $query->orderByDesc('created_at');
            }

            $accounts = $query->paginate($data['rows']);

            $accounts->each(function ($user) {
                $user->profile_photo = $user->getMedia('account_image')
                    ->map(function ($media) {
                        return $media->getUrl();
                    });
            });

            $pendingAccountCounts = (clone $query)
                ->distinct('user_id')
                ->count();

            $totalPendingCapital = (clone $query)
                ->sum('broker_capital');

            $accounts->each(function ($transaction) {
                $transaction->broker_account_image = $transaction->getMedia('account_proof')
                    ->map(function ($media) {
                        return $media->getUrl();
                    });
            });

            // $accounts->each(function ($account) {
            //     $account->decrypted_master_password = Crypt::decrypt($account->master_password);
            // });

            return response()->json([
                'success' => true,
                'data' => $accounts,
                'pendingAccountCounts' => $pendingAccountCounts,
                'totalPendingCapital' => $totalPendingCapital,
            ]);
        }

        return response()->json(['success' => false, 'data' => []]);
    }

    public function pendingAccountApproval(Request $request)
    {

        Validator::make($request->all(), [
            'action' => ['required'],
        ])->setAttributeNames([
            'action' => trans('public.action'),
        ])->validate();

        $account = BrokerAccount::find($request->account_id);

        if ($request->action == 'approve') {
            $account->status = 'linked';
        } else {
            if (!$request->remarks) {
                throw ValidationException::withMessages(['remarks' => trans('public.remarks_required_reject')]);
            }

            $account->status = 'rejected';
            $account->remarks = $request->remarks;
        }
        $account->update();

        return back()->with('toast', 'success');
    }
}
