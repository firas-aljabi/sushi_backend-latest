<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'food',
            'email' => 'food@gmail.com',
            'password' => bcrypt('123456789'),
            'role' => 'super admin',
        ]);
    }
}
