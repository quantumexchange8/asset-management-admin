<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\Country;
use App\Models\User;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): Response
    {
        return Inertia::render('Profile/Edit', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => session('status'),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();

        Validator::make($request->all(), [
            'name' => ['required', 'regex:/^[\p{L}\p{N}\p{M}. @]+$/u', 'max:255'],
            'username' => ['required'],
            'country_id' => ['required'],
            'dial_code' => ['required'],
            'phone' => ['required', 'max:255'],
            'phone_number' => ['required', 'max:255', Rule::unique('users', 'phone_number')->ignore($user->id),],
            'identity_number' => ['nullable', 'string', 'max:30'],
        ])->setAttributeNames([
            'name' => trans('public.name'),
            'username' => trans('public.username'),
            'country_id' => trans('public.country'),
            'dial_code' => trans('public.dial_code'),
            'phone' => trans('public.phone'),
            'identity_number' => trans('public.identity_number'),
        ])->validate();

        $country = Country::find($request->country_id['id']);
        $user->update([
            'name' => $request->name,
            'username' => $request->username,
            'country_id' => $country->id,
            'dial_code' => $request->dial_code,
            'phone' => $request->phone,
            'phone_number' => $request->phone_number,
            'identity_number' => $request->identity_number,
        ]);

        if ($request->hasFile('profile_photo')) {
            $user->clearMediaCollection('profile_photo');
            $user->addMedia($request->profile_photo)->toMediaCollection('profile_photo');
        }

        if ($request->profile_action == 'remove') {
            $user->clearMediaCollection('profile_photo');
        }

        return back()->with('toast', 'success');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
