<?php

namespace App\Http\Middleware;

use App\Services\SidebarService;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $sidebarService = new SidebarService();
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user(),
                'profile_photo' => $request->user() ? $request->user()->getFirstMediaUrl('profile_photo') : null,
            ],
            'toast' => session('toast'),
            'permissions' => $request->user() ? $request->user()->getAllPermissions()->pluck('name')->toArray() : 'no permission',
            'getPendingKycCount' => $sidebarService->getPendingKycCount(),
            'getPendingDepositCount' => $sidebarService->getPendingDepositCount(),
            'getPendingAccountCount' => $sidebarService->getPendingAccountCount(),
            'pendingWithdrawalCounts' => $sidebarService->pendingWithdrawalCounts(),
        ];
    }
}
