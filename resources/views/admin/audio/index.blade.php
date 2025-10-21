@extends('layouts.admin')

@section('title','Audio Files')
@section('page-title','Audio Files')

@section('content')
@php
  // Dummy data (ganti dengan data DB nanti)
  // ERD hint: AudioFile ↔ Item (prototype: 1–1)
  $items = [
    ['id'=>1,'nama'=>'Meriam VOC abad XIX','kategori'=>'Senjata','lokasi'=>'Galeri A'],
    ['id'=>2,'nama'=>'Diorama Perang Diponegoro','kategori'=>'Diorama','lokasi'=>'Galeri B'],
    ['id'=>3,'nama'=>'Patung Jenderal Sudirman','kategori'=>'Patung','lokasi'=>'Lobby'],
  ];

  // Dummy "audio files" joined to items (null => belum ada)
  $audio = [
    1 => ['filename'=>'meriam_voc_id.wav','lang'=>'id','duration'=>'01:42','size'=>'3.8 MB','storage'=>'s3','updated_at'=>'2025-01-21 14:12','in_sync'=>true],
    2 => null,
    3 => ['filename'=>'sudirman_id.wav','lang'=>'id','duration'=>'00:58','size'=>'2.2 MB','storage'=>'local','updated_at'=>'2025-01-19 10:03','in_sync'=>false],
  ];

  $selectedItemId = (int) request('item'); // dari Items page: ?item=ID
@endphp

<section
  x-data="{
    openUpload:false,
    // form state (prototype only)
    form: { item_id: {{ $selectedItemId ?: 'null' }}, lang: 'id', file: null, notes: '' },
    pick(itemId){ this.form.item_id = itemId; this.openUpload = true; }
  }"
  x-cloak
  class="space-y-5"
>
  {{-- Top bar --}}
  <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
    <div class="flex-1">
      <h1 class="text-xl font-semibold">Audio Files</h1>
      <p class="text-sm text-gray-500">Kelola berkas audio yang terhubung ke setiap item. (Prototipe: 1 audio per item, bahasa <em>ID</em>.)</p>
    </div>

    <div class="flex flex-col md:flex-row gap-2 md:items-center">
      {{-- Filters (dummy) --}}
      <div class="flex items-center gap-2">
        <form method="GET" class="flex items-center gap-2">
          <select name="item" class="rounded-lg border-gray-200">
            <option value="">Semua Item</option>
            @foreach($items as $it)
              <option value="{{ $it['id'] }}" @selected($selectedItemId === $it['id'])>
                #{{ $it['id'] }} — {{ $it['nama'] }}
              </option>
            @endforeach
          </select>
          <button class="px-3 py-2 rounded-lg border hover:bg-gray-50">Terapkan</button>
        </form>
      </div>

      <button type="button"
              @click="openUpload = true"
              class="inline-flex items-center rounded-full bg-aqua text-white px-4 py-2 hover:opacity-90">
        + Upload / Ganti Audio
      </button>
    </div>
  </div>

  {{-- Table --}}
  <div class="rounded-2xl bg-white ring-1 ring-black/5 overflow-hidden">
    <table class="w-full">
      <thead class="bg-gray-50 text-xs uppercase tracking-wide text-gray-600">
        <tr>
          <th class="text-left px-4 py-3">Item</th>
          <th class="text-left px-4 py-3">Kategori / Lokasi</th>
          <th class="text-left px-4 py-3">Filename</th>
          <th class="text-left px-4 py-3">Lang</th>
          <th class="text-left px-4 py-3">Durasi</th>
          <th class="text-left px-4 py-3">Ukuran</th>
          <th class="text-left px-4 py-3">Storage</th>
          <th class="text-left px-4 py-3">Update</th>
          <th class="text-left px-4 py-3">Sync</th>
          <th class="text-right px-4 py-3">Aksi</th>
        </tr>
      </thead>
      <tbody class="divide-y">
        @foreach($items as $it)
          @php $a = $audio[$it['id']] ?? null; @endphp
          {{-- Filter: jika ?item= terpilih --}}
          @if(!$selectedItemId || $selectedItemId === $it['id'])
            <tr class="hover:bg-gray-50">
              <td class="px-4 py-3">
                <div class="font-medium">#{{ $it['id'] }} — {{ $it['nama'] }}</div>
              </td>
              <td class="px-4 py-3">
                <div class="text-sm">{{ $it['kategori'] }}</div>
                <div class="text-xs text-gray-500">{{ $it['lokasi'] }}</div>
              </td>
              <td class="px-4 py-3">{{ $a['filename'] ?? '—' }}</td>
              <td class="px-4 py-3">{{ $a['lang'] ?? '—' }}</td>
              <td class="px-4 py-3">{{ $a['duration'] ?? '—' }}</td>
              <td class="px-4 py-3">{{ $a['size'] ?? '—' }}</td>
              <td class="px-4 py-3">{{ $a['storage'] ?? '—' }}</td>
              <td class="px-4 py-3">
                {{ $a['updated_at'] ?? '—' }}
              </td>
              <td class="px-4 py-3">
                @if($a)
                  @if($a['in_sync'])
                    <span class="inline-flex items-center gap-1 text-green-700 bg-green-50 px-2 py-1 rounded">✔ Sinkron</span>
                  @else
                    <span class="inline-flex items-center gap-1 text-amber-700 bg-amber-50 px-2 py-1 rounded">⟳ Perlu Sync</span>
                  @endif
                @else
                  <span class="inline-flex items-center gap-1 text-gray-600 bg-gray-100 px-2 py-1 rounded">—</span>
                @endif
              </td>
              <td class="px-4 py-3">
                <div class="flex justify-end gap-2 text-sm">
                  @if($a)
                    <button type="button"
                            class="px-3 py-1.5 rounded-lg border hover:bg-gray-50"
                            @click="pick({{ $it['id'] }})">Ganti</button>
                    <button type="button" class="px-3 py-1.5 rounded-lg border hover:bg-gray-50">Unduh</button>
                    <button type="button" class="px-3 py-1.5 rounded-lg bg-rose-50 text-rose-700 hover:bg-rose-100">Hapus</button>
                  @else
                    <button type="button"
                            class="px-3 py-1.5 rounded-lg bg-mint/40 hover:bg-mint/60"
                            @click="pick({{ $it['id'] }})">Upload</button>
                  @endif
                </div>
              </td>
            </tr>
          @endif
        @endforeach
      </tbody>
    </table>
  </div>

  {{-- Notes / Help --}}
  <div class="text-xs text-gray-500">
    <p>Catatan: prototipe ini menganggap 1 audio per item (bahasa: <strong>ID</strong>). Untuk pengembangan lanjut (multi-bahasa), kolom <em>lang</em> akan menjadi kunci tambahan (unique: item_id+lang) dan UI menambah dropdown bahasa & multi-record per item.</p>
  </div>

  {{-- =============== Upload / Replace Modal (prototype only) =============== --}}
  <template x-teleport="body">
    <div x-show="openUpload"
         x-transition.opacity
         x-cloak
         @keydown.window.escape="openUpload=false"
         class="fixed inset-0 z-50 flex items-start justify-center">
      <div class="absolute inset-0 bg-black/40" @click="openUpload=false"></div>

      <div x-show="openUpload" x-transition
           class="relative mt-20 w-full max-w-xl rounded-2xl bg-white p-6 shadow-xl ring-1 ring-black/5"
           role="dialog" aria-modal="true">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-lg font-semibold">Upload / Ganti Audio</h3>
          <button type="button" class="p-2 rounded hover:bg-gray-100" @click="openUpload=false">✕</button>
        </div>

        {{-- Prototype form: prevent submit, no backend yet --}}
        <form class="grid grid-cols-1 gap-4" @submit.prevent="openUpload=false">
          @csrf

          <div>
            <label class="text-sm font-medium">Item</label>
            <select x-model="form.item_id" class="mt-1 w-full rounded-lg border-gray-200" required>
              <option value="" disabled>Pilih item…</option>
              @foreach($items as $it)
                <option value="{{ $it['id'] }}">#{{ $it['id'] }} — {{ $it['nama'] }}</option>
              @endforeach
            </select>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="text-sm font-medium">Bahasa</label>
              <select x-model="form.lang" class="mt-1 w-full rounded-lg border-gray-200">
                <option value="id">Indonesia (id)</option>
                {{-- future ready: <option value="en">English (en)</option> --}}
              </select>
            </div>
            <div>
              <label class="text-sm font-medium">Berkas Audio (.wav)</label>
              <input type="file" accept=".wav,audio/wav" class="mt-1 w-full rounded-lg border-gray-200"
                     @change="form.file = $event.target.files[0]" required>
            </div>
          </div>

          <div>
            <label class="text-sm font-medium">Catatan (opsional)</label>
            <input type="text" x-model="form.notes" class="mt-1 w-full rounded-lg border-gray-200" placeholder="Contoh: revisi narasi, volume diperbaiki">
          </div>

          <div class="mt-6 flex items-center justify-between">
            <p class="text-xs text-gray-500">Setelah tersimpan, perangkat akan menandai item sebagai <em>Perlu Sync</em> hingga berhasil sinkron.</p>
            <div class="flex gap-2">
              <button type="button" class="rounded-full px-4 py-2 bg-gray-100 hover:bg-gray-200" @click="openUpload=false">Batal</button>
              <button type="submit" class="rounded-full px-4 py-2 bg-aqua text-white hover:opacity-90">Simpan (Dummy)</button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </template>
</section>
@endsection