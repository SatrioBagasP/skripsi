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
        Schema::create('proposal', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('mahasiswa_id')->constrained('mahasiswa')->onDelete('cascade');
            $table->foreignId('dosen_id')->constrained('dosen');
            $table->string('no_proposal')->unique();
            $table->string('name');
            $table->text('desc');
            $table->string('file');
            $table->boolean('is_harian');
            $table->dateTime('start_date');
            $table->dateTime('end_date');
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
        Schema::dropIfExists('proposal');
    }
};
