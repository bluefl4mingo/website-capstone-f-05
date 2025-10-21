@extends('layouts.admin')
@section('title', 'Profile')

@section('content')
    <div class="max-w-7xl mx-auto space-y-6">
        <div class="bg-white shadow sm:rounded-lg p-6">
            <livewire:profile.update-profile-information-form />
        </div>

        <div class="bg-white shadow sm:rounded-lg p-6">
            <livewire:profile.update-password-form />
        </div>

        <div class="bg-white shadow sm:rounded-lg p-6">
            <livewire:profile.delete-user-form />
        </div>
    </div>
@endsection