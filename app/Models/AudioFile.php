<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AudioFile extends Model
{
    protected $fillable = [
        'item_id',
        'nama_file',
        'format_file',
        'durasi',
        'lokasi_penyimpanan',
        'tanggal_upload',
    ];

    protected $casts = [
        'tanggal_upload' => 'datetime',
    ];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }


    public function getFormattedDurationAttribute(): string
    {
        if (!$this->durasi) {
            return '—';
        }

        // If duration is already formatted as MM:SS or HH:MM:SS, return as is
        if (preg_match('/^\d{1,2}:\d{2}(:\d{2})?$/', $this->durasi)) {
            return $this->durasi;
        }

        if (is_numeric($this->durasi)) {
            $seconds = (int) $this->durasi;
            $hours = floor($seconds / 3600);
            $minutes = floor(($seconds % 3600) / 60);
            $secs = $seconds % 60;

            if ($hours > 0) {
                return sprintf('%02d:%02d:%02d', $hours, $minutes, $secs);
            }
            return sprintf('%02d:%02d', $minutes, $secs);
        }

        return $this->durasi;
    }
}
