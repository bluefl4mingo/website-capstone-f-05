@extends('layouts.admin')

@section('title','Items')
@section('page-title','Items')

@section('content')
<div x-data="{ 
  openCreate: false, 
  openDetail: false, 
  openEdit: false, 
  detailItem: null, 
  editItem: null,
  processing: false,
  processingProgress: 0,
  
  closeCreateModal() {
    if(!this.processing) {
      this.openCreate = false;
      this.processing = false;
      this.processingProgress = 0;
      if(this.$refs.createForm) this.$refs.createForm.reset();
    }
  },
  
  closeEditModal() {
    if(!this.processing) {
      this.openEdit = false;
      this.processing = false;
      this.processingProgress = 0;
      this.editItem = null;
    }
  }
}" class="space-y-4">

    {{-- Top bar: title, search, filters, create --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
        <div class="flex-1">
            <h1 class="text-xl font-semibold">Items</h1>
            <p class="text-sm text-gray-500">Kelola koleksi dan data terkait (audio & NFC).</p>
        </div>

        <button @click="openCreate = true"
                class="inline-flex items-center rounded-full bg-aqua text-white px-4 py-2 hover:opacity-90">
            + Item Baru
        </button>
    </div>

    {{-- Filters Section --}}
    <div class="rounded-2xl bg-white ring-1 ring-black/5 p-4">
        <form method="GET" action="{{ route('admin.items.index') }}" class="flex flex-col md:flex-row gap-3 items-end" id="filterForm">
            <div class="flex-1">
                <label class="text-xs font-medium text-gray-600 uppercase">Cari</label>
                <input type="text" 
                       name="q" 
                       value="{{ $q }}" 
                       placeholder="Cari nama, deskripsi, kategori, atau lokasi…" 
                       class="mt-1 w-full rounded-lg border-gray-200"
                       autofocus />
            </div>
            
            <div class="w-full md:w-48">
                <label class="text-xs font-medium text-gray-600 uppercase">Kategori</label>
                <select name="category" class="mt-1 w-full rounded-lg border-gray-200">
                    <option value="">Semua Kategori</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat }}" @selected($category === $cat)>{{ $cat }}</option>
                    @endforeach
                </select>
            </div>
            
            <div class="w-full md:w-48">
                <label class="text-xs font-medium text-gray-600 uppercase">Lokasi</label>
                <select name="location" class="mt-1 w-full rounded-lg border-gray-200">
                    <option value="">Semua Lokasi</option>
                    @foreach($locations as $loc)
                        <option value="{{ $loc }}" @selected($location === $loc)>{{ $loc }}</option>
                    @endforeach
                </select>
            </div>
            
            <div class="flex gap-2">
                <button type="submit" class="px-4 py-2 rounded-lg bg-aqua text-white hover:opacity-90 whitespace-nowrap">
                    Terapkan Filter
                </button>
                @if($q || $category || $location)
                    <a href="{{ route('admin.items.index') }}" 
                       class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 whitespace-nowrap">
                        Reset
                    </a>
                @endif
            </div>
        </form>
        
        @if($q || $category || $location)
            <div class="mt-3 flex flex-wrap gap-2 items-center text-sm">
                <span class="text-gray-600">Filter aktif:</span>
                @if($q)
                    <span class="inline-flex items-center gap-1 bg-blue-50 text-blue-700 px-2 py-1 rounded">
                        Pencarian: "{{ $q }}"
                        <a href="{{ route('admin.items.index', array_filter(['category' => $category, 'location' => $location])) }}" class="hover:text-blue-900">✕</a>
                    </span>
                @endif
                @if($category)
                    <span class="inline-flex items-center gap-1 bg-green-50 text-green-700 px-2 py-1 rounded">
                        Kategori: {{ $category }}
                        <a href="{{ route('admin.items.index', array_filter(['q' => $q, 'location' => $location])) }}" class="hover:text-green-900">✕</a>
                    </span>
                @endif
                @if($location)
                    <span class="inline-flex items-center gap-1 bg-purple-50 text-purple-700 px-2 py-1 rounded">
                        Lokasi: {{ $location }}
                        <a href="{{ route('admin.items.index', array_filter(['q' => $q, 'category' => $category])) }}" class="hover:text-purple-900">✕</a>
                    </span>
                @endif
            </div>
        @endif
    </div>

    {{-- Results Summary --}}
    @if($items->total() > 0)
        <div class="flex items-center justify-between text-sm text-gray-600">
            <div>
                Menampilkan {{ $items->firstItem() }} - {{ $items->lastItem() }} dari {{ $items->total() }} items
                @if($q || $category || $location)
                    (terfilter)
                @endif
            </div>
        </div>
    @endif

    {{-- Table --}}
    <div class="rounded-2xl bg-white ring-1 ring-black/5 overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50 text-xs uppercase tracking-wide text-gray-600">
            <tr>
                <th class="text-center px-4 py-3 min-w-[300px]">Nama Item</th>
                <th class="text-center px-4 py-3">Kategori</th>
                <th class="text-center px-4 py-3 min-w-[120px]">Lokasi</th>
                <th class="text-center px-4 py-3">Audio</th>
                <th class="text-center px-4 py-3">NFC</th>
                <th class="text-center px-4 py-3">Ditambahkan</th>
                <th class="text-center px-4 py-3">Aksi</th>
            </tr>
            </thead>
            <tbody class="divide-y">
            @forelse ($items as $item)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3">
                        <div class="font-medium">{{ $item->nama_item }}</div>
                        <div class="text-sm text-gray-500 line-clamp-1">{{ $item->deskripsi }}</div>
                    </td>
                    <td class="px-4 py-3 text-center">{{ $item->kategori ?? '—' }}</td>
                    <td class="px-4 py-3 text-center">{{ $item->lokasi_pameran ?? '—' }}</td>
                    <td class="px-4 py-3 text-center">{{ $item->audio_files_count }}</td>
                    <td class="px-4 py-3 text-center">{{ $item->nfc_tags_count }}</td>
                    <td class="px-4 py-3 text-center">{{ $item->tanggal_penambahan ? \Carbon\Carbon::parse($item->tanggal_penambahan)->isoFormat('D MMM Y') : '—' }}</td>
                    <td class="px-4 py-3">
                        <div class="flex justify-end gap-2">
                            {{-- Detail --}}
                            <button
                            type="button"
                            class="flex items-center justify-center h-12 px-3 rounded-lg border text-sm text-aqua hover:bg-mint/20"
                            @click="
                                detailItem = {
                                id: {{ $item->id }},
                                nama_item: @js($item->nama_item),
                                deskripsi: @js($item->deskripsi),
                                kategori: @js($item->kategori),
                                lokasi_pameran: @js($item->lokasi_pameran),
                                tanggal_penambahan: @js(optional($item->tanggal_penambahan)->format('Y-m-d')),
                                created_at: @js(optional($item->created_at)->setTimezone('Asia/Jakarta')->format('Y-m-d H:i')),
                                updated_at: @js(optional($item->updated_at)->setTimezone('Asia/Jakarta')->format('Y-m-d H:i')),
                                audio_files_count: {{ $item->audio_files_count }},
                                nfc_tags_count: {{ $item->nfc_tags_count }},
                                audio_files: @js($item->audioFiles->map->only(['id','nama_file','format_file','lokasi_penyimpanan','created_at'])),
                                nfc_tags: @js($item->nfcTags->map->only(['id','kode_tag','created_at']))
                                };
                                openDetail = true;
                            "
                            >
                            Detail
                            </button>

                            {{-- Edit --}}
                            <button
                            type="button"
                            class="flex items-center justify-center min-w-[60px] h-12 px-3 rounded-lg border text-sm text-amber-600 hover:bg-amber-50"
                            @click="
                                editItem = {
                                id: {{ $item->id }},
                                nama_item: @js($item->nama_item),
                                deskripsi: @js($item->deskripsi),
                                kategori: @js($item->kategori),
                                lokasi_pameran: @js($item->lokasi_pameran),
                                tanggal_penambahan: new Date().toISOString().split('T')[0]
                                };
                                openEdit = true;
                            "
                            >
                            Edit
                            </button>

                            {{-- Manage Audio (go to audio page filtered by item) --}}
                            <a href="{{ route('admin.audio.index', ['item' => $item->id]) }}"
                               class="flex items-center text-center justify-center h-12 px-3 rounded-lg bg-mint/40 text-sm text-aqua hover:bg-mint/60">Kelola Audio</a>

                            {{-- Manage NFC --}}
                            <a href="{{ route('admin.nfc.index', ['item' => $item->id]) }}"
                               class="flex items-center text-center justify-center h-12 px-3 rounded-lg bg-mint/20 text-sm text-aqua hover:bg-mint/40">Kelola NFC</a>
                            {{-- Delete --}}
                            <form method="POST" action="{{ route('admin.items.destroy', $item->id) }}" onsubmit="return confirm('Yakin ingin menghapus item ini?');" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="flex items-center justify-center h-12 px-3 rounded-lg bg-red-100 text-sm text-red-700 hover:bg-red-200">
                                    Hapus
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-4 py-12 text-center">
                        <div class="text-gray-400 mb-2">
                            <svg class="mx-auto h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                            </svg>
                        </div>
                        @if($q || $category || $location)
                            <p class="text-gray-600 font-medium">Tidak ada item yang sesuai dengan filter</p>
                            <p class="text-sm text-gray-500 mt-1">Coba ubah atau reset filter untuk melihat lebih banyak data</p>
                            <a href="{{ route('admin.items.index') }}" class="inline-block mt-3 px-4 py-2 rounded-lg border border-gray-300 text-sm hover:bg-gray-50">
                                Reset Filter
                            </a>
                        @else
                            <p class="text-gray-600 font-medium">Belum ada data item</p>
                            <p class="text-sm text-gray-500 mt-1">Mulai dengan menambahkan item baru</p>
                            <button @click="openCreate = true" class="inline-block mt-3 px-4 py-2 rounded-lg bg-aqua text-white text-sm hover:opacity-90">
                                + Tambah Item
                            </button>
                        @endif
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    <div class="mt-4">
        {{ $items->links() }}
    </div>

    {{-- Create item --}}
    <template x-teleport="body">
    <div
        x-show="openCreate"
        x-transition.opacity
        class="fixed inset-0 z-50 flex items-start justify-center"
        x-cloak
    >
        {{-- Overlay (non-interactive) --}}
        <div class="absolute inset-0 bg-black/40"></div>

        {{-- Dialog --}}
        <div
        x-show="openCreate"
        x-transition
        class="relative mt-20 w-full max-w-xl rounded-2xl bg-white p-6 shadow-xl ring-1 ring-black/5"
        role="dialog"
        aria-modal="true"
        >
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold">Item Baru</h3>
            <button type="button" @click="closeCreateModal()" :disabled="processing" class="p-2 rounded hover:bg-gray-100 disabled:hover:bg-transparent" aria-label="Close">✕</button>
        </div>

        {{-- Progress Bar --}}
        <div x-show="processing" style="width: 100%" class="mb-4 h-1 bg-gray-200 rounded-full overflow-hidden">
            <div class="h-full bg-aqua animate-pulse"></div>
        </div>

        <form method="POST" action="{{ route('admin.items.store') }}" @submit="processing = true;" x-ref="createForm" class="grid grid-cols-1 gap-4">
            @csrf
            <div>
            <label class="text-sm font-medium">Nama Item</label>
            <input type="text" name="nama_item" class="mt-1 w-full rounded-lg border-gray-200" required>
            </div>

            <div>
            <label class="text-sm font-medium">Deskripsi</label>
            <textarea name="deskripsi" class="mt-1 w-full rounded-lg border-gray-200" rows="3"></textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="text-sm font-medium">Kategori</label>
                <input type="text" name="kategori" class="mt-1 w-full rounded-lg border-gray-200">
            </div>
            <div>
                <label class="text-sm font-medium">Lokasi Pameran</label>
                <input type="text" name="lokasi_pameran" class="mt-1 w-full rounded-lg border-gray-200">
            </div>
            </div>

            <div>
            <label class="text-sm font-medium">Tanggal Penambahan</label>
            <input type="date" name="tanggal_penambahan" class="mt-1 w-full rounded-lg border-gray-200" value="{{ date('Y-m-d') }}" required>
            </div>

            <div class="mt-6 flex justify-end gap-2">
            <button type="button" @click="closeCreateModal()" class="rounded-full px-4 py-2 bg-gray-100 hover:bg-gray-200 disabled:hover:bg-gray-100">
                Batal
            </button>
            <button type="submit" :disabled="processing" class="rounded-full px-4 py-2 bg-aqua text-white hover:opacity-90 disabled:hover:opacity-100">
                Simpan
            </button>
            </div>
        </form>
        </div>
    </div>
    </template>

    <template x-teleport="body">
    <div
        x-show="openDetail"
        x-transition.opacity
        x-cloak
        @keydown.window.escape="openDetail=false"
        class="fixed inset-0 z-50 flex items-start justify-center"
    >
        <div class="absolute inset-0 bg-black/40" @click="openDetail=false"></div>

        <div
        x-show="openDetail"
        x-transition
        class="relative mt-16 w-full max-w-3xl rounded-2xl bg-white p-6 shadow-xl ring-1 ring-black/5"
        role="dialog" aria-modal="true"
        >
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold">Detail Item</h3>
            <button class="p-2 rounded hover:bg-gray-100" @click="openDetail=false" aria-label="Close">✕</button>
        </div>

        <template x-if="detailItem">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left: basic info -->
            <div class="lg:col-span-1 space-y-3">
                <div>
                <div class="text-xs text-gray-500">Nama Item</div>
                <div class="font-medium" x-text="detailItem.nama_item"></div>
                </div>
                <div>
                <div class="text-xs text-gray-500">Deskripsi</div>
                <div class="text-sm text-gray-700" x-text="detailItem.deskripsi || '—'"></div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                <div>
                    <div class="text-xs text-gray-500">Kategori</div>
                    <div x-text="detailItem.kategori || '—'"></div>
                </div>
                <div>
                    <div class="text-xs text-gray-500">Lokasi</div>
                    <div x-text="detailItem.lokasi_pameran || '—'"></div>
                </div>
                <div>
                    <div class="text-xs text-gray-500">Ditambahkan</div>
                    <div x-text="detailItem.created_at || '—'"></div>
                </div>
                <div>
                    <div class="text-xs text-gray-500">Diperbarui</div>
                    <div x-text="detailItem.updated_at || '—'"></div>
                </div>
                </div>

                <div class="grid grid-cols-2 gap-3 pt-2">
                <div class="rounded-xl bg-mint/10 px-3 py-2">
                    <div class="text-xs text-gray-500 text-center">Audio</div>
                    <div class="text-lg text-center font-semibold" x-text="detailItem.audio_files_count"></div>
                </div>
                <div class="rounded-xl bg-mint/10 px-3 py-2">
                    <div class="text-xs text-gray-500 text-center">NFC</div>
                    <div class="text-lg text-center font-semibold" x-text="detailItem.nfc_tags_count"></div>
                </div>
                </div>
            </div>

            <!-- Right: lists -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Audio files -->
                <div>
                <div class="flex items-center justify-between mb-2">
                    <h4 class="font-semibold">Audio Files</h4>
                    <a
                    class="text-sm underline text-aqua hover:text-ink"
                    :href="'{{ route('admin.audio.index') }}?item=' + detailItem.id"
                    >Kelola Audio</a>
                </div>
                <div class="rounded-xl border border-gray-200 divide-y">
                    <template x-if="(detailItem.audio_files || []).length === 0">
                    <div class="px-3 py-3 text-sm text-gray-500">Belum ada audio.</div>
                    </template>
                    <template x-for="af in detailItem.audio_files" :key="af.id">
                    <div class="px-3 py-3 flex items-center justify-between">
                        <div>
                        <div class="font-medium" x-text="af.nama_file"></div>
                        <div class="text-xs text-gray-500">
                            <span x-text="(af.format_file || '').toUpperCase()"></span>
                            •
                            <span x-text="af.created_at"></span>
                        </div>
                        </div>
                        <div class="text-xs text-gray-500" x-text="af.lokasi_penyimpanan"></div>
                    </div>
                    </template>
                </div>
                </div>

                <!-- NFC tags -->
                <div>
                <div class="flex items-center justify-between mb-2">
                    <h4 class="font-semibold">NFC Tags</h4>
                    <a
                    class="text-sm underline text-aqua hover:text-ink"
                    :href="'{{ route('admin.nfc.index') }}?item=' + detailItem.id"
                    >Kelola NFC</a>
                </div>
                <div class="rounded-xl border border-gray-200 divide-y">
                    <template x-if="(detailItem.nfc_tags || []).length === 0">
                    <div class="px-3 py-3 text-sm text-gray-500">Belum ada NFC tag.</div>
                    </template>
                    <template x-for="tag in detailItem.nfc_tags" :key="tag.id">
                    <div class="px-3 py-3 flex items-center justify-between">
                        <div class="font-medium" x-text="tag.kode_tag"></div>
                        <div class="text-xs text-gray-500" x-text="tag.created_at"></div>
                    </div>
                    </template>
                </div>
                </div>
            </div>
            </div>
        </template>

        <div class="mt-6 text-right">
            <button class="rounded-lg px-4 py-2 bg-gray-100 hover:bg-gray-200" @click="openDetail=false">Close</button>
        </div>
        </div>
    </div>
    </template>

    {{-- Edit Item Modal --}}
    <template x-teleport="body">
    <div
        x-show="openEdit"
        x-transition.opacity
        class="fixed inset-0 z-50 flex items-start justify-center"
        x-cloak
    >
        {{-- Overlay --}}
        <div class="absolute inset-0 bg-black/40"></div>

        {{-- Dialog --}}
        <div
        x-show="openEdit"
        x-transition
        class="relative mt-20 w-full max-w-xl rounded-2xl bg-white p-6 shadow-xl ring-1 ring-black/5"
        role="dialog"
        aria-modal="true"
        >
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold">Edit Item</h3>
            <button class="p-2 rounded hover:bg-gray-100 disabled:hover:bg-transparent" @click="if(!processing) { openEdit = false; processing = false; processingProgress = 0; }" :disabled="processing" aria-label="Close">✕</button>
        </div>

        {{-- Progress bar --}}
        <div x-show="processing" x-cloak class="mb-4">
          <div class="flex items-center justify-between text-sm mb-2">
            <span class="font-medium text-gray-700">Processing...</span>
            <span class="text-gray-600">...</span>
          </div>
          <div class="w-full bg-gray-200 rounded-full h-2.5 overflow-hidden">
            <div class="bg-aqua h-2.5 rounded-full transition-all duration-300 animate-pulse" 
                 style="width: 100%"></div>
          </div>
        </div>

        <template x-if="editItem">
        <form method="POST" 
              :action="`{{ url('/admin/items') }}/${editItem.id}`" 
              class="grid grid-cols-1 gap-4"
              @submit="processing = true;">
            @csrf
            @method('PATCH')
            
            <div>
            <label class="text-sm font-medium">Nama Item</label>
            <input type="text" name="nama_item" x-model="editItem.nama_item" class="mt-1 w-full rounded-lg border-gray-200" required>
            </div>

            <div>
            <label class="text-sm font-medium">Deskripsi</label>
            <textarea name="deskripsi" x-model="editItem.deskripsi" class="mt-1 w-full rounded-lg border-gray-200" rows="3"></textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="text-sm font-medium">Kategori</label>
                <input type="text" name="kategori" x-model="editItem.kategori" class="mt-1 w-full rounded-lg border-gray-200">
            </div>
            <div>
                <label class="text-sm font-medium">Lokasi Pameran</label>
                <input type="text" name="lokasi_pameran" x-model="editItem.lokasi_pameran" class="mt-1 w-full rounded-lg border-gray-200">
            </div>
            </div>

            <div>
            <label class="text-sm font-medium">Tanggal Perubahan</label>
            <input type="date" name="tanggal_penambahan" x-model="editItem.tanggal_penambahan" class="mt-1 w-full rounded-lg border-gray-200" required>
            </div>

            <div class="mt-6 flex justify-end gap-2">
            <button type="button" class="rounded-full px-4 py-2 bg-gray-100 hover:bg-gray-200" @click="openEdit=false">
                Batal
            </button>
            <button type="submit" class="rounded-full px-4 py-2 bg-amber-600 text-white hover:bg-amber-700" :disabled="processing">
                <span x-show="!processing">Simpan Perubahan</span>
                <span x-show="processing">Processing...</span>
            </button>
            </div>
        </form>
        </template>
        </div>
    </div>
    </template>

</div>
@endsection