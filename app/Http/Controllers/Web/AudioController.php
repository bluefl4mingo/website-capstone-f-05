<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAudioRequest;
use App\Http\Requests\UpdateAudioRequest;
use App\Models\Audio;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AudioController extends Controller
{
    public function index()
    {
        $audios = Audio::with('category')->latest()->paginate(15);
        return view('audio.index', compact('audios'));
    }

    public function create()
    {
        $categories = Category::orderBy('name')->get();
        return view('audio.create', compact('categories'));
    }

    public function store(StoreAudioRequest $request)
    {
        // (opsional) otorisasi jika pakai Policy
        // $this->authorize('create', Audio::class);

        $file = $request->file('file');
        $extension = $file->guessExtension() ?: $file->getClientOriginalExtension();
        $blob = 'audios/'.Str::uuid().'.'.$extension;

        DB::beginTransaction();
        try {
            // A) Cara sederhana (baik untuk file kecil–sedang)
            Storage::disk('gcs')->put($blob, file_get_contents($file->getRealPath()));

            // B) Alternatif hemat memori (untuk file besar)
            // $stream = fopen($file->getRealPath(), 'r');
            // Storage::disk('gcs')->writeStream($blob, $stream);
            // fclose($stream);

            Audio::create([
                'title'        => $request->string('title'),
                'description'  => $request->string('description'),
                'category_id'  => $request->input('category_id'),
                'storage_path' => $blob,
                'mime_type'    => $file->getMimeType(),
                'size_bytes'   => $file->getSize(),
            ]);

            DB::commit();

            return redirect()
                ->route('audios.index')
                ->with('status', 'Audio berhasil diunggah ke Google Cloud Storage.');
        } catch (\Throwable $e) {
            DB::rollBack();
            // Bersihkan blob jika sempat terunggah tapi DB gagal
            try { Storage::disk('gcs')->delete($blob); } catch (\Throwable $ignored) {}
            return back()->withErrors(['file' => 'Gagal mengunggah: '.$e->getMessage()])->withInput();
        }
    }

    public function edit(Audio $audio)
    {
        $categories = Category::orderBy('name')->get();
        return view('audio.edit', compact('audio','categories'));
    }

    public function update(UpdateAudioRequest $request, Audio $audio)
    {
        // $this->authorize('update', $audio);

        DB::beginTransaction();
        try {
            // Jika ada file baru, unggah & hapus yang lama
            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $ext  = $file->guessExtension() ?: $file->getClientOriginalExtension();
                $newBlob = 'audios/'.Str::uuid().'.'.$ext;

                Storage::disk('gcs')->put($newBlob, file_get_contents($file->getRealPath()));
                // hapus lama (jika ada)
                if ($audio->storage_path) {
                    Storage::disk('gcs')->delete($audio->storage_path);
                }

                $audio->storage_path = $newBlob;
                $audio->mime_type    = $file->getMimeType();
                $audio->size_bytes   = $file->getSize();
            }

            $audio->title        = $request->string('title');
            $audio->description  = $request->string('description');
            $audio->category_id  = $request->input('category_id');
            $audio->save();

            DB::commit();
            return redirect()->route('audios.index')->with('status', 'Audio berhasil diperbarui.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withErrors(['file' => 'Gagal memperbarui: '.$e->getMessage()])->withInput();
        }
    }

    public function destroy(Audio $audio)
    {
        // $this->authorize('delete', $audio);

        DB::beginTransaction();
        try {
            if ($audio->storage_path) {
                Storage::disk('gcs')->delete($audio->storage_path);
            }
            $audio->delete();
            DB::commit();

            return back()->with('status', 'Audio dihapus.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withErrors(['file' => 'Gagal menghapus: '.$e->getMessage()]);
        }
    }
}
