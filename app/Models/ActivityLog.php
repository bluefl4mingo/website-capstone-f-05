<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $fillable = ['user_id','aktivitas','waktu_aktivitas','context'];
    protected $casts = ['context' => 'array','waktu_aktivitas' => 'datetime'];
    public function user() { return $this->belongsTo(User::class); }
}
