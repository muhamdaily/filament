<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('users')->insert([
            'name' => 'MuhamDaily',             // Mengatur nama lengkap
            'email' => 'admin@admin.com',       // Mengatur email pengguna
            'password' => Hash::make('admin'),  // Meng-hash dan mengatur kata sandi pengguna
            'email_verified_at' => now(),       // Mengatur verifikasi email
        ]);
    }
}
