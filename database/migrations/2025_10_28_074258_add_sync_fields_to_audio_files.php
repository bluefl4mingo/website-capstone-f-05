<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('audio_files', function (Blueprint $table) {
            $table->string('sync_status')->default('pending'); // pending|in_progress|synced|failed
            $table->unsignedInteger('sync_version')->default(1);
            $table->timestamp('last_synced_at')->nullable();
            $table->string('checksum')->nullable();            // optional (md5/sha256)
        });
    }

    public function down(): void {
        Schema::table('audio_files', function (Blueprint $table) {
            $table->dropColumn(['sync_status','sync_version','last_synced_at','checksum']);
        });
    }
};