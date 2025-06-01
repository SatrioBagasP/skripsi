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
        Schema::create('laporan_kegiatan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('propsal_id')->constrained('proposal')->onDelete('cascade');
            $table->string('file');
            $table->dateTime('available_at');
            $table->string('status');
            $table->text('alasan_tolak')->nullable();
            $table->boolean('is_acc_dosen')->nullable();
            $table->boolean('is_acc_kaprodi')->nullable();
            $table->boolean('is_acc_minat_bakat')->nullable();
            $table->boolean('is_acc_layanan')->nullable();
            $table->boolean('is_acc_wakil_rektor')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('laporan_kegiatan');
    }
};
