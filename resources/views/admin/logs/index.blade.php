@extends('layouts.admin')

@section('title','Activity Logs')
@section('page-title','Activity Logs')

@section('content')
@php
  // ===== Dummy data (replace with DB query later) =====
  // Each log: id, user_id, user_name, action, when (Carbon|string), target(optional)
  $logs = [
    ['id'=>1011,'user_id'=>1,'user_name'=>'tes1','action'=>'login','when'=>'2025-02-05 09:12:11','target'=>null],
    ['id'=>1012,'user_id'=>1,'user_name'=>'tes1','action'=>'create_item','when'=>'2025-02-05 09:20:02','target'=>'Meriam VOC abad XIX'],
    ['id'=>1013,'user_id'=>1,'user_name'=>'tes1','action'=>'upload_audio','when'=>'2025-02-05 09:25:41','target'=>'Meriam VOC abad XIX (id:1)'],
    ['id'=>1014,'user_id'=>1,'user_name'=>'tes1','action'=>'assign_nfc','when'=>'2025-02-05 09:33:17','target'=>'NFC #AA12FF → Item #1'],
    ['id'=>1015,'user_id'=>1,'user_name'=>'tes1','action'=>'logout','when'=>'2025-02-05 10:05:03','target'=>null],
    ['id'=>1016,'user_id'=>1,'user_name'=>'tes1','action'=>'device_sync','when'=>'2025-02-06 08:04:20','target'=>'Handset-021'],
    ['id'=>1017,'user_id'=>1,'user_name'=>'tes1','action'=>'update_item','when'=>'2025-02-06 08:22:10','target'=>'Patung Jenderal Sudirman'],
    ['id'=>1018,'user_id'=>1,'user_name'=>'tes1','action'=>'upload_audio','when'=>'2025-02-06 08:30:54','target'=>'Diorama Perang Diponegoro (id:2)'],
  ];

  $users    = ['tes1'];           // in real app -> User::pluck('name','id')
  $actions  = ['login','logout','create_item','update_item','delete_item','upload_audio','assign_nfc','device_sync','settings_changed'];
  $total7d  = count($logs);       // example KPI; replace with 7-day scoped count
  $uploads7d= collect($logs)->where('action','upload_audio')->count();
  $sync7d   = collect($logs)->where('action','device_sync')->count();
@endphp

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
    <div class="grid grid-cols-1 md:grid-cols-5 gap-3">
      <input type="text" class="rounded-lg border-gray-200 md:col-span-2" placeholder="Cari (user / target / action)…" x-model="search">

      <select class="rounded-lg border-gray-200" x-model="user">
        <option value="">Semua User</option>
        @foreach($users as $u)
          <option value="{{ $u }}">{{ $u }}</option>
        @endforeach
      </select>

      <select class="rounded-lg border-gray-200" x-model="action">
        <option value="">Semua Aksi</option>
        @foreach($actions as $a)
          <option value="{{ $a }}">{{ Str::headline($a) }}</option>
        @endforeach
      </select>

      <div class="grid grid-cols-2 gap-2">
        <input type="date" class="rounded-lg border-gray-200" x-model="from" title="Dari tanggal">
        <input type="date" class="rounded-lg border-gray-200" x-model="to" title="Sampai tanggal">
      </div>
    </div>
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
      <tbody class="divide-y"
             x-data="{ rows: @js($logs) }"
             x-init="
               $watch('search', ()=>{}); $watch('user', ()=>{}); $watch('action', ()=>{});
               $watch('from', ()=>{}); $watch('to', ()=>{});
             ">
        <template
          x-for="row in rows.filter(r => {
            const s = search.toLowerCase();
            const matchSearch = !s || (r.user_name?.toLowerCase().includes(s) || r.target?.toLowerCase().includes(s) || r.action?.toLowerCase().includes(s));
            const matchUser   = !user || r.user_name === user;
            const matchAction = !action || r.action === action;

            const ts = new Date(r.when.replace(' ','T'));
            const fromOk = !from || ts >= new Date(from);
            const toOk   = !to || ts <= new Date(to + 'T23:59:59');

            return matchSearch && matchUser && matchAction && fromOk && toOk;
          })"
          :key="row.id"
        >
          <tr class="hover:bg-gray-50">
            <td class="px-4 py-3" x-text="new Date(row.when.replace(' ','T')).toLocaleString()"></td>
            <td class="px-4 py-3" x-text="row.user_name"></td>
            <td class="px-4 py-3">
              <span class="inline-flex items-center gap-1 px-2 py-1 rounded text-xs"
                    :class="{
                      'bg-green-50 text-green-700': ['login','create_item','upload_audio','assign_nfc','device_sync'].includes(row.action),
                      'bg-amber-50 text-amber-700': ['update_item','settings_changed'].includes(row.action),
                      'bg-rose-50 text-rose-700': ['logout','delete_item'].includes(row.action),
                    }"
                    x-text="row.action.replaceAll('_',' ')">
              </span>
            </td>
            <td class="px-4 py-3" x-text="row.target ?? '-'"></td>
            <td class="px-4 py-3">
              <div class="flex justify-end">
                <button class="px-3 py-1.5 rounded-lg border text-sm hover:bg-gray-50"
                        @click="selected=row; openDetail=true">
                  View
                </button>
              </div>
            </td>
          </tr>
        </template>
      </tbody>
    </table>

    {{-- Fake pagination placeholder --}}
    <div class="border-t px-4 py-3 text-sm text-gray-500">
      Pagination here (later)
    </div>
  </div>

  {{-- TIMELINE VIEW --}}
  <div x-show="view==='timeline'">
    <div class="rounded-2xl bg-white ring-1 ring-black/5 p-6">
      <ol class="relative border-s border-gray-200 ms-3"
          x-data="{ rows: @js($logs) }">
        <template x-for="row in rows.sort((a,b)=> new Date(b.when.replace(' ','T')) - new Date(a.when.replace(' ','T')))" :key="row.id">
          <li class="mb-8 ms-6">
            <span class="absolute -start-3 flex h-6 w-6 items-center justify-center rounded-full bg-mint/40 text-xs">⏺</span>
            <h3 class="font-medium" x-text="row.user_name + ' — ' + row.action.replaceAll('_',' ')"></h3>
            <time class="text-xs text-gray-500" x-text="new Date(row.when.replace(' ','T')).toLocaleString()"></time>
            <p class="text-sm mt-1" x-text="row.target ?? '-'"></p>
            <button class="mt-2 px-3 py-1.5 rounded-lg border text-sm hover:bg-gray-50"
                    @click="selected=row; openDetail=true">
              View Detail
            </button>
          </li>
        </template>
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
          <div><span class="text-gray-500">User:</span> <span x-text="selected?.user_name"></span></div>
          <div><span class="text-gray-500">Action:</span> <span x-text="selected?.action"></span></div>
          <div><span class="text-gray-500">Target:</span> <span x-text="selected?.target ?? '-'"></span></div>
          <div><span class="text-gray-500">When:</span> <span x-text="new Date((selected?.when || '').replace(' ','T')).toLocaleString()"></span></div>
        </div>

        <div class="mt-6 flex justify-end">
          <button class="px-3 py-1.5 rounded-lg border hover:bg-gray-50" @click="openDetail=false">Close</button>
        </div>
      </div>
    </div>
  </template>

</section>
@endsection