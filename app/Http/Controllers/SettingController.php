<?php

namespace App\Http\Controllers;

use App\Models\User;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
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

            // Filter role
            if ($data['filters']['rank']['value']) {
                $query->where('setting_rank_id', $data['filters']['rank']['value']);
            }

            if ($data['sortField'] && $data['sortOrder']) {
                $order = $data['sortOrder'] == 1 ? 'asc' : 'desc';
                $query->orderBy($data['sortField'], $order);
            } else {
                $query->orderByRaw("FIELD(role, 'super_admin') DESC")
                    ->orderByDesc('created_at');
            }

            $users = $query->paginate($data['rows']);

            return response()->json([
                'success' => true,
                'data' => $users,
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
}
