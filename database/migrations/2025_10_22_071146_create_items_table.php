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
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->string('nama_item');           
            $table->text('deskripsi')->nullable();    
            $table->string('kategori')->nullable(); 
            $table->string('lokasi_pameran')->nullable();
            $table->date('tanggal_penambahan')->nullable();
            $table->timestamps();

            $table->index(['nama_item','kategori']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
