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
        Schema::create('bukti_dukung', function (Blueprint $table) {
            $table->id();
            $table->foreignId('laporan_kegiatan_id')->constrained('laporan_kegiatan')->onDelete('cascade');
            $table->string('file');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bukti_dukung');
    }
};
