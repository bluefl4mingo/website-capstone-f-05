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
}
