@extends('layouts.admin')

@section('title','Dashboard')

@section('content')
@php
  use App\Models\Item;
  use App\Models\AudioFile;
  use App\Models\NfcTag;
  use Illuminate\Support\Facades\DB;

  // --- KPIs ---
  $totalItems      = Item::count();
  $totalAudio      = AudioFile::count();

  // --- Coverage (how many items already have…) ---
  $itemsWithAudio  = Item::has('audioFiles')->count();
  $itemsWithNfc    = Item::has('nfcTags')->count();

  $pctAudio = $totalItems ? round(($itemsWithAudio / $totalItems) * 100) : 0;
  $pctNfc   = $totalItems ? round(($itemsWithNfc   / $totalItems) * 100) : 0;

  // --- Completeness (gaps to fix) ---
  $itemsNoAudio = $totalItems - $itemsWithAudio;
  $itemsNoNfc   = $totalItems - $itemsWithNfc;

  // --- Recent activity (not realtime; just latest records) ---
  $recentItems = Item::latest('created_at')->take(5)->get(['id','nama_item','kategori','lokasi_pameran','created_at']);
  $recentAudio = AudioFile::with('item:id,nama_item')
                  ->latest('created_at')
                  ->take(5)
                  ->get(['id','item_id','nama_file','format_file','durasi','created_at']);

  // --- Breakdown by category (top 6) ---
  $byCategory = Item::select('kategori', DB::raw('COUNT(*) as total'))
                  ->groupBy('kategori')
                  ->orderByDesc('total')
                  ->take(6)
                  ->get();
@endphp

{{-- KPI tiles --}}
<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
  {{-- Items --}}
  <div class="rounded-2xl bg-white ring-1 ring-black/5 p-5 flex items-center gap-4">
    <div class="text-2xl">⚱️</div>
    <div>
      <div class="text-2xl font-semibold">{{ number_format($totalItems) }}</div>
      <div class="text-sm text-gray-500">Items</div>
      <div class="text-xs text-gray-400">total koleksi</div>
    </div>
  </div>

  {{-- Audio Tracks --}}
  <div class="rounded-2xl bg-white ring-1 ring-black/5 p-5 flex items-center gap-4">
    <div class="text-2xl">🎧</div>
    <div>
      <div class="text-2xl font-semibold">{{ number_format($totalAudio) }}</div>
      <div class="text-sm text-gray-500">Audio Tracks</div>
      <div class="text-xs text-gray-400">semua bahasa</div>
    </div>
  </div>

  {{-- Coverage: Items w/ Audio --}}
  <div class="rounded-2xl bg-white ring-1 ring-black/5 p-5">
    <div class="text-sm text-gray-500 mb-1">Cakupan Audio</div>
    <div class="flex items-end justify-between">
      <div class="text-2xl font-semibold">{{ $pctAudio }}%</div>
      <div class="text-xs text-gray-400">{{ $itemsWithAudio }} dari {{ $totalItems }}</div>
    </div>
    <div class="mt-2 w-full h-2 bg-gray-100 rounded-full overflow-hidden">
      <div class="h-2 bg-[#89BBB0]" style="width: {{ $pctAudio }}%"></div>
    </div>
  </div>

  {{-- Coverage: Items w/ NFC --}}
  <div class="rounded-2xl bg-white ring-1 ring-black/5 p-5">
    <div class="text-sm text-gray-500 mb-1">Cakupan NFC</div>
    <div class="flex items-end justify-between">
      <div class="text-2xl font-semibold">{{ $pctNfc }}%</div>
      <div class="text-xs text-gray-400">{{ $itemsWithNfc }} dari {{ $totalItems }}</div>
    </div>
    <div class="mt-2 w-full h-2 bg-gray-100 rounded-full overflow-hidden">
      <div class="h-2 bg-[#89BBB0]" style="width: {{ $pctNfc }}%"></div>
    </div>
  </div>
</div>

{{-- Overview panels (no realtime) --}}
<div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mt-6">
  {{-- Recently Added --}}
  <div class="xl:col-span-2 grid grid-cols-1 lg:grid-cols-2 gap-6">
    {{-- Recent Items --}}
    <div class="rounded-2xl bg-white ring-1 ring-black/5 p-5">
      <h2 class="font-semibold mb-3">Item Terbaru</h2>
      <ul class="divide-y text-sm">
        @forelse($recentItems as $it)
          <li class="py-2">
            <div class="font-medium">{{ $it->nama_item }}</div>
            <div class="text-xs text-gray-500">
              {{ $it->kategori ?? '—' }} • {{ $it->lokasi_pameran ?? '—' }} •
              {{ optional($it->created_at)->format('Y-m-d H:i') }}
            </div>
          </li>
        @empty
          <li class="py-2 text-gray-500">Belum ada item.</li>
        @endforelse
      </ul>
      <div class="mt-3 text-right">
        <a href="{{ route('admin.items.index') }}" class="text-sm underline text-aqua hover:text-ink">Kelola Items</a>
      </div>
    </div>

    {{-- Recent Audio Uploads --}}
    <div class="rounded-2xl bg-white ring-1 ring-black/5 p-5">
      <h2 class="font-semibold mb-3">Audio Terbaru</h2>
      <ul class="divide-y text-sm">
        @forelse($recentAudio as $af)
          <li class="py-2">
            <div class="font-medium">{{ $af->nama_file }}</div>
            <div class="text-xs text-gray-500">
              #{{ $af->item_id }} — {{ $af->item->nama_item ?? 'N/A' }}
              • {{ strtoupper($af->format_file) }}
              • {{ $af->durasi ? gmdate('i:s', $af->durasi) : '—' }}
              • {{ optional($af->created_at)->format('Y-m-d H:i') }}
            </div>
          </li>
        @empty
          <li class="py-2 text-gray-500">Belum ada unggahan audio.</li>
        @endforelse
      </ul>
      <div class="mt-3 text-right">
        <a href="{{ route('admin.audio.index') }}" class="text-sm underline text-aqua hover:text-ink">Kelola Audio</a>
      </div>
    </div>
  </div>

  {{-- Right column: Completeness + Breakdown + Quick Actions --}}
  <div class="space-y-6">
    {{-- Data completeness --}}
    <div class="rounded-2xl bg-white ring-1 ring-black/5 p-5">
      <h2 class="font-semibold mb-3">Kelengkapan Data</h2>
      <ul class="text-sm space-y-2">
        <li class="flex items-center justify-between">
          <span>Item tanpa audio</span>
          <span class="font-semibold">{{ $itemsNoAudio }}</span>
        </li>
        <li class="flex items-center justify-between">
          <span>Item tanpa NFC tag</span>
          <span class="font-semibold">{{ $itemsNoNfc }}</span>
        </li>
      </ul>
      <div class="mt-3 grid grid-cols-2 gap-2">
        <a href="{{ route('admin.audio.index') }}" class="rounded-lg border px-3 py-2 text-center text-green-700 hover:bg-mint/50 text-sm">Tambah Audio</a>
        <a href="{{ route('admin.nfc.index') }}"   class="rounded-lg border px-3 py-2 text-center text-green-700 hover:bg-mint/50 text-sm">Set NFC</a>
      </div>
    </div>

    {{-- Category breakdown --}}
    <div class="rounded-2xl bg-white ring-1 ring-black/5 p-5">
      <h2 class="font-semibold mb-3">Ringkasan Kategori</h2>
      <table class="w-full text-sm">
        <tbody class="divide-y">
          @forelse($byCategory as $row)
            <tr>
              <td class="py-2">{{ $row->kategori ?: '—' }}</td>
              <td class="py-2 text-right font-medium">{{ $row->total }}</td>
            </tr>
          @empty
            <tr><td class="py-2 text-gray-500">Belum ada data.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>

    {{-- Quick actions --}}
    <div class="rounded-2xl bg-white ring-1 ring-black/5 p-5">
      <h2 class="font-semibold mb-3">Quick Actions</h2>
      <div class="grid grid-cols-3 gap-2">
        <a href="{{ route('admin.items.index') }}" class="rounded-xl bg-mint/50 px-3 py-2 text-center text-green-700 hover:bg-mint/70">New Item</a>
        <a href="{{ route('admin.audio.index') }}" class="rounded-xl bg-mint/50 px-3 py-2 text-center text-green-700 hover:bg-mint/70">Upload Audio</a>
        <a href="{{ route('admin.export.nfc.mappings') }}" class="rounded-xl bg-mint/50 px-3 py-2 text-center text-green-700 hover:bg-mint/70">Export Mapping</a>
      </div>
    </div>
  </div>
</div>
@endsection