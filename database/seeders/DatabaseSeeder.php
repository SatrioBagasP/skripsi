<?php

namespace Database\Seeders;

use App\Models\Roles;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('123'),
        ]);

        Roles::create([
            'name' => 'Admin',
        ]);
        Roles::create([
            'name' => 'Unit Kemahasiswaan',
        ]);
        Roles::create([
            'name' => 'Dosen',
        ]);
        Roles::create([
            'name' => 'Kaprodi',
        ]);
        Roles::create([
            'name' => 'Kepala Bagian Minat Dan Bakat',
        ]);
        Roles::create([
            'name' => 'Layanan Mahasiswa',
        ]);
        Roles::create([
            'name' => 'Wakil Rektor Bidang Kemahasiswaan',
        ]);
    }
}
