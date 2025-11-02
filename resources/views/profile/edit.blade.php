@extends('layouts.admin')

@section('title', 'Profile')
@section('page-title', 'Profile')

@section('content')
@php
    $defaultTab = session('tab')
        ?? ($errors->has('password') || $errors->has('current_password') ? 'password' : 'info');
@endphp
<div
  x-data="{ tab: '{{ $defaultTab }}' }"
  class="max-w-4xl space-y-6"
>

  {{-- Flash success --}}
  @if (session('status'))
    <div class="rounded-xl bg-emerald-50 text-emerald-800 px-4 py-3">
      {{ session('status') }}
    </div>
  @endif

  {{-- Tabs --}}
  <div class="flex items-center gap-4 border-b">
    <button
      class="px-3 py-2 -mb-px border-b-2"
      :class="tab === 'info' ? 'border-ink font-semibold' : 'border-transparent text-gray-500'"
      @click="tab='info'">Main information</button>
    <button
      class="px-3 py-2 -mb-px border-b-2"
      :class="tab === 'password' ? 'border-ink font-semibold' : 'border-transparent text-gray-500'"
      @click="tab='password'">Password</button>
  </div>

  {{-- Main information --}}
  <section x-show="tab === 'info'" x-cloak class="rounded-2xl bg-white ring-1 ring-black/5 p-6">
    <form method="POST" action="{{ route('profile.update') }}" class="grid gap-4 max-w-xl">
      @csrf
      @method('PATCH')
      <input type="hidden" name="action" value="profile">

      <div>
        <label class="text-sm font-medium">Name <span class="text-rose-500">*</span></label>
        <input name="name" type="text" value="{{ old('name', $user->name) }}"
               class="mt-1 w-full rounded-lg border-gray-200" required>
        @error('name') <p class="text-sm text-rose-600 mt-1">{{ $message }}</p> @enderror
      </div>

      <div>
        <label class="text-sm font-medium">Email <span class="text-rose-500">*</span></label>
        <input name="email" type="email" value="{{ old('email', $user->email) }}"
               class="mt-1 w-full rounded-lg border-gray-200" required>
        @error('email') <p class="text-sm text-rose-600 mt-1">{{ $message }}</p> @enderror
      </div>

      <div class="pt-2">
        <button class="rounded-full bg-aqua text-white px-5 py-2 hover:opacity-90">
          Save
        </button>
      </div>
    </form>
  </section>

  {{-- Password --}}
  <section x-show="tab === 'password'" x-cloak class="rounded-2xl bg-white ring-1 ring-black/5 p-6">
    <form method="POST" action="{{ route('password.update') }}" class="grid gap-4 max-w-xl">
      @csrf
      @method('PUT')
      <input type="hidden" name="action" value="password">

      <div>
        <label class="text-sm font-medium">Current password</label>
        <input name="current_password" type="password" class="mt-1 w-full rounded-lg border-gray-200" required>
        @error('current_password') <p class="text-sm text-rose-600 mt-1">{{ $message }}</p> @enderror
      </div>

      <div>
        <label class="text-sm font-medium">New password</label>
        <input name="password" type="password" class="mt-1 w-full rounded-lg border-gray-200" required>
        @error('password') <p class="text-sm text-rose-600 mt-1">{{ $message }}</p> @enderror
      </div>

      <div>
        <label class="text-sm font-medium">Confirm new password</label>
        <input name="password_confirmation" type="password" class="mt-1 w-full rounded-lg border-gray-200" required>
      </div>

      <div class="pt-2">
        <button class="rounded-full bg-aqua text-white px-5 py-2 hover:opacity-90">
          Update Password
        </button>
      </div>
    </form>
  </section>

  {{-- Danger zone (optional delete) --}}
  <section class="rounded-2xl bg-white ring-1 ring-black/5 p-6">
    <h3 class="font-semibold mb-3">Delete account</h3>
    <p class="text-sm text-gray-600 mb-4">Aksi ini tidak dapat dibatalkan.</p>

    <form method="POST" action="{{ route('profile.destroy') }}" class="max-w-xl">
      @csrf
      @method('DELETE')

      <label class="text-sm font-medium">Confirm your password</label>
      <input name="password" type="password" class="mt-1 w-full rounded-lg border-gray-200" required>
      @error('userDeletion.password') <p class="text-sm text-rose-600 mt-1">{{ $message }}</p> @enderror

      <div class="pt-3">
        <button class="rounded-lg bg-rose-100 text-rose-700 px-4 py-2 hover:bg-rose-200">
          Permanently Delete Account
        </button>
      </div>
    </form>
  </section>
</div>
@endsection