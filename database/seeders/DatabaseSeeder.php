<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Dosen;
use App\Models\Roles;
use App\Models\Jabatan;
use App\Models\Jurusan;
use Faker\Factory as Faker;
use App\Models\Akademik;
use App\Models\Mahasiswa;
use App\Models\UnitKemahasiswaan;
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
        ], [
            'no_hp' => '081336180467',
            'status' => true,
        ]);

        Akademik::updateOrCreate([
            'id' => 2,
            'name' => 'Minat dan Bakat',
        ], [
            'no_hp' => '081336180467',
            'status' => true,
        ]);

        Jurusan::updateOrCreate([
            'id' => 1,
            'name' => 'Sistem Informasi',
        ], [
            'kode' => 13,
            'status' => true,
        ]);

        Jurusan::updateOrCreate([
            'id' => 2,
            'name' => 'Teknik Informatika',
        ], [
            'kode' => 12,
            'status' => true,
        ]);

        Jurusan::updateOrCreate([
            'id' => 3,
            'name' => 'Teknik Sipil',
        ], [
            'kode' => 6,
            'status' => true,
        ]);

        Jabatan::updateOrCreate([
            'id' => 1,
            'name' => 'Dosen',
        ]);

        Jabatan::updateOrCreate([
            'id' => 2,
            'name' => 'Ketua Program Studi',
        ]);

        Jabatan::updateOrCreate([
            'id' => 3,
            'name' => 'Wakil Rektor 1',
        ]);


        $faker = Faker::create('id_ID');

        foreach (range(1, 10) as $i) {
            Dosen::create([
                'name' => $faker->name,
                'nip' => $faker->unique()->numerify('14##########'),
                'jurusan_id' => $faker->numberBetween(1, 3),
                'jabatan_id' => 1,
                'no_hp' => '081336180467',
                'alamat' => $faker->address,
                'status' => true,
            ]);
        }

        foreach (range(1, 10) as $i) {
            Mahasiswa::create([
                'name' => $faker->name,
                'npm' => $faker->unique()->numerify('#.####.#.#####'),
                'jurusan_id' => $faker->numberBetween(1, 3),
                'no_hp' => '081336180467',
                'status' => true,
            ]);
        }

        UnitKemahasiswaan::updateOrCreate([
            'id' => 1,
            'name' => 'Himpunan Mahasiswa Sistem Informasi',
        ], [
            'jurusan_id' => 1,
            'is_non_jurusan' => false,
            'status' => true,
        ]);
        UnitKemahasiswaan::updateOrCreate([
            'id' => 2,
            'name' => 'Himpunan Mahasiswa Teknik Informatika',
        ], [
            'jurusan_id' => 2,
            'is_non_jurusan' => false,
            'status' => true,
        ]);
        UnitKemahasiswaan::updateOrCreate([
            'id' => 3,
            'name' => 'Himpunan Mahasiswa Teknik Sipil',
        ], [
            'jurusan_id' => 3,
            'is_non_jurusan' => false,
            'status' => true,
        ]);

        UnitKemahasiswaan::updateOrCreate([
            'id' => 4,
            'name' => 'Mapala',
        ], [
            'is_non_jurusan' => true,
            'status' => true,
        ]);

        UnitKemahasiswaan::updateOrCreate([
            'id' => 5,
            'name' => 'Batminton',
        ], [
            'is_non_jurusan' => true,
            'status' => true,
        ]);
    }
}
