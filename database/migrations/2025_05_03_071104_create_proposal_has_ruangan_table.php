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
        Schema::create('proposal_has_ruangan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proposal_id')->constrained('proposal')->onDelete('cascade');
            $table->foreignId('ruangan_id')->nullable()->constrained('ruangan')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('proposal_has_ruangan');
    }
};
