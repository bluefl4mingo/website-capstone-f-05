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
             class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-mint/20">
            <span>🏛</span><span>Dashboard</span>
          </a>

          <a href="{{ route('admin.items.index') }}"
             class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-mint/20">
            <span>⚱️</span><span>Items</span>
          </a>

          <a href="{{ route('admin.audio.index') }}"
             class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-mint/20">
            <span>🎧</span><span>Audio Files</span>
          </a>

          <a href="{{ route('admin.nfc.index') }}"
             class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-mint/20">
            <span>🔖</span><span>NFC Tags</span>
          </a>

          <a href="{{ route('admin.devices.index') }}"
             class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-mint/20">
            <span>📱</span><span>Devices</span>
          </a>

          <a href="{{ route('admin.logs.index') }}"
             class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-mint/20">
            <span>🕒</span><span>Activity Logs</span>
          </a>
        </nav>
      </div>

      {{-- bottom: profile + logout --}}
      <div class="border-t border-gray-200 p-4">
        <div class="flex items-center gap-3 mb-3">
          <div class="h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center text-gray-700 font-medium">
            {{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}
          </div>
          <div class="text-xs">
            <div class="font-semibold text-sm">{{ auth()->user()->name ?? 'Admin' }}</div>
            <div class="text-gray-500">{{ auth()->user()->email ?? '' }}</div>
          </div>
          <div class="ms-auto"> <a href="{{ route('profile.edit') }}" class="text-xs underline text-ink/60 hover:text-ink">Profil</a> </div>
        </div>

        <form method="POST" action="{{ route('logout') }}">
          @csrf
          <button type="submit"
                  class="w-full inline-flex items-center justify-center gap-2 rounded-lg bg-rose-100 px-3 py-2
                         text-rose-700 hover:bg-rose-200 transition">
            <span>⏻</span><span>Keluar</span>
          </button>
        </form>
      </div>
    </aside>

    {{-- overlay for mobile --}}
    <div class="fixed inset-0 z-30 bg-black/40 lg:hidden"
         x-show="sidebarOpen"
         x-transition.opacity
         @click="sidebarOpen = false"
         aria-hidden="true"></div>

    {{-- ===================== MAIN COLUMN ===================== --}}
    <div class="flex-1 flex flex-col lg:ml-64">

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
        @yield('content')
      </main>

    </div>
  </div>

  @stack('scripts') {{-- per-page scripts (e.g., Chart.js) --}}
</body>
</html>