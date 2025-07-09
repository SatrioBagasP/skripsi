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

        Roles::updateOrCreate([
            'name' => 'Admin', 
        ], []);
        Roles::updateOrCreate([
            'name' => 'Unit Kemahasiswaan',
        ], []);
        Roles::updateOrCreate([
            'name' => 'Dosen',
        ], []);
        Roles::updateOrCreate([
            'name' => 'Kaprodi',
        ], []);
        Roles::updateOrCreate([
            'name' => 'Kepala Bagian Minat Dan Bakat',
        ], []);
        Roles::updateOrCreate([
            'name' => 'Layanan Mahasiswa',
        ], []);
        Roles::updateOrCreate([
            'name' => 'Wakil Rektor Bidang Kemahasiswaan',
        ], []);

        User::updateOrCreate([
            'email' => 'admin@example.com',
        ], [
            'name' => 'admin',
            'password' => Hash::make('123'),
            'role_id' => 1,
        ]);
    }
}
