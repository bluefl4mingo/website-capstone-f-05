<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Museum Dashboard') }}</title>

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    {{-- Scripts --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
  </head>
  <body class="font-sans antialiased bg-mint text-ink">
    <div class="min-h-screen flex flex-col">
      {{-- Centered page content --}}
      <main class="flex-1 flex items-center">
        <div class="w-full max-w-7xl mx-auto px-6">
          {{ $slot }}
        </div>
      </main>

      <footer class="py-5 text-center text-sm text-ink/70">
        © {{ date('Y') }} Museum Benteng Vredeburg - Dashboard Audio Guide
      </footer>
    </div>
  </body>
</html>