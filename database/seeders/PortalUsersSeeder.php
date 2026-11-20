<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class PortalUsersSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin.sistema@local.test'],
            [
                'name' => 'Administrador Portal',
                'password' => Hash::make('Admin1234*'),
                'role' => 'admin',
                'two_factor_code' => null,
                'two_factor_expires_at' => null,
            ]
        );

        User::updateOrCreate(
            ['email' => 'empleado.sistema@local.test'],
            [
                'name' => 'Empleado Portal',
                'password' => Hash::make('Empleado1234*'),
                'role' => 'employee',
                'two_factor_code' => null,
                'two_factor_expires_at' => null,
            ]
        );
    }
}