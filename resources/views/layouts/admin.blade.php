<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>@yield('title', 'Admin')</title>

  {{-- Tailwind / Alpine are already included by Breeze/Volt via Vite --}}
  @vite(['resources/css/app.css', 'resources/js/app.js'])

  {{-- Hide Alpine-marked elements until JS is ready --}}
  <style>[x-cloak]{display:none!important}</style>

  @stack('head') {{-- optional: per-page head additions --}}
</head>
<body class="bg-gray-50 text-gray-900" x-data="{ sidebarOpen: false }">

  <div class="flex h-screen">

    {{-- ============= SIDEBAR (desktop fixed / mobile drawer) ============= --}}
    <aside
      class="fixed inset-y-0 left-0 z-40 w-64 bg-white border-r border-gray-200 flex flex-col justify-between
             transform transition-transform duration-200
             -translate-x-full lg:translate-x-0"
  :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'">

      {{-- top / nav --}}
      <div class="p-6">
        {{-- logo / title --}}
        <div class="flex items-center gap-2 mb-8">
          <img src="{{ asset('images/logo_vredeburg.avif') }}" alt="Logo Museum" class="h-12">
          <span class="text-lg font-semibold">Admin Vredeburg</span>
        </div>

        {{-- NAV LINKS --}}
        <nav class="space-y-2 text-md text-aqua font-semibold">
          <a href="{{ route('admin.dashboard') }}"
             class="flex items-center gap-3 px-3 py-2 rounded-lg 
                    {{ request()->routeIs('admin.dashboard') ? 'bg-mint/20 text-aqua' : 'hover:bg-mint/20 text-ink' }}"
            @if(request()->routeIs('admin.dashboard')) aria-current="page" @endif>
            <span>🏛</span><span>Dashboard</span>
          </a>

          <a href="{{ route('admin.items.index') }}"
             class="flex items-center gap-3 px-3 py-2 rounded-lg 
                    {{ request()->routeIs('admin.items.*') ? 'bg-mint/20 text-aqua' : 'hover:bg-mint/20 text-ink' }}"
            @if(request()->routeIs('admin.items.*')) aria-current="page" @endif>
            <span>
              <img src="{{ asset('images/items.png') }}" alt="Items" class="h-6 w-6">
            </span><span>Items</span>
          </a>

          <a href="{{ route('admin.audio.index') }}"
             class="flex items-center gap-3 px-3 py-2 rounded-lg 
                    {{ request()->routeIs('admin.audio.*') ? 'bg-mint/20 text-aqua' : 'hover:bg-mint/20 text-ink' }}"
            @if(request()->routeIs('admin.audio.*')) aria-current="page" @endif>
            <span>🎧</span><span>Audio Files</span>
          </a>

          <a href="{{ route('admin.nfc.index') }}"
             class="flex items-center gap-3 px-3 py-2 rounded-lg 
                    {{ request()->routeIs('admin.nfc.*') ? 'bg-mint/20 text-aqua' : 'hover:bg-mint/20 text-ink' }}"
            @if(request()->routeIs('admin.nfc.*')) aria-current="page" @endif>
            <span>
              <img src="{{ asset('images/nfc.png') }}" alt="NFC Tags" class="h-5 w-5">
            </span><span>NFC Tags</span>
          </a>

          <!-- <a href="{{ route('admin.devices.index') }}"
             class="flex items-center gap-3 px-3 py-2 rounded-lg 
                    {{ request()->routeIs('admin.devices.*') ? 'bg-mint/20 text-aqua' : 'hover:bg-mint/20 text-ink' }}"
            @if(request()->routeIs('admin.devices.*')) aria-current="page" @endif>
            <span>
              <img src="{{ asset('images/iot.png') }}" alt="Devices" class="h-8 w-5">
            </span><span>Devices</span>
          </a> -->

          <a href="{{ route('admin.logs.index') }}"
             class="flex items-center gap-3 px-3 py-2 rounded-lg 
                    {{ request()->routeIs('admin.logs.*') ? 'bg-mint/20 text-aqua' : 'hover:bg-mint/20 text-ink' }}"
            @if(request()->routeIs('admin.logs.*')) aria-current="page" @endif>
            <span>
              <img src="{{ asset('images/log.png') }}" alt="Activity Logs" class="h-5 w-5">
            </span><span>Activity Logs</span>
          </a>
        </nav>
      </div>

      {{-- bottom: profile --}}
      <div class="border-t border-gray-200 p-4" x-data="{ showLogoutConfirm: false }">
        <div class="flex items-center">
          <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 hover:bg-gray-50 p-2 rounded-lg flex-1">
            <div class="h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center text-gray-700 font-medium">
              {{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}
            </div>
              <div class="text-xs overflow-hidden">
                <div class="font-semibold text-sm">{{ auth()->user()->name ?? 'Admin' }}</div>
                <div class="text-gray-500 truncate" style="max-width:180px">{{ auth()->user()->email ?? '' }}</div>
              </div>
          </a>

          <button type="button" @click="showLogoutConfirm = true" class="p-2 rounded-full hover:bg-gray-100 transition text-ink/70" title="Logout" aria-label="Logout">
            {{-- power icon --}}
            <span>⏻</span>
          </button>
        </div>

        <!-- Logout confirmation modal -->
        <div x-show="showLogoutConfirm" x-cloak class="fixed inset-0 z-50 flex items-center justify-center">
          <div class="absolute inset-0 bg-black/40" @click="showLogoutConfirm = false" aria-hidden="true"></div>

          <div class="relative bg-white rounded-lg shadow-lg p-4 w-[320px] mx-4" @keydown.escape.window="showLogoutConfirm = false">
            <h3 class="font-semibold text-lg text-rose-600 mb-2">Konfirmasi Logout</h3>
            <p class="text-sm text-gray-600 mb-4">Apakah Anda yakin ingin mengakhiri sesi dan kembali ke halaman login?</p>

            <div class="flex justify-end gap-2">
              <button type="button" @click="showLogoutConfirm = false" class="px-3 py-2 rounded-lg bg-gray-100 hover:bg-gray-200">Batal</button>

              <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="px-3 py-2 rounded-lg bg-rose-100 text-rose-700 hover:bg-rose-200">Logout</button>
              </form>
            </div>
          </div>
        </div>
      </div>
    </aside>

    {{-- overlay for mobile --}}
    <div class="fixed inset-0 z-30 bg-black/40 lg:hidden"
         x-show="sidebarOpen"
         x-transition.opacity
         @click="sidebarOpen = false"
         aria-hidden="true"></div>

    {{-- ===================== MAIN COLUMN ===================== --}}
  <div class="flex-1 flex flex-col lg:ml-72">

      {{-- mobile top bar --}}
      <header class="sticky top-0 z-10 bg-gray-50/80 backdrop-blur border-b border-gray-200 lg:hidden">
        <div class="flex items-center justify-between px-4 py-3">
          <button class="p-2 rounded-md hover:bg-gray-200"
                  @click="sidebarOpen = true"
                  aria-label="Open sidebar">
            {{-- simple hamburger --}}
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
                 viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
              <path stroke-linecap="round" stroke-linejoin="round"
                    d="M3.75 6.75h16.5M3.75 12h16.5M3.75 17.25h16.5" />
            </svg>
          </button>
          <div class="font-semibold">Admin</div>
          <div class="w-6"></div>
        </div>
      </header>

      {{-- scrollable page content --}}
      <main class="flex-1 overflow-y-auto p-6">
        {{-- Flash Messages --}}
        @if(session('status'))
          <div class="mb-4 rounded-lg bg-green-50 p-4 text-green-800 border border-green-200" x-data="{ show: true }" x-show="show" x-transition>
            <div class="flex items-center justify-between">
              <span>{{ session('status') }}</span>
              <button @click="show = false" class="text-green-600 hover:text-green-800">✕</button>
            </div>
          </div>
        @endif

        @if($errors->any())
          <div class="mb-4 rounded-lg bg-red-50 p-4 text-red-800 border border-red-200" x-data="{ show: true }" x-show="show" x-transition>
            <div class="flex items-center justify-between">
              <div>
                <strong>Terdapat kesalahan:</strong>
                <ul class="mt-2 list-disc list-inside">
                  @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                  @endforeach
                </ul>
              </div>
              <button @click="show = false" class="text-red-600 hover:text-red-800">✕</button>
            </div>
          </div>
        @endif

        @yield('content')
      </main>

    </div>
  </div>

  @stack('scripts') {{-- per-page scripts (e.g., Chart.js) --}}
</body>
</html>