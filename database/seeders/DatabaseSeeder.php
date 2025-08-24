<?php

namespace Database\Seeders;

use App\Models\Akademik;
use App\Models\User;
use App\Models\Roles;
use App\Models\Jurusan;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        Roles::updateOrCreate([
            'name' => 'Admin',
        ], []);
        Roles::updateOrCreate([
            'name' => 'Unit Kemahasiswaan',
        ], []);
        Roles::updateOrCreate([
            'name' => 'Verifikator',
        ], []);

        User::updateOrCreate([
            'id' => 1,
            'email' => 'admin@example.com',
        ], [
            'name' => 'admin',
            'password' => Hash::make('123'),
        ]);

        DB::table('user_has_role')->updateOrInsert([
            'user_id' => 1,
            'role_id' => 1,
        ], [
            'created_at' => now(),
            'updated_at' => now(),
        ]);


        Akademik::updateOrCreate([
            'id' => 1,
            'name' => 'Layanan Mahasiswa',
            'no_hp' => '081336180467',
            'status' => true,
        ]);

        Akademik::updateOrCreate([
            'id' => 2,
            'name' => 'Minat dan Bakat',
            'no_hp' => '081336180467',
            'status' => true,
        ]);

        Jurusan::updateOrCreate([
            'id' => 1,
            'name' => 'Sistem Informasi',
            'kode' => 13,
            'status' => true,
        ]);

        Jurusan::updateOrCreate([
            'id' => 2,
            'name' => 'Teknik Informatika',
            'kode' => 12,
            'status' => true,
        ]);
    }
}
