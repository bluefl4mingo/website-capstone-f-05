@extends('layouts.admin')

@section('title','Activity Logs')
@section('page-title','Activity Logs')

@section('content')
<section x-data="{view:'table', search:'', user:'', action:'', from:'', to:'', openDetail:false, selected:null}" x-cloak class="space-y-5">

  {{-- Header / Actions --}}
  <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
    <div>
      <h1 class="text-xl font-semibold">Activity Logs</h1>
      <p class="text-sm text-gray-500">Jejak aktivitas admin: siapa melakukan apa dan kapan.</p>
    </div>

    <div class="flex flex-wrap gap-2 md:items-center">
      <button class="rounded-full px-4 py-2 border hover:bg-gray-50" @click="view='table'">Table</button>
      <button class="rounded-full px-4 py-2 border hover:bg-gray-50" @click="view='timeline'">Timeline</button>
      <button class="rounded-full px-4 py-2 bg-gray-100 text-gray-500 cursor-not-allowed" title="Export CSV (later)">Export CSV</button>
      <button class="rounded-full px-4 py-2 bg-gray-100 text-gray-500 cursor-not-allowed" title="Purge old logs (later)">Purge</button>
    </div>
  </div>

  {{-- KPIs --}}
  <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
    <div class="rounded-2xl bg-white ring-1 ring-black/5 p-4">
      <div class="text-xs text-gray-500">Total Events (7d)</div>
      <div class="text-2xl font-semibold mt-1">{{ $total7d }}</div>
    </div>
    <div class="rounded-2xl bg-white ring-1 ring-black/5 p-4">
      <div class="text-xs text-gray-500">Audio Uploads (7d)</div>
      <div class="text-2xl font-semibold mt-1">{{ $uploads7d }}</div>
    </div>
    <div class="rounded-2xl bg-white ring-1 ring-black/5 p-4">
      <div class="text-xs text-gray-500">Device Syncs (7d)</div>
      <div class="text-2xl font-semibold mt-1">{{ $sync7d }}</div>
    </div>
  </div>

  {{-- Filters --}}
  <div class="rounded-2xl bg-white ring-1 ring-black/5 p-4">
    <form method="GET" action="{{ route('admin.logs.index') }}" class="grid grid-cols-1 md:grid-cols-5 gap-3">
      <input type="text" name="search" value="{{ request('search') }}" class="rounded-lg border-gray-200 md:col-span-2" placeholder="Cari (user / target / action)…">

      <select name="user" class="rounded-lg border-gray-200">
        <option value="">Semua User</option>
        @foreach($users as $u)
          <option value="{{ $u->id }}" @selected($user == $u->id)>{{ $u->name }}</option>
        @endforeach
      </select>

      <select name="action" class="rounded-lg border-gray-200">
        <option value="">Semua Aksi</option>
        @foreach($actions as $a)
          <option value="{{ $a }}" @selected($action === $a)>{{ Str::headline($a) }}</option>
        @endforeach
      </select>

      <div class="grid grid-cols-2 gap-2">
        <input type="date" name="from" value="{{ $from }}" class="rounded-lg border-gray-200" title="Dari tanggal">
        <input type="date" name="to" value="{{ $to }}" class="rounded-lg border-gray-200" title="Sampai tanggal">
      </div>
      
      <button type="submit" class="rounded-lg bg-aqua text-white px-4 py-2 hover:opacity-90">Filter</button>
    </form>
  </div>

  {{-- TABLE VIEW --}}
  <div x-show="view==='table'" class="rounded-2xl bg-white ring-1 ring-black/5 overflow-hidden">
    <table class="w-full">
      <thead class="bg-gray-50 text-xs uppercase tracking-wide text-gray-600">
        <tr>
          <th class="text-left px-4 py-3">Waktu</th>
          <th class="text-left px-4 py-3">User</th>
          <th class="text-left px-4 py-3">Aksi</th>
          <th class="text-left px-4 py-3">Target</th>
          <th class="text-right px-4 py-3">Detail</th>
        </tr>
      </thead>
      <tbody class="divide-y">
        @forelse($logs as $log)
          <tr class="hover:bg-gray-50">
            <td class="px-4 py-3">{{ $log->waktu_aktivitas->format('Y-m-d H:i:s') }}</td>
            <td class="px-4 py-3">{{ $log->user->name ?? 'N/A' }}</td>
            <td class="px-4 py-3">
              <span class="inline-flex items-center gap-1 px-2 py-1 rounded text-xs
                @if(in_array($log->aktivitas, ['create_item', 'upload_audio', 'create_nfc_tag', 'device_sync']))
                  bg-green-50 text-green-700
                @elseif(in_array($log->aktivitas, ['update_item', 'update_audio', 'update_nfc_tag']))
                  bg-amber-50 text-amber-700
                @elseif(in_array($log->aktivitas, ['delete_item', 'delete_audio', 'delete_nfc_tag']))
                  bg-rose-50 text-rose-700
                @else
                  bg-gray-100 text-gray-700
                @endif
              ">
                {{ str_replace('_', ' ', $log->aktivitas) }}
              </span>
            </td>
            <td class="px-4 py-3">{{ $log->context['nama_item'] ?? $log->context['nama_file'] ?? $log->context['kode_tag'] ?? '—' }}</td>
            <td class="px-4 py-3">
              <div class="flex justify-end">
                <button class="px-3 py-1.5 rounded-lg border text-sm hover:bg-gray-50"
                        @click="selected = {{ json_encode([
                          'id' => $log->id,
                          'user' => $log->user->name ?? 'N/A',
                          'aktivitas' => $log->aktivitas,
                          'waktu' => $log->waktu_aktivitas->format('Y-m-d H:i:s'),
                          'context' => $log->context
                        ]) }}; openDetail=true">
                  View
                </button>
              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="5" class="px-4 py-10 text-center text-gray-500">
              Belum ada activity logs.
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>

    {{-- Pagination --}}
    <div class="border-t px-4 py-3">
      {{ $logs->links() }}
    </div>
  </div>

  {{-- TIMELINE VIEW --}}
  <div x-show="view==='timeline'">
    <div class="rounded-2xl bg-white ring-1 ring-black/5 p-6">
      <ol class="relative border-s border-gray-200 ms-3">
        @foreach($logs as $log)
          <li class="mb-8 ms-6">
            <span class="absolute -start-3 flex h-6 w-6 items-center justify-center rounded-full bg-mint/40 text-xs">⏺</span>
            <h3 class="font-medium">{{ $log->user->name ?? 'N/A' }} — {{ str_replace('_', ' ', $log->aktivitas) }}</h3>
            <time class="text-xs text-gray-500">{{ $log->waktu_aktivitas->format('Y-m-d H:i:s') }}</time>
            <p class="text-sm mt-1">{{ $log->context['nama_item'] ?? $log->context['nama_file'] ?? $log->context['kode_tag'] ?? '—' }}</p>
            <button class="mt-2 px-3 py-1.5 rounded-lg border text-sm hover:bg-gray-50"
                    @click="selected = {{ json_encode([
                      'id' => $log->id,
                      'user' => $log->user->name ?? 'N/A',
                      'aktivitas' => $log->aktivitas,
                      'waktu' => $log->waktu_aktivitas->format('Y-m-d H:i:s'),
                      'context' => $log->context
                    ]) }}; openDetail=true">
              View Detail
            </button>
          </li>
        @endforeach
      </ol>
    </div>
  </div>

  {{-- DETAIL MODAL --}}
  <template x-teleport="body">
    <div x-show="openDetail"
         x-transition.opacity
         x-cloak
         @keydown.window.escape="openDetail=false"
         class="fixed inset-0 z-50 flex items-start justify-center">
      <div class="absolute inset-0 bg-black/40" @click="openDetail=false"></div>

      <div x-show="openDetail" x-transition
           class="relative mt-20 w-full max-w-xl rounded-2xl bg-white p-6 shadow-xl ring-1 ring-black/5"
           role="dialog" aria-modal="true">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-lg font-semibold">Log Detail</h3>
          <button class="p-2 rounded hover:bg-gray-100" @click="openDetail=false">✕</button>
        </div>

        <div class="grid grid-cols-1 gap-3 text-sm">
          <div><span class="text-gray-500">ID:</span> <span x-text="selected?.id"></span></div>
          <div><span class="text-gray-500">User:</span> <span x-text="selected?.user"></span></div>
          <div><span class="text-gray-500">Action:</span> <span x-text="selected?.aktivitas"></span></div>
          <div><span class="text-gray-500">When:</span> <span x-text="selected?.waktu"></span></div>
          <div><span class="text-gray-500">Context:</span> <pre class="text-xs bg-gray-50 p-2 rounded mt-1" x-text="JSON.stringify(selected?.context, null, 2)"></pre></div>
        </div>

        <div class="mt-6 flex justify-end">
          <button class="px-3 py-1.5 rounded-lg border hover:bg-gray-50" @click="openDetail=false">Close</button>
        </div>
      </div>
    </div>
  </template>

</section>
@endsection