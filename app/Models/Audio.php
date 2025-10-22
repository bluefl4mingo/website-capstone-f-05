<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Audio extends Model
{
    protected $fillable = [
        'title','description','category_id','storage_path',
        'mime_type','size_bytes','duration_sec','hash'
    ];

    public function category() {
        return $this->belongsTo(Category::class);
    }
}
