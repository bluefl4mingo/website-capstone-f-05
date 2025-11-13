<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class Item extends Model
{
    protected $fillable = ['nama_item','deskripsi','kategori','lokasi_pameran','tanggal_penambahan'];

    public function audioFiles() { return $this->hasMany(AudioFile::class); }
    public function nfcTags()    { return $this->hasMany(NfcTag::class); }

    /**
     * Boot method to handle cascade deletes
     */
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($item) {
            // Delete all audio files (including from cloud storage)
            foreach ($item->audioFiles as $audio) {
                // Delete from Google Cloud Storage
                if ($audio->lokasi_penyimpanan) {
                    try {
                        Storage::disk('gcs')->delete($audio->lokasi_penyimpanan);
                        Log::info("Deleted audio file from GCS: {$audio->lokasi_penyimpanan}");
                    } catch (\Exception $e) {
                        Log::warning("Failed to delete audio from GCS: {$audio->lokasi_penyimpanan}", [
                            'error' => $e->getMessage()
                        ]);
                    }
                }
                
                // Delete from database
                $audio->delete();
            }

            // Delete all NFC tags
            $item->nfcTags()->delete();
        });
    }
}
