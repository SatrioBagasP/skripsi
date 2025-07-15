<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    // public function up(): void
    // {
    //     Schema::table('dosen', function (Blueprint $table) {
    //         $table->foreignId('jurusan_id')->nullable()->constrained('jurusan')->nullOnDelete();
    //     });
    //     Schema::table('unit_kemahasiswaan', function (Blueprint $table) {
    //         $table->foreignId('jurusan_id')->nullable()->constrained('jurusan')->nullOnDelete();
    //     });
    // }

    // /**
    //  * Reverse the migrations.
    //  */
    // public function down(): void
    // {
    //     Schema::table('dosen', function (Blueprint $table) {
    //         $table->dropConstrainedForeignId('jurusan_id');
    //     });
    //     Schema::table('dosen', function (Blueprint $table) {
    //         //
    //         $table->dropConstrainedForeignId('jurusan_id');
    //     });
    // }
};
