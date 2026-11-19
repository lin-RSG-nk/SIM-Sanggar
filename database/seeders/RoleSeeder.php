<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        DB::table('roles')->upsert([
            [
                'name' => 'Admin',
                'slug' => 'admin',
                'description' => 'Mengelola data pengguna dan seluruh operasional sistem.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Kasir',
                'slug' => 'kasir',
                'description' => 'Mengelola transaksi dan absensi peserta.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Owner',
                'slug' => 'owner',
                'description' => 'Melihat dashboard analisis dan laporan sanggar.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ], ['slug'], ['name', 'description', 'updated_at']);
    }
}
