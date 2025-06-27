<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        User::create([
            'name' => 'ユーザーA',
            'email' => 'userA@example.com',
            'password' => Hash::make('sekirubeg'),
        ]);

        User::create([
            'name' => 'ユーザーB',
            'email' => 'userB@example.com',
            'password' => Hash::make('sekirubeg'),
        ]);

        User::create([
            'name' => 'ユーザーC',
            'email' => 'userC@example.com',
            'password' => Hash::make('sekirubeg'),
        ]);
    }

}
