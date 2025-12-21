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
        // Tạo tài khoản Admin
        User::updateOrCreate(
            ['email' => 'admin@ctut.edu.vn'],
            [
                'name' => 'Administrator',
                'email' => 'admin@ctut.edu.vn',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'email_verified_at' => now(),
            ]
        );

        // Tạo tài khoản User mẫu
        User::updateOrCreate(
            ['email' => 'user@ctut.edu.vn'],
            [
                'name' => 'Người dùng',
                'email' => 'user@ctut.edu.vn',
                'password' => Hash::make('password'),
                'role' => 'user',
                'email_verified_at' => now(),
            ]
        );

        $this->command->info('Đã tạo tài khoản mặc định:');
        $this->command->info('Admin: admin@ctut.edu.vn / password');
        $this->command->info('User: user@ctut.edu.vn / password');
    }
}

