@extends('layouts.admin')

@section('title','Devices')
@section('page-title','Devices')

@section('content')
@php
  // Dummy devices – replace with DB/API later
  $devices = [
    [
      'id' => 1,
      'name' => 'Handset-021',
      'type' => 'Handset',
      'status' => 'online',
      'battery' => 82,
      'last_sync' => '2m ago',
      'app_version' => '1.3.2',
      'location' => 'Galeri A',
      'items_cached' => 123,
      'ip' => '10.0.0.21',
    ],
    [
      'id' => 2,
      'name' => 'Kiosk-Lobby',
      'type' => 'Kiosk',
      'status' => 'online',
      'battery' => null,  // Kiosk on AC
      'last_sync' => '12m ago',
      'app_version' => '1.3.2',
      'location' => 'Lobby',
      'items_cached' => 142,
      'ip' => '10.0.0.33',
    ],
    [
      'id' => 3,
      'name' => 'Handset-009',
      'type' => 'Handset',
      'status' => 'offline',
      'battery' => 15,
      'last_sync' => '1h ago',
      'app_version' => '1.3.1',
      'location' => 'Galeri B',
      'items_cached' => 97,
      'ip' => '10.0.0.45',
    ],
  ];

  $locations = ['Lobby', 'Galeri A', 'Galeri B'];
@endphp

<section
  x-data="{ openRegister:false, openDetail:false, selected:null }"
  x-cloak
  class="space-y-5"
>
  {{-- Top: title + actions --}}
  <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
    <div>
      <h1 class="text-xl font-semibold">Devices</h1>
      <p class="text-sm text-gray-500">Pantau perangkat audio guide (status, sinkronisasi, dan versi aplikasi).</p>
    </div>

    <div class="flex flex-col md:flex-row gap-2 md:items-center">
      <input type="text" class="w-64 rounded-lg border-gray-200" placeholder="Cari nama / IP…">
      <select class="rounded-lg border-gray-200">
        <option value="">Semua Lokasi</option>
        @foreach($locations as $loc)
          <option>{{ $loc }}</option>
        @endforeach
      </select>
      <select class="rounded-lg border-gray-200">
        <option value="">Semua Status</option>
        <option value="online">Online</option>
        <option value="offline">Offline</option>
      </select>

      <button type="button"
              class="rounded-full bg-gray-100 px-4 py-2 hover:bg-gray-200">
        Sync All
      </button>

      <button type="button"
              @click="openRegister=true; selected=null"
              class="rounded-full bg-aqua text-white px-4 py-2 hover:opacity-90">
        + Register Device
      </button>
    </div>
  </div>

  {{-- Tiny stats --}}
  <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
    @php
      $online = collect($devices)->where('status','online')->count();
      $offline = collect($devices)->where('status','offline')->count();
      $total = count($devices);
    @endphp
    <div class="rounded-2xl bg-white ring-1 ring-black/5 p-4">
      <div class="text-xs text-gray-500">Devices Online</div>
      <div class="text-2xl font-semibold mt-1">{{ $online }}</div>
    </div>
    <div class="rounded-2xl bg-white ring-1 ring-black/5 p-4">
      <div class="text-xs text-gray-500">Devices Offline</div>
      <div class="text-2xl font-semibold mt-1">{{ $offline }}</div>
    </div>
    <div class="rounded-2xl bg-white ring-1 ring-black/5 p-4">
      <div class="text-xs text-gray-500">Total Devices</div>
      <div class="text-2xl font-semibold mt-1">{{ $total }}</div>
    </div>
  </div>

  {{-- Table --}}
  <div class="rounded-2xl bg-white ring-1 ring-black/5 overflow-hidden">
    <table class="w-full">
      <thead class="bg-gray-50 text-xs uppercase tracking-wide text-gray-600">
        <tr>
          <th class="text-left px-4 py-3">Device</th>
          <th class="text-left px-4 py-3">Status</th>
          <th class="text-left px-4 py-3">Battery</th>
          <th class="text-left px-4 py-3">Last Sync</th>
          <th class="text-left px-4 py-3">App</th>
          <th class="text-left px-4 py-3">Location</th>
          <th class="text-right px-4 py-3">Actions</th>
        </tr>
      </thead>
      <tbody class="divide-y">
        @foreach($devices as $d)
          <tr class="hover:bg-gray-50">
            <td class="px-4 py-3">
              <div class="font-medium">{{ $d['name'] }}</div>
              <div class="text-xs text-gray-500">{{ $d['type'] }} • {{ $d['ip'] }}</div>
            </td>

            <td class="px-4 py-3">
              @if($d['status']==='online')
                <span class="inline-flex items-center gap-1 text-green-700 bg-green-50 px-2 py-1 rounded text-xs">● Online</span>
              @else
                <span class="inline-flex items-center gap-1 text-rose-700 bg-rose-50 px-2 py-1 rounded text-xs">● Offline</span>
              @endif
            </td>

            <td class="px-4 py-3">
              @if(is_null($d['battery']))
                <span class="text-xs text-gray-500">AC power</span>
              @else
                <div class="flex items-center gap-2">
                  <div class="w-24 h-2 rounded bg-gray-200">
                    <div class="h-2 rounded bg-emerald-500" style="width: {{ $d['battery'] }}%"></div>
                  </div>
                  <span class="text-sm">{{ $d['battery'] }}%</span>
                </div>
              @endif
            </td>

            <td class="px-4 py-3">{{ $d['last_sync'] }}</td>
            <td class="px-4 py-3">v{{ $d['app_version'] }}</td>
            <td class="px-4 py-3">{{ $d['location'] }}</td>

            <td class="px-4 py-3">
              <div class="flex justify-end gap-2 text-sm">
                <button type="button" class="px-3 py-1.5 rounded-lg bg-mint/40 hover:bg-mint/60">
                  Sync Now
                </button>

                <button type="button"
                        class="px-3 py-1.5 rounded-lg border hover:bg-gray-50"
                        @click="selected={{ json_encode($d) }}; openDetail=true">
                  View
                </button>

                <button type="button" class="px-3 py-1.5 rounded-lg bg-gray-100 text-gray-500 cursor-not-allowed"
                        title="Reboot (disabled in prototype)">
                  Reboot
                </button>
              </div>
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>

  {{-- Notes --}}
  <p class="text-xs text-gray-500">
    Catatan: pada produksi, status online/battery/last sync biasanya berasal dari heartbeat API perangkat.
  </p>

  {{-- ================= Register Device Modal ================= --}}
  <template x-teleport="body">
    <div x-show="openRegister"
         x-transition.opacity
         x-cloak
         @keydown.window.escape="openRegister=false"
         class="fixed inset-0 z-50 flex items-start justify-center">
      <div class="absolute inset-0 bg-black/40" @click="openRegister=false"></div>

      <div x-show="openRegister" x-transition
           class="relative mt-20 w-full max-w-xl rounded-2xl bg-white p-6 shadow-xl ring-1 ring-black/5"
           role="dialog" aria-modal="true">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-lg font-semibold">Register Device</h3>
          <button class="p-2 rounded hover:bg-gray-100" @click="openRegister=false">✕</button>
        </div>

        {{-- Dummy form – later: generate pairing code / QR for device app --}}
        <form class="grid grid-cols-1 gap-4" @submit.prevent="openRegister=false">
          <div>
            <label class="text-sm font-medium">Device Name</label>
            <input type="text" class="mt-1 w-full rounded-lg border-gray-200" placeholder="e.g., Handset-022" required>
          </div>
          <div>
            <label class="text-sm font-medium">Device Type</label>
            <select class="mt-1 w-full rounded-lg border-gray-200">
              <option>Handset</option>
              <option>Kiosk</option>
            </select>
          </div>
          <div>
            <label class="text-sm font-medium">Location</label>
            <select class="mt-1 w-full rounded-lg border-gray-200">
              <option value="">— Choose —</option>
              @foreach($locations as $loc)
                <option>{{ $loc }}</option>
              @endforeach
            </select>
          </div>

          <div class="rounded-lg bg-gray-50 border p-3 text-sm">
            <div class="font-medium mb-1">Pairing</div>
            <p>
              Pada versi produksi, klik <em>Generate Pairing Code</em> untuk menampilkan
              kode atau QR yang dipindai di aplikasi perangkat guna mendaftarkan device.
            </p>
            <button type="button" class="mt-2 px-3 py-1.5 rounded-lg bg-gray-200 hover:bg-gray-300">
              Generate Pairing Code
            </button>
          </div>

          <div class="mt-6 flex justify-end gap-2">
            <button type="button" class="rounded-full px-4 py-2 bg-gray-100 hover:bg-gray-200" @click="openRegister=false">Batal</button>
            <button type="submit" class="rounded-full px-4 py-2 bg-aqua text-white hover:opacity-90">Simpan</button>
          </div>
        </form>
      </div>
    </div>
  </template>

  {{-- ================= Device Detail (read-only) ================= --}}
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
          <h3 class="text-lg font-semibold" x-text="selected?.name || 'Device'"></h3>
          <button class="p-2 rounded hover:bg-gray-100" @click="openDetail=false">✕</button>
        </div>

        <div class="grid grid-cols-1 gap-3 text-sm">
          <div><span class="text-gray-500">Type:</span> <span x-text="selected?.type"></span></div>
          <div><span class="text-gray-500">IP:</span> <span x-text="selected?.ip"></span></div>
          <div><span class="text-gray-500">Status:</span>
            <span x-show="selected?.status==='online'" class="inline-flex items-center gap-1 text-green-700 bg-green-50 px-2 py-1 rounded text-xs">● Online</span>
            <span x-show="selected?.status==='offline'" class="inline-flex items-center gap-1 text-rose-700 bg-rose-50 px-2 py-1 rounded text-xs">● Offline</span>
          </div>
          <div><span class="text-gray-500">Battery:</span>
            <template x-if="selected?.battery === null"><span> AC power</span></template>
            <template x-if="selected?.battery !== null"><span x-text="` ${selected?.battery}%`"></span></template>
          </div>
          <div><span class="text-gray-500">Last Sync:</span> <span x-text="selected?.last_sync"></span></div>
          <div><span class="text-gray-500">App Version:</span> <span x-text="`v${selected?.app_version}`"></span></div>
          <div><span class="text-gray-500">Location:</span> <span x-text="selected?.location"></span></div>
          <div><span class="text-gray-500">Items Cached:</span> <span x-text="selected?.items_cached"></span></div>
        </div>

        <div class="mt-6 flex justify-end gap-2">
          <button class="px-3 py-1.5 rounded-lg border hover:bg-gray-50" @click="openDetail=false">Close</button>
          <button class="px-3 py-1.5 rounded-lg bg-mint/40 hover:bg-mint/60">Sync Now</button>
        </div>
      </div>
    </div>
  </template>
</section>
@endsection