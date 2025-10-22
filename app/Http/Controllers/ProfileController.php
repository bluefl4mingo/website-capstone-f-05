<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user   = $request->user();
        $action = $request->input('action');

        // 1) Update basic info
        if ($action === 'profile') {
            $validated = $request->validate([
                'name'  => ['required','string','max:255'],
                'email' => ['required','email','max:255', Rule::unique('users','email')->ignore($user->id)],
            ]);

            $user->fill($validated);

            // If email changed, re-verify
            if ($user->isDirty('email')) {
                $user->email_verified_at = null;
            }

            $user->save();

            return Redirect::route('profile.edit')->with('status', 'Profile berhasil diperbarui.');
        }

        // 2) Update password
        if ($action === 'password') {
            $validated = $request->validate([
                'current_password' => ['required'],
                'password' => ['required', 'confirmed', PasswordRule::defaults()],
            ]);

            if (! Hash::check($validated['current_password'], $user->password)) {
                return Redirect::back()->withErrors([
                    'current_password' => 'The current password is incorrect.',
                ]);
            }

            $user->forceFill([
                'password' => Hash::make($validated['password']),
            ])->save();

            return Redirect::route('profile.edit')->with('status', 'Password updated.');
        }

        // Fallback to original behavior (basic info via FormRequest)
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'Profile updated.');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
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