<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $q = trim((string) $request->get('q', ''));
        $role = $request->get('role', '');

        $users = User::query()
            ->withCount('activityLogs')
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($q2) use ($q) {
                    $q2->where('name', 'like', "%{$q}%")
                        ->orWhere('email', 'like', "%{$q}%");
                });
            })
            ->when($role !== '', function ($query) use ($role) {
                $query->where('role', $role);
            })
            ->latest('created_at')
            ->paginate(15)
            ->withQueryString();

        $roles = User::distinct()->pluck('role')->filter()->sort()->values();

        return view('admin.users.index', compact('users', 'q', 'role', 'roles'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', Password::defaults()],
        ]);

        return DB::transaction(function () use ($validated) {
            $validated['password'] = Hash::make($validated['password']);

            $user = User::create($validated);

            ActivityLog::create([
                'user_id' => auth()->id(),
                'aktivitas' => 'create_user',
                'waktu_aktivitas' => now(),
                'context' => [
                    'user_id' => $user->id,
                ],
            ]);

            return redirect()
                ->route('admin.users.index')
                ->with('status', 'User berhasil dibuat.');
        });
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', Password::defaults()],
        ]);

        return DB::transaction(function () use ($user, $validated) {
            if (!empty($validated['password'])) {
                $validated['password'] = Hash::make($validated['password']);
            } else {
                unset($validated['password']);
            }

            $user->update($validated);

            ActivityLog::create([
                'user_id' => auth()->id(),
                'aktivitas' => 'update_user',
                'waktu_aktivitas' => now(),
                'context' => [
                    'user_id' => $user->id,
                ],
            ]);

            return redirect()
                ->route('admin.users.index')
                ->with('status', 'User berhasil diperbarui.');
        });
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        // Prevent deleting own account
        if ($user->id === auth()->id()) {
            return redirect()
                ->route('admin.users.index')
                ->withErrors(['error' => 'Tidak dapat menghapus akun sendiri.']);
        }

        return DB::transaction(function () use ($user) {
            $userId = $user->id;

            $user->delete();

            ActivityLog::create([
                'user_id' => auth()->id(),
                'aktivitas' => 'delete_user',
                'waktu_aktivitas' => now(),
                'context' => [
                    'user_id' => $userId,
                ],
            ]);

            return redirect()
                ->route('admin.users.index')
                ->with('status', 'User berhasil dihapus.');
        });
    }
}
