<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Rol;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // aseguramos roles por si alguien corre solo este seeder
        $adminRol     = Rol::firstOrCreate(['id' => 1], ['nombre' => 'administrador']);
        $encargadoRol = Rol::firstOrCreate(['id' => 2], ['nombre' => 'encargado']);
        $clienteRol   = Rol::firstOrCreate(['id' => 3], ['nombre' => 'cliente']);

        // ADMIN
        User::updateOrCreate(
            ['username' => 'admin'],
            [
                'email'    => 'admin@mail.com',
                'password' => Hash::make('admin123'),
                'id_rol'   => $adminRol->id,
            ]
        );

        // CLIENTE 1
        User::updateOrCreate(
            ['username' => 'cliente1'],
            [
                'email'    => 'cliente1@mail.com',
                'password' => Hash::make('123456'),
                'id_rol'   => $clienteRol->id,
            ]
        );

        // CLIENTE 2
        User::updateOrCreate(
            ['username' => 'cliente2'],
            [
                'email'    => 'cliente2@mail.com',
                'password' => Hash::make('123456'),
                'id_rol'   => $clienteRol->id,
            ]
        );

        // CLIENTE 3
        User::updateOrCreate(
            ['username' => 'cliente3'],
            [
                'email'    => 'cliente3@mail.com',
                'password' => Hash::make('123456'),
                'id_rol'   => $clienteRol->id,
            ]
        );

        // ENCARGADO
        User::updateOrCreate(
            ['username' => 'encargado'],
            [
                'email'    => 'encargado@mail.com',
                'password' => Hash::make('123456'),
                'id_rol'   => $encargadoRol->id,
            ]
        );
    }
}
