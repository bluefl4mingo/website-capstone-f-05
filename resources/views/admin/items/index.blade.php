@extends('layouts.admin')

@section('title','Items')
@section('page-title','Items')

@section('content')
<div x-data="{ openCreate:false }" class="space-y-4">

    {{-- Top bar: title, search, filters, create --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
        <div class="flex-1">
            <h1 class="text-xl font-semibold">Items</h1>
            <p class="text-sm text-gray-500">Kelola koleksi dan keterkaitannya (audio & NFC).</p>
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
                <th class="text-left px-4 py-3">Nama Item</th>
                <th class="text-left px-4 py-3">Kategori</th>
                <th class="text-left px-4 py-3">Lokasi</th>
                <th class="text-right px-4 py-3">Audio</th>
                <th class="text-right px-4 py-3">NFC</th>
                <th class="text-left px-4 py-3">Ditambahkan</th>
                <th class="text-right px-4 py-3">Aksi</th>
            </tr>
            </thead>
            <tbody class="divide-y">
            @forelse ($items as $item)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3">
                        <div class="font-medium">{{ $item->nama_item }}</div>
                        <div class="text-sm text-gray-500 line-clamp-1">{{ $item->deskripsi }}</div>
                    </td>
                    <td class="px-4 py-3">{{ $item->kategori ?? '—' }}</td>
                    <td class="px-4 py-3">{{ $item->lokasi_pameran ?? '—' }}</td>
                    <td class="px-4 py-3 text-right">{{ $item->audio_files_count }}</td>
                    <td class="px-4 py-3 text-right">{{ $item->nfc_tags_count }}</td>
                    <td class="px-4 py-3">{{ $item->tanggal_penambahan ? \Carbon\Carbon::parse($item->tanggal_penambahan)->isoFormat('D MMM Y') : '—' }}</td>
                    <td class="px-4 py-3">
                        <div class="flex justify-end gap-2">
                            {{-- Detail/Edit (placeholder) --}}
                            <button type="button" class="px-3 py-1.5 rounded-lg border text-sm text-aqua hover:bg-gray-50">Detail</button>

                            {{-- Manage Audio (go to audio page filtered by item) --}}
                            <a href="{{ route('admin.audio.index', ['item' => $item->id]) }}"
                               class="px-3 py-1.5 rounded-lg bg-mint/40 text-sm text-aquahover:bg-mint/60">Kelola Audio</a>

                            {{-- Manage NFC --}}
                            <a href="{{ route('admin.nfc.index', ['item' => $item->id]) }}"
                               class="px-3 py-1.5 rounded-lg bg-mint/20 text-sm text-aquahover:bg-mint/40">Kelola NFC</a>
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

    {{-- Simple create modal (no backend yet) --}}
    <template x-teleport="body">
    <div
        x-show="openCreate"
        x-transition.opacity
        @keydown.window.escape="openCreate = false"
        class="fixed inset-0 z-50 flex items-start justify-center"
        x-cloak
    >
        {{-- Overlay (click to close) --}}
        <div class="absolute inset-0 bg-black/40" @click="openCreate = false"></div>

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
            <button class="p-2 rounded hover:bg-gray-100" @click="openCreate=false" aria-label="Close">✕</button>
        </div>

        <form method="POST" action="{{ route('admin.items.store') }}" class="grid grid-cols-1 gap-4">
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
            <button type="button" class="rounded-full px-4 py-2 bg-gray-100 hover:bg-gray-200" @click="openCreate=false">
                Batal
            </button>
            <button type="submit" class="rounded-full px-4 py-2 bg-aqua text-white hover:opacity-90">
                Simpan
            </button>
            </div>
        </form>
        </div>
    </div>
    </template>

</div>
@endsection