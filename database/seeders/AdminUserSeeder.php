<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class AdminUserSeeder extends Seeder
{
    public function run()
    {
        User::create([
            'first_name' => 'Admin',
            'last_name' => 'Root',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'identification_type' => 'CC',
            'identification_number' => '123456789',
            'role' => 'admin',
        ]);
    }
}
