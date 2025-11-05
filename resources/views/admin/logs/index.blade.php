@extends('layouts.admin')

@section('title','Activity Logs')
@section('page-title','Activity Logs')

@section('content')
<section x-data="{view:'table', openDetail:false, selected:null}" x-cloak class="space-y-5">

  {{-- Header / Actions --}}
  <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
    <div class="flex-1">
      <h1 class="text-xl font-semibold">Activity Logs</h1>
      <p class="text-sm text-gray-500">Jejak aktivitas admin.</p>
    </div>

    <div class="flex flex-wrap gap-2 md:items-center">
      <button class="rounded-full px-4 py-2 border hover:bg-gray-50" 
              :class="{'bg-aqua text-white': view==='table'}" 
              @click="view='table'">Table</button>
      <button class="rounded-full px-4 py-2 border hover:bg-gray-50" 
              :class="{'bg-aqua text-white': view==='timeline'}" 
              @click="view='timeline'">Timeline</button>
    </div>
  </div>

  {{-- KPIs --}}
  <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
    <div class="rounded-2xl bg-white ring-1 ring-black/5 p-4">
      <div class="text-xs text-gray-500 uppercase">Total Events (7d)</div>
      <div class="text-2xl font-semibold mt-1">{{ number_format($total7d) }}</div>
    </div>
    <div class="rounded-2xl bg-white ring-1 ring-black/5 p-4">
      <div class="text-xs text-gray-500 uppercase">Audio Uploads (7d)</div>
      <div class="text-2xl font-semibold mt-1">{{ number_format($uploads7d) }}</div>
    </div>
    <div class="rounded-2xl bg-white ring-1 ring-black/5 p-4">
      <div class="text-xs text-gray-500 uppercase">Device Syncs (7d)</div>
      <div class="text-2xl font-semibold mt-1">{{ number_format($sync7d) }}</div>
    </div>
  </div>

  {{-- Filters Section --}}
  <div class="rounded-2xl bg-white ring-1 ring-black/5 p-4">
    <form method="GET" action="{{ route('admin.logs.index') }}" class="grid grid-cols-1 gap-3" id="filterForm">
      <div class="grid grid-cols-1 md:grid-cols-6 gap-3">
        <div class="md:col-span-2">
          <label class="text-xs font-medium text-gray-600 uppercase">Cari</label>
          <input type="text" 
                 name="search" 
                 value="{{ $search }}" 
                 class="mt-1 w-full rounded-lg border-gray-200" 
                 placeholder="Cari user, aktivitas, atau target…"
                 autofocus>
        </div>

        <div>
          <label class="text-xs font-medium text-gray-600 uppercase">User</label>
          <select name="user" class="mt-1 w-full rounded-lg border-gray-200">
            <option value="">Semua User</option>
            @foreach($users as $u)
              <option value="{{ $u->id }}" @selected($user == $u->id)>{{ $u->name }}</option>
            @endforeach
          </select>
        </div>

        <div>
          <label class="text-xs font-medium text-gray-600 uppercase">Aktivitas</label>
          <select name="action" class="mt-1 w-full rounded-lg border-gray-200">
            <option value="">Semua Aksi</option>
            @foreach($actions as $a)
              <option value="{{ $a }}" @selected($action === $a)>{{ Str::headline($a) }}</option>
            @endforeach
          </select>
        </div>

        <div>
          <label class="text-xs font-medium text-gray-600 uppercase">Dari Tanggal</label>
          <input type="date" 
                 name="from" 
                 value="{{ $from }}" 
                 class="mt-1 w-full rounded-lg border-gray-200">
        </div>

        <div>
          <label class="text-xs font-medium text-gray-600 uppercase">Sampai Tanggal</label>
          <input type="date" 
                 name="to" 
                 value="{{ $to }}" 
                 class="mt-1 w-full rounded-lg border-gray-200">
        </div>
      </div>

      <div class="flex gap-2">
        <button type="submit" class="px-4 py-2 rounded-lg bg-aqua text-white hover:opacity-90 whitespace-nowrap">
          Terapkan Filter
        </button>
        @if($search || $user || $action || $from || $to)
          <a href="{{ route('admin.logs.index') }}" 
             class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 whitespace-nowrap">
            Reset
          </a>
        @endif
      </div>
    </form>

    {{-- Active Filters Display --}}
    @if($search || $user || $action || $from || $to)
      <div class="mt-3 pt-3 border-t flex flex-wrap gap-2 items-center text-sm">
        <span class="text-gray-600 font-medium">Filter aktif:</span>
        @if($search)
          <span class="inline-flex items-center gap-1 bg-blue-50 text-blue-700 px-2 py-1 rounded">
            Pencarian: "{{ $search }}"
            <a href="{{ route('admin.logs.index', array_filter(['user' => $user, 'action' => $action, 'from' => $from, 'to' => $to])) }}" class="hover:text-blue-900">✕</a>
          </span>
        @endif
        @if($user)
          @php $selectedUser = $users->firstWhere('id', $user); @endphp
          <span class="inline-flex items-center gap-1 bg-green-50 text-green-700 px-2 py-1 rounded">
            User: {{ $selectedUser->name ?? 'Unknown' }}
            <a href="{{ route('admin.logs.index', array_filter(['search' => $search, 'action' => $action, 'from' => $from, 'to' => $to])) }}" class="hover:text-green-900">✕</a>
          </span>
        @endif
        @if($action)
          <span class="inline-flex items-center gap-1 bg-purple-50 text-purple-700 px-2 py-1 rounded">
            Aktivitas: {{ Str::headline($action) }}
            <a href="{{ route('admin.logs.index', array_filter(['search' => $search, 'user' => $user, 'from' => $from, 'to' => $to])) }}" class="hover:text-purple-900">✕</a>
          </span>
        @endif
        @if($from || $to)
          <span class="inline-flex items-center gap-1 bg-amber-50 text-amber-700 px-2 py-1 rounded">
            Tanggal: {{ $from ?: '...' }} s/d {{ $to ?: '...' }}
            <a href="{{ route('admin.logs.index', array_filter(['search' => $search, 'user' => $user, 'action' => $action])) }}" class="hover:text-amber-900">✕</a>
          </span>
        @endif
      </div>
    @endif
  </div>

  {{-- Results Summary --}}
  @if($logs->total() > 0)
    <div class="flex items-center justify-between text-sm text-gray-600">
      <div>
        Menampilkan {{ $logs->firstItem() }} - {{ $logs->lastItem() }} dari {{ $logs->total() }} log
        @if($search || $user || $action || $from || $to)
          (terfilter)
        @endif
      </div>
    </div>
  @endif

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
            <td class="px-4 py-3">{{ $log->waktu_aktivitas->setTimezone('Asia/Jakarta')->format('Y-m-d H:i:s') }}</td>
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
                          'waktu' => $log->waktu_aktivitas->setTimezone('Asia/Jakarta')->format('Y-m-d H:i:s'),
                          'context' => $log->context
                        ]) }}; openDetail=true">
                  View
                </button>
              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="5" class="px-4 py-12 text-center">
              <div class="text-gray-400 mb-2">
                <svg class="mx-auto h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
              </div>
              @if($search || $user || $action || $from || $to)
                <p class="text-gray-600 font-medium">Tidak ada log yang sesuai dengan filter</p>
                <p class="text-sm text-gray-500 mt-1">Coba ubah atau reset filter untuk melihat lebih banyak data</p>
                <a href="{{ route('admin.logs.index') }}" class="inline-block mt-3 px-4 py-2 rounded-lg border border-gray-300 text-sm hover:bg-gray-50">
                  Reset Filter
                </a>
              @else
                <p class="text-gray-600 font-medium">Belum ada activity logs</p>
                <p class="text-sm text-gray-500 mt-1">Log aktivitas akan muncul saat ada tindakan yang dilakukan</p>
              @endif
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
      @if($logs->count() > 0)
        <ol class="relative border-s border-gray-200 ms-3">
          @foreach($logs as $log)
            <li class="mb-8 ms-6">
              <span class="absolute -start-3 flex h-6 w-6 items-center justify-center rounded-full 
                @if(in_array($log->aktivitas, ['create_item', 'upload_audio', 'create_nfc_tag']))
                  bg-green-100 text-green-700
                @elseif(in_array($log->aktivitas, ['update_item', 'update_audio', 'update_nfc_tag']))
                  bg-amber-100 text-amber-700
                @elseif(in_array($log->aktivitas, ['delete_item', 'delete_audio', 'delete_nfc_tag']))
                  bg-rose-100 text-rose-700
                @else
                  bg-mint/40 text-gray-700
                @endif
                text-xs">⏺</span>
              <h3 class="font-medium">{{ $log->user->name ?? 'N/A' }} — {{ str_replace('_', ' ', $log->aktivitas) }}</h3>
              <time class="text-xs text-gray-500">{{ $log->waktu_aktivitas->setTimezone('Asia/Jakarta')->format('Y-m-d H:i:s') }}</time>
              <p class="text-sm mt-1">{{ $log->context['nama_item'] ?? $log->context['nama_file'] ?? $log->context['kode_tag'] ?? '—' }}</p>
              <button class="mt-2 px-3 py-1.5 rounded-lg border text-sm hover:bg-gray-50"
                      @click="selected = {{ json_encode([
                        'id' => $log->id,
                        'user' => $log->user->name ?? 'N/A',
                        'aktivitas' => $log->aktivitas,
                        'waktu' => $log->waktu_aktivitas->setTimezone('Asia/Jakarta')->format('Y-m-d H:i:s'),
                        'context' => $log->context
                      ]) }}; openDetail=true">
                View Detail
              </button>
            </li>
          @endforeach
        </ol>

        {{-- Pagination for Timeline --}}
        <div class="mt-6 pt-6 border-t">
          {{ $logs->links() }}
        </div>
      @else
        <div class="text-center py-12">
          <div class="text-gray-400 mb-2">
            <svg class="mx-auto h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
          @if($search || $user || $action || $from || $to)
            <p class="text-gray-600 font-medium">Tidak ada log yang sesuai dengan filter</p>
            <p class="text-sm text-gray-500 mt-1">Coba ubah atau reset filter untuk melihat lebih banyak data</p>
            <a href="{{ route('admin.logs.index') }}" class="inline-block mt-3 px-4 py-2 rounded-lg border border-gray-300 text-sm hover:bg-gray-50">
              Reset Filter
            </a>
          @else
            <p class="text-gray-600 font-medium">Belum ada activity logs</p>
            <p class="text-sm text-gray-500 mt-1">Log aktivitas akan muncul saat ada tindakan yang dilakukan</p>
          @endif
        </div>
      @endif
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