<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Str;
use Inertia\Inertia;
use App\Services\RunningNumberService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class MemberController extends Controller
{
    public function getMemberList()
    {
        $user = User::with([
            'country:id,name,emoji',
            'rank:id,rank_name',
            'upline:id,name,email,upline_id',
        ])->get();

        $userCount = User::count();

        return Inertia::render('Member/Listing/MemberListing', [
            'user' => $user,
            'userCount' => $userCount,
        ]);
    }

    public function addNewMember(Request $request)
    {
        $validatedData = $request->validate([
            'name' => ['required', 'regex:/^[a-zA-Z0-9\p{Han}. ]+$/u', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:' . User::class],
            'username' => ['required'],
            'country' => ['required'],
            'dial_code' => ['required'],
            'phone' => ['required', 'max:255', 'unique:' . User::class],
            'password' => ['required', Password::defaults(), 'confirmed'],
            'upline' => ['nullable'],
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

        $id_no = 'LID' . Str::padLeft($user->id - 1, 6, "0");
        $user->id_number = $id_no;

        $user->save();

        $wallet = new Wallet();
        $wallet->user_id = $user->id;
        $wallet->type = 'cash_wallet';
        $wallet->address = RunningNumberService::getID('cash_wallet');
        $wallet->currency = 'CNY';
        $wallet->currency_symbol = '¥';
        $wallet->save();

        $wallet = new Wallet();
        $wallet->user_id = $user->id;
        $wallet->type = 'bonus_wallet';
        $wallet->address = RunningNumberService::getID('bonus_wallet');
        $wallet->currency = 'CNY';
        $wallet->currency_symbol = '¥';

        $wallet->save();
    }

    public function memberDetail($id_number)
    {
        $user = User::where('id_number', $id_number)
            ->with([
                'country:id,name,emoji',
                'upline:id,name,email,upline_id',
                'rank:id,rank_name',

            ])
            ->withCount('wallets')
            ->first();

        return Inertia::render('Member/Listing/Detail/MemberDetail', [
            'user' => $user,
        ]);
    }

    public function getWalletData($id_number)
    {
        $user = User::where('id_number', $id_number)
            ->with('wallets')
            ->first();

        $wallets = $user->wallets; //wallets from User.php relationship
        return response()->json($wallets);
    }

    public function updateMemberProfile(Request $request, $id)
    {
        $validatedData = $request->validate([
            'name' => ['required', 'regex:/^[a-zA-Z0-9\p{Han}. ]+$/u', 'max:255'],
            'username' => ['required'],
            'country' => ['required'],
            'dial_code' => ['required'],
            'phone' => ['required', 'max:255'],
            'phone_number' => ['required', 'max:255', Rule::unique('users', 'phone_number')->ignore($id),],
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
        return back()->with('toast', [
            'title' => trans("public.success"),
            'message' => trans("public.toast_create_customer_success_message"),
            'type' => 'success',
        ]);
    }

    public function walletAdjustment(Request $request)
    {
        $validatedData = $request->validate([
            'action' => 'required',
            'fund_type' => 'required',
            'amount' => 'required|numeric',
            'remarks' => 'nullable',
        ]);

        $wallet = Wallet::find($request->wallet_id);

        //validate insufficient amount for demo and real fund seperately
        if ($validatedData['fund_type'] == 'demo_fund') {
            if ($validatedData['action'] == 'withdrawal' && $wallet->demo_fund < $validatedData['amount']) {
                throw ValidationException::withMessages(['amount' => trans('Insufficient Demo Fund Balance')]);
            }
        } else {
            if ($validatedData['action'] == 'withdrawal' && $wallet->real_fund < $validatedData['amount']) {
                throw ValidationException::withMessages(['amount' => trans('Insufficient Real Fund Balance')]);
            }
        }

        // if ($validatedData['action'] == 'withdrawal' && $wallet->balance < $validatedData['amount']) {
        //     throw ValidationException::withMessages(['amount' => trans('Insufficient Balance')]);
        // }

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
    }

    public function upgradeRank(Request $request){
        $user = User::find($request->user_id);
        $user->setting_rank_id = $request->rank['id'];
        $user->update();
    }
}
