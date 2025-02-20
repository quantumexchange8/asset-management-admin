<?php

namespace App\Http\Controllers;

use App\Models\DepositProfile;
use App\Models\User;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Spatie\Permission\Models\Permission;

class SettingController extends Controller
{
    public function admin_listing()
    {
        $permissions = Permission::get()
            ->groupBy(['category', 'type']);

        return Inertia::render('Settings/Admin/AdminListing', [
            'permissions' => $permissions,
            'permissionsCount' => Permission::count()
        ]);
    }

    public function getAdminListingData(Request $request)
    {
        if ($request->has('lazyEvent')) {
            $data = json_decode($request->only(['lazyEvent'])['lazyEvent'], true);

            $query = User::role([
                'super_admin',
                'admin',
            ])
                ->withCount('permissions');

            // Filter keyword
            if ($data['filters']['global']['value']) {
                $query->where(function ($q) use ($data) {
                    $keyword = $data['filters']['global']['value'];

                    $q->where('name', 'like', '%' . $keyword . '%')
                        ->orWhere('email', 'like', '%' . $keyword . '%')
                        ->orWhere('username', 'like', '%' . $keyword . '%')
                        ->orWhere('id_number', 'like', '%' . $keyword . '%');
                });
            }

            if ($data['sortField'] && $data['sortOrder']) {
                $order = $data['sortOrder'] == 1 ? 'asc' : 'desc';
                $query->orderBy($data['sortField'], $order);
            } else {
                $query->orderByRaw("FIELD(role, 'super_admin') DESC")
                    ->orderByDesc('created_at');
            }

            $users = $query->paginate($data['rows']);

            $adminCounts = (clone $query)
                ->count();

            return response()->json([
                'success' => true,
                'data' => $users,
                'adminCounts' => $adminCounts,
            ]);
        }

        return response()->json(['success' => false, 'data' => []]);
    }

    public function addAdmin(Request $request)
    {
        Validator::make($request->all(), [
            'name' => ['required', 'regex:/^[\p{L}\p{N}\p{M}. @]+$/u', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', Password::defaults()],
            'role' => ['required'],
        ])->setAttributeNames([
            'name' => trans('public.name'),
            'email' => trans('public.email'),
            'password' => trans('public.password'),
            'role' => trans('public.role'),
        ])->validate();

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        if ($request->has('permissions')) {
            $user->syncRoles($request->role);

            $permissions = Permission::whereIn('id', $request->permissions)
                ->get()
                ->pluck('name')
                ->toArray();

            $user->givePermissionTo($permissions);
        }

        return back()->with('toast', 'success');
    }

    public function depositProfile()
    {
        return Inertia::render('Settings/DepositProfile/DepositProfile');
    }

    public function getDepositProfileData(Request $request)
    {
        if ($request->has('lazyEvent')) {
            $data = json_decode($request->only(['lazyEvent'])['lazyEvent'], true);

            $query = DepositProfile::query();

            if ($data['filters']['global']['value']) {
                $query->where(function ($q) use ($data) { //function() allow to add more condition' use ($data) means $data is passed into the clause to be use
                    $keyword = $data['filters']['global']['value'];

                    $q->where('name', 'like', '%' . $keyword . '%');
                });
            }

            if ($data['filters']['type']['value']) {
                $query->where('type', $data['filters']['type']['value']);
            }

            if ($data['sortField'] && $data['sortOrder']) {
                $order = $data['sortOrder'] == 1 ? 'asc' : 'desc';
                $query->orderBy($data['sortField'], $order);
            } else {
                $query->latest();
            }

            $depositProfile = $query->paginate($data['rows']);

            $depositProfileCounts = (clone $query)
                ->count();

            return response()->json([
                'success' => true,
                'data' => $depositProfile,
                'depositProfileCounts' => $depositProfileCounts,
            ]);
        }

        return response()->json(['success' => false, 'data' => []]);
    }

    public function addDepositProfile(Request $request)
    {
        $validatedData = $request->validate([
            'name' => ['required'],
            'account_number' => ['required'],
            'bank_name' => ['required_if:type,bank'],
            'bank_branch' => ['required_if:type,bank'],
            'crypto_tether' => ['required_if:type,crypto'],
            'crypto_network' => ['required_if:type,crypto'],
            'currency' => ['required'],
        ], [
            'bank_name.required_if' => trans('validation.required_if', [
                'attribute' => trans('public.bank_name'),
                'other' => trans('public.type'),
                'value' => trans('public.bank')
            ]),

            'bank_branch.required_if' => trans('validation.required_if', [
                'attribute' => trans('public.bank_branch'),
                'other' => trans('public.type'),
                'value' => trans('public.bank')
            ]),

            'crypto_tether.required_if' => trans('validation.required_if', [
                'attribute' => trans('public.crypto_tether'),
                'other' => trans('public.type'),
                'value' => trans('public.crypto')
            ]),

            'crypto_network.required_if' => trans('validation.required_if', [
                'attribute' => trans('public.crypto_network'),
                'other' => trans('public.type'),
                'value' => trans('public.crypto')
            ]),

        ], [
            'name' => trans('public.name'),
            'account_number' => trans('public.account_number'),
            'currency' => trans('public.currency'),
        ]);

        $currency = $request->currency;

        $depositProfile = new DepositProfile();
        $depositProfile->name = $validatedData['name'];
        $depositProfile->type = $request->type;
        $depositProfile->account_number = $validatedData['account_number'];
        $depositProfile->bank_name = $validatedData['bank_name'];
        $depositProfile->bank_branch = $validatedData['bank_branch'];
        $depositProfile->crypto_tether = $validatedData['crypto_tether'];
        $depositProfile->crypto_network = $validatedData['crypto_network'] ?? null;
        $depositProfile->country_id = $request->type === 'bank' ? $currency['id'] : null;
        $depositProfile->currency = $request->type === 'bank' ? $currency['currency'] : $currency;
        $depositProfile->edited_by = Auth::id();

        $depositProfile->save();

        return redirect()->back()->with('toast');
    }

    public function updateDepositProfileStatus(Request $request)
    {
        $depositProfile = DepositProfile::find($request->id);

        $depositProfile->status = $depositProfile->status == 'active' ? 'inactive' : 'active';
        $depositProfile->update();
    }
}
