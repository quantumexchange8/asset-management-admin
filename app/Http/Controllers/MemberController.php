<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use App\Policies\UserPolicy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Str;
use Inertia\Inertia;
use App\Services\RunningNumberService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class MemberController extends Controller
{
    public function getMemberList()
    {
        Gate::authorize('access', User::class);

        return Inertia::render('Member/Listing/MemberListing');
    }

    public function getMemberData(Request $request)
    {
        Gate::authorize('access', User::class);

        if ($request->has('lazyEvent')) {
            $data = json_decode($request->only(['lazyEvent'])['lazyEvent'], true); //only() extract parameters in lazyEvent

            //user query
            $query = User::query()
                ->with([
                    'country:id,name,emoji,iso2,translations',
                    'rank:id,rank_name',
                    'upline:id,name,email,upline_id',
                ])
                ->where('role', 'user');

            //global filter
            if ($data['filters']['global']['value']) {
                $query->where(function ($q) use ($data) { //function() allow to add more condition' use ($data) means $data is passed into the clause to be use
                    $keyword = $data['filters']['global']['value'];

                    $q->where('name', 'like', '%' . $keyword . '%')
                        ->orWhere('email', 'like', '%' . $keyword . '%');
                });
            }

            //date filter
            if (!empty($data['filters']['start_date']['value']) && !empty($data['filters']['end_date']['value'])) {
                $start_date = Carbon::parse($data['filters']['start_date']['value'])->addDay()->startOfDay(); //add day to ensure capture entire day
                $end_date = Carbon::parse($data['filters']['end_date']['value'])->addDay()->endOfDay();

                $query->whereBetween('created_at', [$start_date, $end_date]);
            }

            //referrer filter
            if ($data['filters']['referrer']['value']) {
                $query->where('upline_id', $data['filters']['referrer']['value']);
            }

            //country filter
            if ($data['filters']['country']['value']) {
                $query->where('country_id', $data['filters']['country']['value']);
            }

            //rank filter
            if ($data['filters']['rank']['value']) {
                $query->where('setting_rank_id', $data['filters']['rank']['value']);
            }

            //kyc status filter
            if ($data['filters']['status']['value']) {
                $query->where('kyc_status', $data['filters']['status']['value']);
            }

            //sort field/order
            if ($data['sortField'] && $data['sortOrder']) {
                $order = $data['sortOrder'] == 1 ? 'asc' : 'desc';
                $query->orderBy($data['sortField'], $order);
            } else {
                $query->latest();
            }

            $users = $query->paginate($data['rows']);

            $memberCounts = (clone $query)
                ->count();

            $verifiedUser = (clone $query)
                ->where('kyc_status', 'verified')
                ->count();

            $unverifiedUser = (clone $query)
                ->whereIn('kyc_status', ['unverified', 'pending', 'rejected'])
                ->count();

            $users->each(function ($user) {
                $user->profile_photo = $user->getMedia('profile_photo')
                    ->map(function ($media) {
                        return $media->getUrl();
                    });
            });

            return response()->json([
                'success' => true,
                'data' => $users,
                'memberCounts' => $memberCounts,
                'verifiedUser' => $verifiedUser,
                'unverifiedUser' => $unverifiedUser,
            ]);
        }

        return response()->json(['success' => false, 'data' => []]);
    }

    public function getPendingKyc()
    {
        Gate::authorize('access-kyc', User::class);

        return Inertia::render('Member/Listing/KycPending');
    }

    public function getPendingKycData(Request $request)
    {
        Gate::authorize('access-kyc', User::class);

        if ($request->has('lazyEvent')) {
            $data = json_decode($request->only(['lazyEvent'])['lazyEvent'], true);

            $query = User::query()
                ->with([
                    'country:id,name,iso2,translations',
                    'rank:id,rank_name',
                    'upline:id,name,email,upline_id',
                    'media'
                ])
                ->where('kyc_status', 'pending');

            //global filter
            if ($data['filters']['global']['value']) {
                $query->where(function ($q) use ($data) {
                    $keyword = $data['filters']['global']['value'];

                    $q->where('name', 'like', '%' . $keyword . '%')
                        ->orWhere('email', 'like', '%' . $keyword . '%')
                        ->orWhere('username', 'like', '%' . $keyword . '%')
                        ->orWhere('id_number', 'like', '%' . $keyword . '%');
                });
            }

            //date filter
            if (!empty($data['filters']['start_date']['value']) && !empty($data['filters']['end_date']['value'])) {
                $start_date = Carbon::parse($data['filters']['start_date']['value'])->addDay()->startOfDay();
                $end_date = Carbon::parse($data['filters']['end_date']['value'])->addDay()->endOfDay();

                $query->whereBetween('kyc_requested_at', [$start_date, $end_date]);
            }

            //country filter
            if ($data['filters']['country']['value']) {
                $query->where('country_id', $data['filters']['country']['value']);
            }

            //rank filter
            if ($data['filters']['rank']['value']) {
                $query->where('setting_rank_id', $data['filters']['rank']['value']);
            }

            if ($data['sortField'] && $data['sortOrder']) {
                $order = $data['sortOrder'] == 1 ? 'asc' : 'desc';
                $query->orderBy($data['sortField'], $order);
            } else {
                $query->orderByDesc('kyc_requested_at');
            }

            $users = $query->paginate($data['rows']);

            $pendingKycCounts = (clone $query)
                ->count();

            $users->each(function ($user) {
                $user->front_identity = $user->getFirstMediaUrl('front_identity');
                $user->back_identity = $user->getFirstMediaUrl('back_identity');
            });

            return response()->json([
                'success' => true,
                'data' => $users,
                'pendingKycCounts' => $pendingKycCounts,
            ]);
        }

        return response()->json(['success' => false, 'data' => []]);
    }

    public function kycPendingApproval(Request $request)
    {
        Gate::authorize('manage-kyc', User::class);

        Validator::make($request->all(), [
            'action' => ['required'],
        ])->setAttributeNames([
            'action' => trans('public.action'),
        ])->validate();

        $user = User::find($request->user_id);

        if ($request->action == 'approve') {
            $user->kyc_status = 'verified';
            $user->kyc_approval_at = now();
        } else {
            if (!$request->remarks) {
                throw ValidationException::withMessages(['remarks' => trans('public.remarks_required_reject')]);
            }

            $user->kyc_status = 'rejected';
            $user->kyc_approval_description = $request->remarks;
        }
        $user->update();

        return back()->with('toast', 'success');
    }

    public function addNewMember(Request $request)
    {
        Gate::authorize('create', User::class);

        $validatedData = $request->validate([
            'name' => ['required', 'regex:/^[\p{L}\p{N}\p{M}. @]+$/u', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:' . User::class],
            'username' => ['required'],
            'country' => ['required'],
            'dial_code' => ['required'],
            'phone' => ['required', 'max:255', 'unique:' . User::class],
            'password' => ['required', Password::defaults(), 'confirmed'],
            'upline' => ['nullable'],
            'identity_number' => ['required', 'unique:' . User::class],
        ], [], [
            'name' => trans('public.name'),
            'email' => trans('public.email'),
            'username' => trans('public.username'),
            'country' => trans('public.country'),
            'dial_code' => trans('public.dial_code'),
            'phone' => trans('public.phone'),
            'password' => trans('public.password'),
            'upline' => trans('public.upline'),
            'identity_number' => trans('public.identity_number'),
        ]);

        $dial_code = $request->dial_code;
        $country = Country::find($request->country['id']);

        $user = new User();
        $user->name = $validatedData['name'];
        $user->email = $validatedData['email'];
        $user->username = $validatedData['username'];
        $user->dial_code = $dial_code['phone_code'];
        $user->phone = $validatedData['phone'];
        $user->phone_number = $request->phone_number;
        $user->country_id = $country->id;
        $user->nationality = $country->nationality;
        $user->password = Hash::make($validatedData['password']);
        $user->identity_number = $request->identity_number;

        if ($request->upline) {
            $upline_id = $request->upline['id'];
            $upline = User::find($upline_id);

            if (empty($upline->hierarchyList)) {
                $hierarchyList = "-" . $upline_id . "-";
            } else {
                $hierarchyList = $upline->hierarchyList . $upline_id . "-";
            }

            $user->upline_id = $upline_id;
            $user->hierarchyList = $hierarchyList;
        }

        $user->setReferralId();

        $id_no = 'MID' . Str::padLeft($user->id - 1, 6, "0");
        $user->id_number = $id_no;
        $user->save();

        $wallet = new Wallet();
        $wallet->user_id = $user->id;
        $wallet->type = 'cash_wallet';
        $wallet->address = RunningNumberService::getID('cash_wallet');
        $wallet->currency = 'USD';
        $wallet->currency_symbol = '$';
        $wallet->save();

        $wallet = new Wallet();
        $wallet->user_id = $user->id;
        $wallet->type = 'bonus_wallet';
        $wallet->address = RunningNumberService::getID('bonus_wallet');
        $wallet->currency = 'USD';
        $wallet->currency_symbol = '$';
        $wallet->save();

        return back()->with('toast', 'success');
    }

    public function memberDetail($id_number)
    {
        $user = User::where('id_number', $id_number)
            ->with([
                'country:id,name,emoji,iso2,translations',
                'upline:id,name,email,upline_id',
                'rank:id,rank_name',
            ])
            ->withCount('wallets')
            ->first();

        $profile_photo = $user->getFirstMediaUrl('profile_photo');

        $kycImages = $user->getMedia('kyc_image')->map(fn($image) => $image->getUrl());

        $refereeCount = User::where('upline_id', $user->id)->count();

        return Inertia::render('Member/Listing/Detail/MemberDetail', [
            'user' => $user,
            'refereeCount' => $refereeCount,
            'kycImages' => $kycImages,
            'profile_photo' => $profile_photo,
        ]);
    }

    public function updateMemberProfile(Request $request, $id)
    {
        Gate::authorize('update', User::class);

        $validatedData = $request->validate([
            'name' => ['required', 'regex:/^[a-zA-Z0-9\p{Han}. ]+$/u', 'max:255'],
            'username' => ['required'],
            'country' => ['required'],
            'dial_code' => ['required'],
            'phone' => ['required', 'max:255'],
            'phone_number' => ['required', 'max:255', Rule::unique('users', 'phone_number')->ignore($id),],
        ], [], [
            'name' => trans('public.name'),
            'username' => trans('public.username'),
            'dial_code' => trans('public.dial_code'),
            'phone' => trans('public.phone'),
        ]);

        $user = User::find($id);
        $dial_code = $request->dial_code;
        $country = Country::find($request->country['id']);

        $user->name = $validatedData['name'];
        $user->username = $validatedData['username'];
        $user->dial_code = $dial_code['phone_code'];
        $user->phone = $validatedData['phone'];
        $user->phone_number = $request->phone_number;
        $user->country_id = $country->id;
        $user->nationality = $country->nationality;

        $user->update();
    }

    public function deleteMember(Request $request)
    {
        Gate::authorize('delete', User::class);

        $user = User::find($request->id);
        $user->delete_at = now();
        $user->status = 'inactive';
        $user->update();

        return back()->with('toast', 'success');
    }

    public function getWalletData($id_number)
    {
        $user = User::where('id_number', $id_number)
            ->with('wallets')
            ->first();

        $wallets = $user->wallets; //wallets from User.php relationship
        return response()->json($wallets);
    }

    public function walletAdjustment(Request $request)
    {
        $validatedData = $request->validate([
            'action' => 'required',
            'fund_type' => 'required',
            'amount' => 'required|numeric',
            'remarks' => 'nullable',
        ], [], [
            'amount' => trans('public.amount'),
            'fund_type' => trans('public.fund_type'),
            'action' => trans('public.action'),
        ]);

        $wallet = Wallet::find($request->wallet_id);

        //validate insufficient amount for demo and real fund seperately
        if ($validatedData['fund_type'] == 'demo_fund') {
            if ($validatedData['action'] == 'withdrawal' && $wallet->demo_fund < $validatedData['amount']) {
                throw ValidationException::withMessages(['amount' => trans('Insufficient Demo Fund Balance')]);
            }
        } else {
            if ($validatedData['action'] == 'withdrawal' && $wallet->balance < $validatedData['amount']) {
                throw ValidationException::withMessages(['amount' => trans('Insufficient Balance')]);
            }
        }

        //seperate demo and real fund amount during transaction
        $new_demo_fund = $validatedData['action'] == 'withdrawal' ? $wallet->demo_fund - $validatedData['amount'] : $wallet->demo_fund + $validatedData['amount'];
        $new_real_fund = $validatedData['action'] == 'withdrawal' ? $wallet->real_fund - $validatedData['amount'] : $wallet->real_fund + $validatedData['amount'];

        if ($validatedData['fund_type'] == 'demo_fund') {
            $wallet->demo_fund = $new_demo_fund;
        } else {
            $wallet->real_fund = $new_real_fund;
        }

        //calculate balance after withdrawal or deposit (demo + real)
        $new_balance = $validatedData['action'] == 'withdrawal' ? $wallet->balance - $validatedData['amount'] : $wallet->balance + $validatedData['amount'];

        //create transaction
        $transaction = new Transaction();
        $transaction->user_id = $wallet->user_id;
        $transaction->category = $wallet->type;
        $transaction->transaction_type = $validatedData['action'];
        $transaction->from_wallet_id = $validatedData['action'] == 'withdrawal' ? $wallet->id : null;
        $transaction->to_wallet_id = $validatedData['action'] == 'deposit' ? $wallet->id : null;
        $transaction->transaction_number = RunningNumberService::getID('transaction');
        $transaction->amount = $validatedData['amount'];
        $transaction->from_currency = $wallet->currency;
        $transaction->to_currency = $wallet->currency;
        $transaction->transaction_amount = $validatedData['amount'];
        $transaction->fund_type = $validatedData['fund_type'];
        $transaction->old_wallet_amount = $wallet->balance;
        $transaction->new_wallet_amount = $new_balance;
        $transaction->status = 'success';
        $transaction->remarks = $validatedData['remarks'];
        $transaction->approval_at = now();
        $transaction->handle_by = Auth::id();

        $transaction->save();

        //update wallet balance
        $wallet->balance = $new_balance;
        $wallet->update();

        return back()->with('toast', 'success');
    }

    public function upgradeRank(Request $request)
    {
        $user = User::find($request->user_id);
        $user->setting_rank_id = $request->rank['id'];
        $user->rank_up_status = 'manual';
        $user->update();

        return back()->with('toast', 'success');
    }

    public function changeUpline(Request $request)
    {
        $user = User::find($request->user_id);

        if ($request->upline) {
            $upline_id = $request->upline['id'];
            $upline = User::find($upline_id);

            // Build the new hierarchy list for the user based on the new upline
            $newHierarchyList = empty($upline->hierarchyList) ? "-$upline_id-" : $upline->hierarchyList . "$upline_id-";

            // Update the user's upline and hierarchy list
            $user->upline_id = $upline_id;
            $user->hierarchyList = $newHierarchyList;
            $user->save();

            // Update all downLines' hierarchy lists recursively
            $this->updateDownLineHierarchy($user->id, $newHierarchyList);
        }

        return back()->with('toast', 'success');
    }

    private function updateDownLineHierarchy($userId, $parentHierarchy)
    {
        // Get all direct downLines of the user
        $downLines = User::where('upline_id', $userId)->get();

        foreach ($downLines as $downLine) {
            // Build the new hierarchy list for the downLine
            $newHierarchyList = $parentHierarchy . "$downLine->upline_id-";
            $downLine->hierarchyList = $newHierarchyList;
            $downLine->save();

            // Recursively update the downLines of the current downLine
            $this->updateDownLineHierarchy($downLine->id, $newHierarchyList);
        }
    }

    public function access_portal(User $user)
    {
        $dataToHash = $user->name . $user->email . $user->id_number;
        $hashedToken = md5($dataToHash);

        $currentHost = $_SERVER['HTTP_HOST'];

        // Retrieve the app URL and parse its host
        $appUrl = parse_url(config('app.url'), PHP_URL_HOST);
        $memberProductionUrl = config('app.member_production_url');

        if ($currentHost === 'assm-admin.currenttech.pro') {
            $url = "https://assm-user.currenttech.pro/admin_login/$hashedToken";
        } elseif ($currentHost === $appUrl) {
            $url = "$memberProductionUrl/admin_login/$hashedToken";
        } else {
            return back();
        }

        $params = [
            'admin_id' => Auth::id(),
            'admin_name' => Auth::user()->name,
        ];

        $redirectUrl = $url . "?" . http_build_query($params);
        return Inertia::location($redirectUrl);
    }
}
