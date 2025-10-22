<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NfcTag extends Model
{
    protected $fillable = ['item_id','kode_tag'];
    public function item() { return $this->belongsTo(Item::class); }
}
