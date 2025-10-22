<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('audio_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained('items')->cascadeOnDelete();
            $table->string('nama_file');
            $table->string('format_file')->nullable(); 
            $table->unsignedInteger('durasi')->nullable();
            $table->string('lokasi_penyimpanan'); 
            $table->timestamp('tanggal_upload')->nullable();
            $table->timestamps();

            $table->index(['item_id','nama_file']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audio_files');
    }
};
