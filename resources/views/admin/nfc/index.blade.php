@extends('layouts.admin')

@section('title','NFC Tags')
@section('page-title','NFC Tags')

@section('content')

<section x-data="{ openAssign:false, selectedTag:null }" x-cloak class="space-y-4">
  {{-- Top bar --}}
  <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
    <div class="flex-1">
      <h1 class="text-xl font-semibold">NFC Tags</h1>
      <p class="text-sm text-gray-500">Kelola kode tag NFC dan hubungannya dengan item koleksi.</p>
    </div>

    <div class="flex flex-col md:flex-row gap-2 md:items-center">
      <form method="GET" class="flex-1">
        <input type="text" name="q" value="{{ $q }}" placeholder="Cari kode atau nama item…" class="w-64 rounded-lg border-gray-200">
      </form>
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
        @forelse($tags as $tag)
          <tr class="hover:bg-gray-50">
            <td class="px-4 py-3 font-mono text-sm">{{ $tag->kode_tag }}</td>
            <td class="px-4 py-3">{{ $tag->item->nama_item ?? '—' }}</td>
            <td class="px-4 py-3">{{ $tag->item->lokasi_pameran ?? '—' }}</td>
            <td class="px-4 py-3">
              @if($tag->item_id)
                <span class="inline-flex items-center gap-1 text-green-700 bg-green-50 px-2 py-1 rounded text-xs">● Aktif</span>
              @else
                <span class="inline-flex items-center gap-1 text-gray-600 bg-gray-100 px-2 py-1 rounded text-xs">○ Belum dikaitkan</span>
              @endif
            </td>
            <td class="px-4 py-3">
              <div class="flex justify-end gap-2 text-sm">
                @if(!$tag->item_id)
                  <button type="button" class="px-3 py-1.5 rounded-lg bg-mint/40 hover:bg-mint/60"
                          @click="selectedTag={{ $tag->id }}; openAssign=true">
                    Kaitkan
                  </button>
                @else
                  <button type="button" 
                          class="px-3 py-1.5 rounded-lg border hover:bg-gray-50"
                          @click="selectedTag = {
                              id: {{ $tag->id }},
                              kode_tag: '{{ $tag->kode_tag }}',
                              item_id: {{ $tag->item_id }}
                          }; 
                          openAssign = true">
                    Edit
                  </button>
                  <form method="POST" action="{{ route('admin.nfc.destroy', $tag) }}" class="inline"
                        onsubmit="return confirm('Hapus tag NFC ini?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-3 py-1.5 rounded-lg bg-rose-50 text-rose-700 hover:bg-rose-100">
                      Hapus
                    </button>
                  </form>
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

  {{-- Pagination --}}
  <div class="mt-4">
    {{ $tags->links() }}
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
        <form x-bind:action="selectedTag 
        ? `{{ url('/admin/nfc-tags') }}/${selectedTag.id}`
        : '{{ route('admin.nfc.store') }}'"
              method="POST" 
              class="grid grid-cols-1 gap-4">
            @csrf
            <template x-if="selectedTag">
                @method('PATCH')
            </template>

            <div>
                <label class="text-sm font-medium">Kode Tag (UID)</label>
                <input type="text" 
                      name="kode_tag" 
                      placeholder="Contoh: 04A2246B9C21"
                      x-bind:value="selectedTag ? selectedTag.kode_tag : ''"
                      class="mt-1 w-full rounded-lg border-gray-200 font-mono text-sm" 
                      required>
            </div>

            <div>
                <label class="text-sm font-medium">Item yang Dikaitkan</label>
                <select name="item_id" 
                        class="mt-1 w-full rounded-lg border-gray-200" 
                        required>
                    <option value="">— Pilih item —</option>
                    @foreach($items as $item)
                        <option value="{{ $item->id }}"
                                x-bind:selected="selectedTag && selectedTag.item_id == {{ $item->id }}">
                            #{{ $item->id }} — {{ $item->nama_item }}
                        </option>                </option>
                    @endforeach
                </select>
            </div>

            <div class="mt-6 flex justify-end gap-2">
                <button type="button"     
                        class="rounded-full px-4 py-2 bg-gray-100 hover:bg-gray-200" 
                        @click="openAssign=false">Batal
                </button>
                <button type="submit" class="rounded-full px-4 py-2 bg-aqua text-white hover:opacity-90">
                    Simpan
                </button>
            </div>
        </form>
      </div>
    </div>
  </template>
</section>
@endsection