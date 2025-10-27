<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\AudioFile;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AudioFileController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $selectedItemId = (int) $request->get('item', 0);

        $items = Item::orderBy('nama_item')
            ->withCount('audioFiles')
            ->get(['id', 'nama_item', 'kategori', 'lokasi_pameran']);

        // Get audio files with their related items
        $audioFiles = AudioFile::query()
            ->with('item:id,nama_item,kategori,lokasi_pameran')
            ->when($selectedItemId > 0, function ($query) use ($selectedItemId) {
                $query->where('item_id', $selectedItemId);
            })
            ->latest('created_at')
            ->paginate(15)
            ->withQueryString();

        return view('admin.audio.index', compact('items', 'audioFiles', 'selectedItemId'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'item_id' => ['required', 'exists:items,id'],
            'nama_file' => ['required', 'string', 'max:255'],
            'file' => ['required', 'file', 'mimes:mp3,wav,ogg,m4a', 'max:10240'], // max 10MB
        ]);

        return DB::transaction(function () use ($request, $validated) {
            $file = $request->file('file');
            $ext = $file->guessExtension() ?: $file->getClientOriginalExtension();
            $blob = 'audios/' . Str::uuid() . '.' . $ext;

            // Extract audio duration
            $duration = $this->extractAudioDuration($file);

            // Upload to storage
            Storage::disk(config('filesystems.default'))->put($blob, file_get_contents($file->getRealPath()));

            $audio = AudioFile::create([
                'item_id' => $validated['item_id'],
                'nama_file' => $validated['nama_file'],
                'format_file' => $ext,
                'durasi' => $duration,
                'lokasi_penyimpanan' => $blob,
                'tanggal_upload' => now(),
            ]);

            ActivityLog::create([
                'user_id' => auth()->id(),
                'aktivitas' => 'upload_audio',
                'waktu_aktivitas' => now(),
                'context' => [
                    'audio_id' => $audio->id,
                    'item_id' => $audio->item_id,
                    'nama_file' => $audio->nama_file,
                    'durasi' => $duration,
                ],
            ]);

            return redirect()
                ->route('admin.audio.index')
                ->with('status', 'Audio berhasil diunggah.');
        });
    }

    /**
     * Extract audio file duration using getID3 library
     * 
     * @param \Illuminate\Http\UploadedFile $file
     * @return int|null Duration in seconds
     */
    private function extractAudioDuration($file): ?int
    {
        try {
            $getID3 = new \getID3;
            $fileInfo = $getID3->analyze($file->getRealPath());
            
            if (isset($fileInfo['playtime_seconds'])) {
                return (int) round($fileInfo['playtime_seconds']);
            }
            
            return null;
        } catch (\Exception $e) {
            \Log::warning('Failed to extract audio duration: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, AudioFile $audioFile)
    {
        $validated = $request->validate([
            'nama_file' => ['required', 'string', 'max:255'],
            'item_id' => ['required', 'exists:items,id'],
        ]);

        return DB::transaction(function () use ($audioFile, $validated) {
            $audioFile->update($validated);

            ActivityLog::create([
                'user_id' => auth()->id(),
                'aktivitas' => 'update_audio',
                'waktu_aktivitas' => now(),
                'context' => [
                    'audio_id' => $audioFile->id,
                    'nama_file' => $audioFile->nama_file,
                ],
            ]);

            return redirect()
                ->route('admin.audio.index')
                ->with('status', 'Audio berhasil diperbarui.');
        });
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AudioFile $audioFile)
    {
        return DB::transaction(function () use ($audioFile) {
            // Delete file from storage
            if ($audioFile->lokasi_penyimpanan) {
                Storage::disk(config('filesystems.default'))->delete($audioFile->lokasi_penyimpanan);
            }

            $audioId = $audioFile->id;
            $namaFile = $audioFile->nama_file;

            ActivityLog::create([
                'user_id' => auth()->id(),
                'aktivitas' => 'delete_audio',
                'waktu_aktivitas' => now(),
                'context' => [
                    'audio_id' => $audioId,
                    'nama_file' => $namaFile,
                ],
            ]);

            $audioFile->delete();

            return redirect()
                ->route('admin.audio.index')
                ->with('status', 'Audio berhasil dihapus.');
        });
    }
}
