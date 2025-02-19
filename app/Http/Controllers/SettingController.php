<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Spatie\Permission\Models\Permission;

class SettingController extends Controller
{
    public function admin_listing()
    {
        $permissions = Permission::get()
            ->groupBy(['category', 'type']);

        return Inertia::render('Settings/Admin/AdminListing', [
            'permissions' => $permissions
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
                $query->orderByDesc('kyc_requested_at');
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
        dd($request->all());
    }
}
