<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    protected $fillable = ['nama_item','deskripsi','kategori','lokasi_pameran','tanggal_penambahan'];

    public function audioFiles() { return $this->hasMany(AudioFile::class); }
    public function nfcTags()    { return $this->hasMany(NfcTag::class); }
}
