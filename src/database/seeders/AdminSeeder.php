<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        Admin::create([
            'name' => '管理者',
            'email' => 'admin@example.com',
            'password' => Hash::make('sekirubeg'), // ← 本番ではより強固に
        ]);
    }
}

