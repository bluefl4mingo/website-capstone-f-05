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
use Google\Cloud\Storage\StorageClient;


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
            'item_id'   => ['required', 'exists:items,id'],
            'nama_file' => ['required', 'string', 'max:255'],
            'file'      => ['required', 'file', 'mimes:mp3,wav,ogg,m4a', 'max:10240'],
        ]);

        if (AudioFile::where('item_id', $validated['item_id'])->exists()) {
            return back()
                ->withErrors(['item_id' => 'Item tersebut sudah memiliki audio. Silakan gunakan aksi "Ganti" pada audio yang ada.'])
                ->withInput();
        }

        $disk = 'gcs';
        return DB::transaction(function () use ($request, $validated, $disk) {
            $file = $request->file('file');
            $ext  = $file->guessExtension() ?: $file->getClientOriginalExtension();
            $blob = 'audios/' . Str::uuid() . '.' . $ext;

            $duration = $this->extractAudioDuration($file);

            Storage::disk($disk)->put($blob, file_get_contents($file->getRealPath()));

            $audio = AudioFile::create([
                'item_id'            => $validated['item_id'],
                'nama_file'          => $validated['nama_file'],
                'format_file'        => $ext,
                'durasi'             => $duration,
                'lokasi_penyimpanan' => $blob,
                'tanggal_upload'     => now(),
                'sync_status'        => 'pending', 
                'sync_version'       => 1,
            ]);

            ActivityLog::create([
                'user_id'         => auth()->id(),
                'aktivitas'       => 'upload_audio',
                'waktu_aktivitas' => now(),
                'context'         => [
                    'audio_id'  => $audio->id,
                    'item_id'   => $audio->item_id,
                    'nama_file' => $audio->nama_file,
                    'durasi'    => $duration,
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
     * Update (replace) an existing audio file.
     * Upload new → update DB → delete old → log.
     */
    public function update(Request $request, AudioFile $audioFile)
    {
        $validated = $request->validate([
            'nama_file' => ['required', 'string', 'max:255'],
            'item_id'   => ['required', 'exists:items,id'],
            'file'      => ['nullable', 'file', 'mimes:mp3,wav,ogg,m4a', 'max:10240'],
        ]);

        if ((int)$validated['item_id'] !== (int)$audioFile->item_id) {
            $conflict = AudioFile::where('item_id', $validated['item_id'])
                ->where('id', '!=', $audioFile->id)
                ->exists();

            if ($conflict) {
                return back()
                    ->withErrors(['item_id' => 'Item tersebut sudah memiliki audio lain.'])
                    ->withInput();
            }
        }

        $disk = 'gcs';
        $oldPath = $audioFile->lokasi_penyimpanan;
        $newPath = $oldPath;
        $duration = $audioFile->durasi;
        $ext = $audioFile->format_file;

        // Upload new file first if provided
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $ext  = $file->guessExtension() ?: $file->getClientOriginalExtension();
            $newPath = 'audios/' . Str::uuid() . '.' . $ext;
            $duration = $this->extractAudioDuration($file);

            Storage::disk($disk)->put($newPath, file_get_contents($file->getRealPath()));
        }

        // Update database record
        DB::transaction(function () use ($audioFile, $validated, $ext, $duration, $newPath, $oldPath) {
            $audioFile->update([
                'nama_file'          => $validated['nama_file'],
                'item_id'            => $validated['item_id'],
                'format_file'        => $ext,
                'durasi'             => $duration,
                'lokasi_penyimpanan' => $newPath,
                'tanggal_upload'     => now(),
            ]);

            ActivityLog::create([
                'user_id'         => auth()->id(),
                'aktivitas'       => 'replace_audio',
                'waktu_aktivitas' => now(),
                'context'         => [
                    'audio_id'  => $audioFile->id,
                    'item_id'   => $audioFile->item_id,
                    'old_path'  => $oldPath,
                    'new_path'  => $newPath,
                    'nama_file' => $validated['nama_file'],
                ],
            ]);
        });

        // Delete old file AFTER commit (best-effort)
        if ($request->hasFile('file') && $oldPath && $oldPath !== $newPath) {
            try {
                Storage::disk($disk)->delete($oldPath);
            } catch (\Throwable $e) {
                \Log::warning('Failed to delete old GCS audio: ' . $e->getMessage());
            }
        }

        return redirect()->route('admin.audio.index')
            ->with('status', 'Audio berhasil diganti.');
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AudioFile $audioFile)
    {
        $disk = 'gcs';
        $path = $audioFile->lokasi_penyimpanan;

        return DB::transaction(function () use ($audioFile, $disk, $path) {
            $audioId  = $audioFile->id;
            $namaFile = $audioFile->nama_file;

            // Delete from GCS (ignore if fails)
            if ($path) {
                try {
                    Storage::disk($disk)->delete($path);
                } catch (\Throwable $e) {
                    \Log::warning('Failed to delete GCS audio: ' . $e->getMessage());
                }
            }

            $audioFile->delete();

            ActivityLog::create([
                'user_id'         => auth()->id(),
                'aktivitas'       => 'delete_audio',
                'waktu_aktivitas' => now(),
                'context'         => [
                    'audio_id'  => $audioId,
                    'nama_file' => $namaFile,
                    'path'      => $path,
                    'disk'      => $disk,
                ],
            ]);

            return redirect()->route('admin.audio.index')
                ->with('status', 'Audio berhasil dihapus.');
        });
    }

    /**
     * Download the specified resource from storage.
     */
    public function download(AudioFile $audioFile)
    {
        $dbPath = $audioFile->lokasi_penyimpanan; // e.g. "audios/<uuid>.mp3"
        if (!$dbPath) {
            return back()->with('error', 'Path file tidak tersedia untuk audio ini.');
        }

        // Combine with any path_prefix (we keep DB path as-is; this just avoids double-prefixing)
        $objectPath = $this->normalizeObjectPath($dbPath);

        // Nice filename for "Save as…"
        $ext          = ltrim((string) $audioFile->format_file, '.');
        $base         = (string) $audioFile->nama_file;
        $downloadName = str_ends_with(strtolower($base), '.'.$ext) ? $base : ($base . ($ext ? '.'.$ext : ''));

        // Create client & signed URL (NO pre-existence check → avoids SSL CA issues in dev)
        $client = $this->gcsClient();
        $bucket = $client->bucket(config('filesystems.disks.gcs.bucket'));
        $object = $bucket->object($objectPath);

        // Generate a V4 signed URL (10 minutes)
        $signedUrl = $object->signedUrl(new \DateTimeImmutable('+10 minutes'), [
            'version'              => 'v4',
            'responseDisposition'  => 'attachment; filename="'.addslashes($downloadName).'"',
        ]);

        ActivityLog::create([
            'user_id'         => auth()->id(),
            'aktivitas'       => 'download_audio',
            'waktu_aktivitas' => now(),
            'context'         => [
                'audio_id'  => $audioFile->id,
                'item_id'   => $audioFile->item_id,
                'nama_file' => $audioFile->nama_file,
                'path'      => $objectPath,
                'disk'      => 'gcs',
            ],
        ]);

        return redirect()->away($signedUrl);
    }

    /**
     * Build a StorageClient using either the bound client or explicit config/env.
     */
    private function gcsClient(): StorageClient
    {
        if (app()->bound('google.cloud.storage')) {
            /** @var StorageClient $client */
            $client = app('google.cloud.storage');
            return $client;
        }

        $cfg = config('filesystems.disks.gcs');

        // Use GOOGLE_APPLICATION_CREDENTIALS by default; you can also set keyFilePath here explicitly.
        $opts = [];
        if (!empty($cfg['project_id'])) {
            $opts['projectId'] = $cfg['project_id'];
        }
        if (!empty($cfg['key_file'])) {
            // if you store JSON content in config, use 'keyFile'; otherwise prefer env var/path.
            $opts['keyFile'] = $cfg['key_file'];
        } elseif (!empty($cfg['key_file_path'])) {
            $opts['keyFilePath'] = $cfg['key_file_path'];
        }
        return new StorageClient($opts);
    }

    /**
     * Normalize DB path with optional path_prefix to the actual object path.
     */
    private function normalizeObjectPath(string $dbPath): string
    {
        $prefix = trim((string) (config('filesystems.disks.gcs.path_prefix') ?? ''), '/'); // may be ''
        return ltrim($prefix ? $prefix.'/'.ltrim($dbPath, '/') : $dbPath, '/');
    }
}
