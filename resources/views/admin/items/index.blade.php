@extends('layouts.admin')

@section('title','Items')
@section('page-title','Items')

@section('content')
@php
    // Dummy data (replace with DB records later)
    $items = [
        [
            'id' => 1,
            'nama_item' => 'Meriam VOC abad XIX',
            'deskripsi' => 'Meriam besi dari era kolonial.',
            'kategori' => 'Senjata',
            'lokasi_pameran' => 'Galeri A',
            'tanggal_penambahan' => '2024-12-12',
            'audio_count' => 3,
            'nfc_count' => 1,
        ],
        [
            'id' => 2,
            'nama_item' => 'Diorama Perang Diponegoro',
            'deskripsi' => 'Diorama peristiwa sejarah.',
            'kategori' => 'Diorama',
            'lokasi_pameran' => 'Galeri B',
            'tanggal_penambahan' => '2025-01-05',
            'audio_count' => 2,
            'nfc_count' => 2,
        ],
        [
            'id' => 3,
            'nama_item' => 'Patung Jenderal Sudirman',
            'deskripsi' => 'Patung pahlawan nasional.',
            'kategori' => 'Patung',
            'lokasi_pameran' => 'Lobby',
            'tanggal_penambahan' => '2025-01-20',
            'audio_count' => 1,
            'nfc_count' => 1,
        ],
    ];

    // For the filter dropdowns (collect distincts in real app)
    $categories = ['Senjata','Diorama','Patung'];
    $locations  = ['Galeri A', 'Galeri B', 'Lobby'];
@endphp

<div x-data="{ openCreate:false }" class="space-y-4">

    {{-- Top bar: title, search, filters, create --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
        <div class="flex-1">
            <h1 class="text-xl font-semibold">Items</h1>
            <p class="text-sm text-gray-500">Kelola koleksi dan keterkaitannya (audio & NFC).</p>
        </div>

        <div class="flex flex-col md:flex-row gap-2 md:items-center">
            <div class="flex items-center gap-2">
                <input type="text" placeholder="Cari nama / deskripsi…" class="w-64 rounded-lg border-gray-200"
                       />
                <select class="rounded-lg border-gray-200">
                    <option value="">Semua Kategori</option>
                    @foreach($categories as $cat)
                        <option>{{ $cat }}</option>
                    @endforeach
                </select>
                <select class="rounded-lg border-gray-200">
                    <option value="">Semua Lokasi</option>
                    @foreach($locations as $loc)
                        <option>{{ $loc }}</option>
                    @endforeach
                </select>
            </div>

            <button @click="openCreate = true"
                    class="inline-flex items-center rounded-full bg-aqua text-white px-4 py-2 hover:opacity-90">
                + Item Baru
            </button>
        </div>
    </div>

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
            @forelse ($items as $it)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3">
                        <div class="font-medium">{{ $it['nama_item'] }}</div>
                        <div class="text-sm text-gray-500 line-clamp-1">{{ $it['deskripsi'] }}</div>
                    </td>
                    <td class="px-4 py-3">{{ $it['kategori'] }}</td>
                    <td class="px-4 py-3">{{ $it['lokasi_pameran'] }}</td>
                    <td class="px-4 py-3 text-right">{{ $it['audio_count'] }}</td>
                    <td class="px-4 py-3 text-right">{{ $it['nfc_count'] }}</td>
                    <td class="px-4 py-3">{{ \Carbon\Carbon::parse($it['tanggal_penambahan'])->isoFormat('D MMM Y') }}</td>
                    <td class="px-4 py-3">
                        <div class="flex justify-end gap-2">
                            {{-- Detail/Edit (placeholder) --}}
                            <a href="#" class="px-3 py-1.5 rounded-lg border text-sm text-aqua hover:bg-gray-50">Detail</a>

                            {{-- Manage Audio (go to audio page filtered by item) --}}
                            <a href="{{ route('admin.audio.index') }}?item={{ $it['id'] }}"
                               class="px-3 py-1.5 rounded-lg bg-mint/40 text-sm text-aquahover:bg-mint/60">Kelola Audio</a>

                            {{-- Manage NFC --}}
                            <a href="{{ route('admin.nfc.index') }}?item={{ $it['id'] }}"
                               class="px-3 py-1.5 rounded-lg bg-mint/20 text-sm text-aquahover:bg-mint/40">Kelola NFC</a>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-4 py-10 text-center text-gray-500">
                        Belum ada data item.
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
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

        <form class="grid grid-cols-1 gap-4" @submit.prevent="openCreate=false">
            @csrf
            <div>
            <label class="text-sm font-medium">Nama Item</label>
            <input type="text" class="mt-1 w-full rounded-lg border-gray-200" required>
            </div>

            <div>
            <label class="text-sm font-medium">Deskripsi</label>
            <textarea class="mt-1 w-full rounded-lg border-gray-200" rows="3"></textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="text-sm font-medium">Kategori</label>
                <input type="text" class="mt-1 w-full rounded-lg border-gray-200">
            </div>
            <div>
                <label class="text-sm font-medium">Lokasi Pameran</label>
                <input type="text" class="mt-1 w-full rounded-lg border-gray-200">
            </div>
            </div>

            <div>
            <label class="text-sm font-medium">Tanggal Penambahan</label>
            <input type="date" class="mt-1 w-full rounded-lg border-gray-200">
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