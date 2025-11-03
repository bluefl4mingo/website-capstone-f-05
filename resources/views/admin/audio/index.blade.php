@extends('layouts.admin')

@section('title','Audio Files')
@section('page-title','Audio Files')

@section('content')
<section
  x-data="{
    openUpload:false,
    isEdit:false,          
    editId:null,

    form: { item_id: {{ $selectedItemId ?: 'null' }}, file: null, nama_file: '' },
    uploading: false,
    uploadProgress: 0,

    // open for NEW upload
    openNew(){
      this.isEdit=false;
      this.editId=null;
      this.form = { item_id: {{ $selectedItemId ?: 'null' }}, file: null, nama_file: '' };
      this.openUpload = true;
    },

    // open for REPLACE an existing audio
    openReplace(id, itemId, nama){
      this.isEdit=true;
      this.editId=id;
      this.form = { item_id: itemId, file: null, nama_file: nama };
      this.openUpload = true;
    }
  }"
  x-cloak
  class="space-y-5"
>
  {{-- Top bar --}}
  <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
    <div class="flex-1">
      <h1 class="text-xl font-semibold">Audio Files</h1>
      <p class="text-sm text-gray-500">Kelola berkas audio yang terhubung ke setiap item.</p>
    </div>

    <div class="flex flex-col md:flex-row gap-2 md:items-center">
      {{-- Filters --}}
      <div class="flex items-center gap-2">
        <form method="GET" class="flex items-center gap-2">
          <select name="item" class="rounded-lg border-gray-200">
            <option value="">Semua Item</option>
            @foreach($items as $item)
              <option value="{{ $item->id }}" @selected($selectedItemId === $item->id)>
                #{{ $item->id }} — {{ $item->nama_item }}
              </option>
            @endforeach
          </select>
          <button type="submit" class="px-3 py-2 rounded-lg border hover:bg-aqua/20">Terapkan</button>
        </form>
      </div>

      <div class="flex items-center gap-2">
        <a href="{{ route('admin.export.audio.all') }}" 
           class="inline-flex items-center rounded-full border border-aqua text-aqua px-4 py-2 hover:bg-aqua/30">
          <svg class="w-5 h-5 mr-1" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M12 4V16M12 16L8 12M12 16L16 12M6 20H18" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
          Download All
        </a>
        <button type="button"
                @click="openNew()"  
                class="inline-flex items-center rounded-full bg-aqua text-white px-4 py-2 hover:opacity-90">
          + Upload Audio
        </button>
      </div>
    </div>
  </div>

  {{-- Table --}}
  <div class="rounded-2xl bg-white ring-1 ring-black/5 overflow-hidden">
    <table class="w-full">
      <thead class="bg-gray-50 text-xs uppercase tracking-wide text-gray-600">
        <tr>
          <th class="text-center px-4 py-3 min-w-[300px]">Item</th>
          <th class="text-center px-4 py-3">Kategori / Lokasi</th>
          <th class="text-center px-4 py-3">Filename</th>
          <th class="text-center px-4 py-3">Format</th>
          <th class="text-center px-4 py-3">Durasi</th>
          <th class="text-center px-4 py-3">Storage</th>
          <th class="text-center px-4 py-3">Update</th>
          <th class="text-center px-4 py-3 min-w-[150px]">Sync</th>
          <th class="text-center px-4 py-3">Aksi</th>
        </tr>
      </thead>
      <tbody class="divide-y">
        @forelse($audioFiles as $audio)
          <tr class="hover:bg-gray-50">
            <td class="px-4 py-3 min-w-[300px]">
              <div class="font-medium">#{{ $audio->item_id }} — {{ $audio->item->nama_item ?? 'N/A' }}</div>
            </td>
            <td class="px-4 py-3">
              <div class="text-sm text-center">{{ $audio->item->kategori ?? '—' }}</div>
              <div class="text-xs text-center text-gray-500">{{ $audio->item->lokasi_pameran ?? '—' }}</div>
            </td>
            <td class="px-4 py-3">{{ $audio->nama_file }}</td>
            <td class="px-4 py-3 text-center">{{ strtoupper($audio->format_file ?? 'id') }}</td>
            <td class="px-4 py-3 text-center">{{ $audio->formatted_duration }}</td>
            <td class="px-4 py-3 text-center">{{ config('filesystems.default') }}</td>
            <td class="px-4 py-3">
              {{ $audio->updated_at ? $audio->updated_at->setTimezone('Asia/Jakarta')->format('Y-m-d H:i') : '—' }}
            </td>
            <td class="px-4 py-3 min-w-[150px] text-center">
              @php $status = $audio->sync_status ?? 'synced'; @endphp
              @if($status === 'pending')
                <span class="inline-flex items-center gap-1 text-amber-700 bg-amber-50 px-2 py-1 rounded">⏳Perlu Sync</span>
              @elseif($status === 'failed')
                <span class="inline-flex items-center gap-1 text-rose-700 bg-rose-50 px-2 py-1 rounded">⚠️ Gagal Sync</span>
              @else
                <span class="inline-flex items-center gap-1 text-green-700 bg-green-50 px-2 py-1 rounded">✔ Sinkron</span>
              @endif
            </td>
            <td class="px-4 py-3">
              <div class="flex justify-end gap-2 text-sm">
                <button type="button"
                  class="px-3 py-1.5 rounded-lg border hover:bg-mist/10"
                    @click="openReplace({{ $audio->id }}, {{ $audio->item_id }}, @js($audio->nama_file))">
                    Ganti
                </button>
                <a href="{{ route('admin.audio.download', $audio) }}" 
                   class="px-3 py-1.5 rounded-lg border hover:bg-aqua/10">
                    Unduh
                </a>
                <form method="POST" action="{{ route('admin.audio.destroy', $audio) }}" class="inline" 
                      onsubmit="return confirm('Hapus audio ini?')">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="px-3 py-1.5 rounded-lg bg-rose-50 text-rose-700 hover:bg-rose-100">Hapus</button>
                </form>
              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="10" class="px-4 py-10 text-center text-gray-500">
              Belum ada audio files.
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  {{-- Pagination --}}
  <div class="mt-4">
    {{ $audioFiles->links() }}
  </div>

  {{-- Notes / Help --}}
  <div class="text-xs text-gray-500">
    <p>Catatan: Prototipe ini menerapkan 1 audio per item.</p>
  </div>

  {{-- =============== Create / Replace Modal =============== --}}
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
          <h3 class="text-lg font-semibold" x-text="isEdit ? 'Ganti Audio' : 'Upload Audio'"></h3>
          <button type="button" class="p-2 rounded hover:bg-gray-100" @click="openUpload=false">✕</button>
        </div>

         {{-- Progress bar --}}
        <div x-show="uploading" x-cloak class="mb-4">
          <div class="flex items-center justify-between text-sm mb-2">
            <span class="font-medium text-gray-700">Uploading...</span>
            <span class="text-gray-600" x-text="`${uploadProgress}%`"></span>
          </div>
          <div class="w-full bg-gray-200 rounded-full h-2.5 overflow-hidden">
            <div class="bg-aqua h-2.5 rounded-full transition-all duration-300" 
                 :style="`width: ${uploadProgress}%`"></div>
          </div>
        </div>

        {{-- ONE form that switches action/method dynamically --}}
        <form
          method="POST"
          enctype="multipart/form-data"
          :action="isEdit
            ? '{{ url('/admin/audio') }}/' + editId      // PATCH /admin/audio/{id}
            : '{{ route('admin.audio.store') }}'         // POST  /admin/audio
          "
          class="grid grid-cols-1 gap-4"
          @submit="
            uploading = true;
            uploadProgress = 0;
            // simple simulated progress like the prototype
            let __pb = setInterval(() => {
              if (uploadProgress < 95) uploadProgress += 5;
            }, 160);
          "
        >
          @csrf
          {{-- spoof PATCH only on edit --}}
          <template x-if="isEdit">
            <input type="hidden" name="_method" value="PATCH">
          </template>

          <div>
            <label for="audio_item_id" class="text-sm font-medium">Item</label>
            <select id="audio_item_id" name="item_id"
                    x-model="form.item_id"
                    class="mt-1 w-full rounded-lg border-gray-200" 
                    :disabled="isEdit"
                    required>
              <option value="">— Pilih item —</option>
              @foreach($items as $item)
                <option value="{{ $item->id }}"
                  x-show="isEdit || {{ $item->audio_files_count === 0 ? 'true' : 'false' }}"
                  :disabled="!isEdit && {{ $item->audio_files_count > 0 ? 'true' : 'false' }}"
                >
                  #{{ $item->id }} — {{ $item->nama_item }}
                  @if($item->audio_files_count > 0 && false) (sudah ada audio) @endif
                </option>
              @endforeach
            </select>
            <template x-if="isEdit">
              <input type="hidden" name="item_id" x-bind:value="form.item_id">
            </template>
          </div>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label for="audio_nama_file" class="text-sm font-medium">Nama File</label>
              <input id="audio_nama_file" type="text" name="nama_file"
                    class="mt-1 w-full rounded-lg border-gray-200"
                    x-model="form.nama_file" required>
            </div>
            <div>
              <label for="audio_file" class="text-sm font-medium">
                Berkas Audio (.mp3, .wav, .ogg, .m4a)
                <span class="text-xs text-gray-500" x-show="isEdit">(opsional jika hanya ganti nama)</span>
              </label>
              <input id="audio_file" type="file" name="file"
                    accept=".wav,.mp3,.ogg,.m4a,audio/*"
                    class="mt-1 w-full border-gray-200"
                    @change="form.file = $event.target.files[0]"
                    :required="!isEdit">
            </div>
          </div>

          <div class="mt-6 flex items-center justify-between">
            <p class="text-xs text-gray-500">Setelah tersimpan, perangkat akan menandai item sebagai <em>Perlu Sync</em> hingga berhasil sinkron.</p>
            <div class="flex gap-2">
              <button type="button"
                      class="rounded-full px-4 py-2 bg-gray-100 hover:bg-gray-200"
                      @click="openUpload=false; uploading=false; uploadProgress=0"
                      :disabled="uploading">
                Batal
              </button>

              <button type="submit"
                      class="rounded-full px-4 py-2 bg-aqua text-white hover:opacity-90 disabled:opacity-50 disabled:cursor-not-allowed"
                      :disabled="uploading">
                <span x-show="!uploading">Upload</span>
                <span x-show="uploading">Uploading...</span>
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </template>
</section>
@endsection