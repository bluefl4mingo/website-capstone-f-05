@extends('layouts.admin')

@section('title', 'Users')
@section('page-title', 'Users')

@section('content')
<div x-data="{ openCreate: false, openEdit: false, editUser: null }" class="space-y-4">

    {{-- Top bar: title, search, filters, create --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
        <div class="flex-1">
            <h1 class="text-xl font-semibold">Users</h1>
            <p class="text-sm text-gray-500">Kelola pengguna admin sistem.</p>
        </div>

        <div class="flex flex-col md:flex-row gap-2 md:items-center">
            <form method="GET" class="flex items-center gap-2">
                <input type="text" name="q" value="{{ $q }}" placeholder="Cari nama / username / email…" class="w-64 rounded-lg border-gray-200" />
                <select name="role" class="rounded-lg border-gray-200">
                    <option value="">Semua Role</option>
                    @foreach($roles as $r)
                        <option value="{{ $r }}" @selected($role === $r)>{{ ucfirst($r) }}</option>
                    @endforeach
                </select>
                <button type="submit" class="px-3 py-2 rounded-lg border hover:bg-gray-50">Filter</button>
            </form>

            <button @click="openCreate = true"
                    class="inline-flex items-center rounded-full bg-aqua text-white px-4 py-2 hover:opacity-90">
                + User Baru
            </button>
        </div>
    </div>

    {{-- Table --}}
    <div class="rounded-2xl bg-white ring-1 ring-black/5 overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50 text-xs uppercase tracking-wide text-gray-600">
            <tr>
                <th class="text-left px-4 py-3">Nama</th>
                <th class="text-left px-4 py-3">Username</th>
                <th class="text-left px-4 py-3">Email</th>
                <th class="text-left px-4 py-3">Role</th>
                <th class="text-right px-4 py-3">Activity Logs</th>
                <th class="text-left px-4 py-3">Dibuat</th>
                <th class="text-right px-4 py-3">Aksi</th>
            </tr>
            </thead>
            <tbody class="divide-y">
            @forelse ($users as $user)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3">
                        <div class="font-medium">{{ $user->name }}</div>
                    </td>
                    <td class="px-4 py-3">{{ $user->username }}</td>
                    <td class="px-4 py-3">{{ $user->email }}</td>
                    <td class="px-4 py-3">
                        <span class="inline-flex items-center px-2 py-1 rounded text-xs bg-blue-50 text-blue-700">
                            {{ ucfirst($user->role ?? 'user') }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-right">{{ $user->activity_logs_count }}</td>
                    <td class="px-4 py-3">{{ $user->created_at->format('d M Y') }}</td>
                    <td class="px-4 py-3">
                        <div class="flex justify-end gap-2">
                            <button type="button" class="px-3 py-1.5 rounded-lg border text-sm hover:bg-gray-50"
                                    @click="editUser = {{ $user->id }}; openEdit = true">
                                Edit
                            </button>
                            @if($user->id !== auth()->id())
                                <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="inline"
                                      onsubmit="return confirm('Hapus user ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-3 py-1.5 rounded-lg bg-rose-50 text-rose-700 text-sm hover:bg-rose-100">
                                        Hapus
                                    </button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-4 py-10 text-center text-gray-500">
                        Belum ada data user.
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    <div class="mt-4">
        {{ $users->links() }}
    </div>

    {{-- Create Modal --}}
    <template x-teleport="body">
    <div
        x-show="openCreate"
        x-transition.opacity
        @keydown.window.escape="openCreate = false"
        class="fixed inset-0 z-50 flex items-start justify-center"
        x-cloak
    >
        <div class="absolute inset-0 bg-black/40" @click="openCreate = false"></div>

        <div
        x-show="openCreate"
        x-transition
        class="relative mt-20 w-full max-w-xl rounded-2xl bg-white p-6 shadow-xl ring-1 ring-black/5"
        role="dialog"
        aria-modal="true"
        >
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold">User Baru</h3>
            <button class="p-2 rounded hover:bg-gray-100" @click="openCreate=false" aria-label="Close">✕</button>
        </div>

        <form method="POST" action="{{ route('admin.users.store') }}" class="grid grid-cols-1 gap-4">
            @csrf
            <div>
                <label class="text-sm font-medium">Nama</label>
                <input type="text" name="name" class="mt-1 w-full rounded-lg border-gray-200" required>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="text-sm font-medium">Username</label>
                    <input type="text" name="username" class="mt-1 w-full rounded-lg border-gray-200" required>
                </div>
                <div>
                    <label class="text-sm font-medium">Email</label>
                    <input type="email" name="email" class="mt-1 w-full rounded-lg border-gray-200" required>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="text-sm font-medium">Password</label>
                    <input type="password" name="password" class="mt-1 w-full rounded-lg border-gray-200" required>
                </div>
                <div>
                    <label class="text-sm font-medium">Role</label>
                    <input type="text" name="role" class="mt-1 w-full rounded-lg border-gray-200" placeholder="admin, user, etc.">
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-2">
                <button type="button" class="rounded-full px-4 py-2 bg-gray-100 hover:bg-gray-200" @click="openCreate=false">
                    Batal
                </button>
                <button type="submit" class="rounded-full px-4 py-2 bg-aqua text-white hover:opacity-90">
                    Simpan
                </button>
            </div>
        </form>
        </div>
    </div>
    </template>

</div>
@endsection