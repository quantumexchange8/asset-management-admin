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
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
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
                ->with([
                    'permissions:id,name,type,category'
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

    public function updateAdminInfo(Request $request)
    {
        Validator::make($request->all(), [
            'name' => ['required', 'regex:/^[\p{L}\p{N}\p{M}. @]+$/u', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($request->user_id)],
            'role' => ['required'],
        ])->setAttributeNames([
            'name' => trans('public.name'),
            'email' => trans('public.email'),
            'role' => trans('public.role'),
        ])->validate();

        $user = User::find($request->user_id);
        $user->name = $request->name;
        $user->email = $request->email;
        $user->role = $request->role;
        $user->update();
        // Sync role (replaces old role with the new one)
        $user->syncRoles([$request->role]);
        // Sync permissions (adds new and removes unselected)
        if ($request->has('permissions') && is_array($request->permissions)) {
            $permissions = Permission::whereIn('id', $request->permissions)
                ->get()
                ->pluck('name')
                ->toArray();

            $user->syncPermissions($permissions);
        }

        return back()->with('toast', 'success');
    }

    public function deleteAdmin(Request $request)
    {
        $admin = User::find($request->id);
        $admin->delete_at = now();
        $admin->status = 'inactive';
        $admin->syncPermissions([]);
        $admin->update();

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
                $query->where(function ($q) use ($data) {
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

        $validator =  Validator::make($request->all(), [
            'name' => ['required'],
            'account_number' => ['required'],
            'currency' => ['required'],
        ])->setAttributeNames([
            'name' => trans('public.name'),
            'account_number' => trans('public.account_number'),
            'currency' => trans('public.currency'),
        ]);

        // Collect errors from initial validation
        $errors = $validator->errors()->messages();

        // Add additional validation for crypto type
        if ($request->type === 'crypto') {
            if (!$request->crypto_tether) {
                $errors['crypto_tether'][] = trans('public.crypto_tether_required');
            }

            if (!$request->crypto_network) {
                $errors['crypto_network'][] = trans('public.crypto_network_required');
            }
        } else {
            if (!$request->bank_name) {
                $errors['bank_name'][] = trans('public.bank_name_required');
            }

            if (!$request->bank_branch) {
                $errors['bank_branch'][] = trans('public.bank_branch_required');
            }
        }

        // If there are any errors, throw a ValidationException
        if (!empty($errors)) {
            throw ValidationException::withMessages($errors);
        }

        $currency = $request->currency;

        $depositProfile = new DepositProfile();
        $depositProfile->name = $request->name;
        $depositProfile->type = $request->type;
        $depositProfile->account_number = $request->account_number;
        $depositProfile->bank_name = $request->bank_name;
        $depositProfile->bank_branch = $request->bank_branch;
        $depositProfile->crypto_tether = $request->crypto_tether;
        $depositProfile->crypto_network = $request->crypto_network ?? null;
        $depositProfile->country_id = $request->type === 'bank' ? $currency['id'] : null;
        $depositProfile->currency = $request->type === 'bank' ? $currency['currency'] : $currency;
        $depositProfile->edited_by = Auth::id();
        $depositProfile->save();

        return back()->with('toast', 'success');
    }

    public function updateDepositProfile(Request $request)
    {
        $depositProfile = DepositProfile::find($request->id);

        $validator =  Validator::make($request->all(), [
            'name' => ['required'],
            'account_number' => ['required'],
            'currency' => ['required'],
        ])->setAttributeNames([
            'name' => trans('public.name'),
            'account_number' => trans('public.account_number'),
            'currency' => trans('public.currency'),
        ]);

        // Collect errors from initial validation
        $errors = $validator->errors()->messages();

        // Add additional validation for crypto type
        if ($request->type === 'crypto') {
            if (!$request->crypto_tether) {
                $errors['crypto_tether'][] = trans('public.crypto_tether_required');
            }

            if (!$request->crypto_network) {
                $errors['crypto_network'][] = trans('public.crypto_network_required');
            }
        } else {
            if (!$request->bank_name) {
                $errors['bank_name'][] = trans('public.bank_name_required');
            }

            if (!$request->bank_branch) {
                $errors['bank_branch'][] = trans('public.bank_branch_required');
            }
        }   

        // If there are any errors, throw a ValidationException
        if (!empty($errors)) {
            throw ValidationException::withMessages($errors);
        }

        $currency = $request->currency;

        $depositProfile->name = $request->name;
        $depositProfile->type = $request->type;
        $depositProfile->account_number = $request->account_number;
        $depositProfile->bank_name = $request->bank_name;
        $depositProfile->bank_branch = $request->bank_branch;
        $depositProfile->crypto_tether = $request->crypto_tether;
        $depositProfile->crypto_network = $request->crypto_network ?? null;
        $depositProfile->country_id = $request->type === 'bank' ? $currency['id'] : null;
        $depositProfile->currency = $request->type === 'bank' ? $currency['currency'] : $currency;
        $depositProfile->edited_by = Auth::id();

        $depositProfile->update();

        return back()->with('toast', 'success');
    }

    public function updateDepositProfileStatus(Request $request)
    {
        $depositProfile = DepositProfile::find($request->id);

        $depositProfile->status = $depositProfile->status == 'active' ? 'inactive' : 'active';
        $depositProfile->update();

        return back()->with('toast', 'success');
    }

    public function deleteDepositProfile(Request $request)
    {
        $depositProfile = DepositProfile::find($request->id);

        $depositProfile->deleted_at = now();
        $depositProfile->update();

        return back()->with('toast', 'success');
    }
}
