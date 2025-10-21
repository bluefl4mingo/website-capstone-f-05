@extends('layouts.admin')

@section('title','NFC Tags')
@section('page-title','NFC Tags')

@section('content')
@php
  // Dummy data (replace with DB data later)
  $tags = [
    ['id'=>1, 'kode_tag'=>'04A2246B9C21', 'item'=>'Meriam VOC abad XIX', 'lokasi'=>'Galeri A', 'status'=>'Aktif'],
    ['id'=>2, 'kode_tag'=>'03B1158D7F91', 'item'=>'Diorama Perang Diponegoro', 'lokasi'=>'Galeri B', 'status'=>'Aktif'],
    ['id'=>3, 'kode_tag'=>'05C1987AB412', 'item'=>null, 'lokasi'=>null, 'status'=>'Belum dikaitkan'],
  ];

  $items = [
    ['id'=>1, 'nama'=>'Meriam VOC abad XIX'],
    ['id'=>2, 'nama'=>'Diorama Perang Diponegoro'],
    ['id'=>3, 'nama'=>'Patung Jenderal Sudirman'],
  ];
@endphp

<section x-data="{ openAssign:false, selectedTag:null }" x-cloak class="space-y-4">
  {{-- Top bar --}}
  <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
    <div class="flex-1">
      <h1 class="text-xl font-semibold">NFC Tags</h1>
      <p class="text-sm text-gray-500">Kelola kode tag NFC dan hubungannya dengan item koleksi.</p>
    </div>

    <div class="flex flex-col md:flex-row gap-2 md:items-center">
      <input type="text" placeholder="Cari kode atau nama item…" class="w-64 rounded-lg border-gray-200">
      <button type="button"
              @click="openAssign = true; selectedTag = null"
              class="inline-flex items-center rounded-full bg-aqua text-white px-4 py-2 hover:opacity-90">
        + Tambah Tag
      </button>
    </div>
  </div>

  {{-- Table --}}
  <div class="rounded-2xl bg-white ring-1 ring-black/5 overflow-hidden">
    <table class="w-full">
      <thead class="bg-gray-50 text-xs uppercase tracking-wide text-gray-600">
        <tr>
          <th class="text-left px-4 py-3">Kode Tag</th>
          <th class="text-left px-4 py-3">Item Terkait</th>
          <th class="text-left px-4 py-3">Lokasi</th>
          <th class="text-left px-4 py-3">Status</th>
          <th class="text-right px-4 py-3">Aksi</th>
        </tr>
      </thead>
      <tbody class="divide-y">
        @forelse($tags as $t)
          <tr class="hover:bg-gray-50">
            <td class="px-4 py-3 font-mono text-sm">{{ $t['kode_tag'] }}</td>
            <td class="px-4 py-3">{{ $t['item'] ?? '—' }}</td>
            <td class="px-4 py-3">{{ $t['lokasi'] ?? '—' }}</td>
            <td class="px-4 py-3">
              @if($t['status'] === 'Aktif')
                <span class="inline-flex items-center gap-1 text-green-700 bg-green-50 px-2 py-1 rounded text-xs">● Aktif</span>
              @else
                <span class="inline-flex items-center gap-1 text-gray-600 bg-gray-100 px-2 py-1 rounded text-xs">○ Belum dikaitkan</span>
              @endif
            </td>
            <td class="px-4 py-3">
              <div class="flex justify-end gap-2 text-sm">
                @if(!$t['item'])
                  <button type="button" class="px-3 py-1.5 rounded-lg bg-mint/40 hover:bg-mint/60"
                          @click="selectedTag={{ $t['id'] }}; openAssign=true">
                    Kaitkan
                  </button>
                @else
                  <button type="button" class="px-3 py-1.5 rounded-lg border hover:bg-gray-50"
                          @click="selectedTag={{ $t['id'] }}; openAssign=true">
                    Edit
                  </button>
                  <button type="button" class="px-3 py-1.5 rounded-lg bg-rose-50 text-rose-700 hover:bg-rose-100">
                    Hapus
                  </button>
                @endif
              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="5" class="px-4 py-10 text-center text-gray-500">Belum ada tag NFC.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  {{-- Notes --}}
  <p class="text-xs text-gray-500">
    Catatan: setiap tag NFC memiliki <em>Kode_Tag</em> unik. Sistem hanya mengizinkan satu item per tag.
  </p>

  {{-- =============== Add / Edit Modal =============== --}}
  <template x-teleport="body">
    <div x-show="openAssign"
         x-transition.opacity
         x-cloak
         @keydown.window.escape="openAssign=false"
         class="fixed inset-0 z-50 flex items-start justify-center">
      <div class="absolute inset-0 bg-black/40" @click="openAssign=false"></div>

      <div x-show="openAssign" x-transition
           class="relative mt-20 w-full max-w-xl rounded-2xl bg-white p-6 shadow-xl ring-1 ring-black/5"
           role="dialog" aria-modal="true">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-lg font-semibold" x-text="selectedTag ? 'Edit Tag' : 'Tambah Tag'"></h3>
          <button type="button" class="p-2 rounded hover:bg-gray-100" @click="openAssign=false">✕</button>
        </div>

        {{-- Dummy form --}}
        <form @submit.prevent="openAssign=false" class="grid grid-cols-1 gap-4">
          @csrf
          <div>
            <label class="text-sm font-medium">Kode Tag (UID)</label>
            <input type="text" placeholder="Contoh: 04A2246B9C21"
                   class="mt-1 w-full rounded-lg border-gray-200 font-mono text-sm" required>
          </div>

          <div>
            <label class="text-sm font-medium">Item yang Dikaitkan</label>
            <select class="mt-1 w-full rounded-lg border-gray-200">
              <option value="">— Pilih item —</option>
              @foreach($items as $i)
                <option value="{{ $i['id'] }}">#{{ $i['id'] }} — {{ $i['nama'] }}</option>
              @endforeach
            </select>
          </div>

          <div class="mt-6 flex justify-end gap-2">
            <button type="button" class="rounded-full px-4 py-2 bg-gray-100 hover:bg-gray-200" @click="openAssign=false">Batal</button>
            <button type="submit" class="rounded-full px-4 py-2 bg-aqua text-white hover:opacity-90">Simpan</button>
          </div>
        </form>
      </div>
    </div>
  </template>
</section>
@endsection