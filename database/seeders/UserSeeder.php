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
        // Aseguramos roles por si alguien corre solo este seeder
        $adminRol = Rol::firstOrCreate(['id' => 1], ['nombre' => 'admin']);
        $usuarioRol = Rol::firstOrCreate(['id' => 2], ['nombre' => 'usuario']);
        $recepcionistaRol = Rol::firstOrCreate(['id' => 3], ['nombre' => 'recepcionista']);
        $mantenimientoRol = Rol::firstOrCreate(['id' => 4], ['nombre' => 'area de mantenimiento']);

        // ADMIN
        User::updateOrCreate(
            ['username' => 'admin'],
            [
                'numero_carnet' => '00000001',
                'email'    => 'admin@mail.com',
                'password' => Hash::make('admin123'),
                'id_rol'   => $adminRol->id,
            ]
        );

        // USUARIO 1
        User::updateOrCreate(
            ['username' => 'usuario1'],
            [
                'numero_carnet' => '00000002',
                'email'    => 'usuario1@mail.com',
                'password' => Hash::make('123456'),
                'id_rol'   => $usuarioRol->id,
            ]
        );

        // RECEPCIONISTA
        User::updateOrCreate(
            ['username' => 'recepcionista'],
            [
                'numero_carnet' => '00000003',
                'email'    => 'recepcionista@mail.com',
                'password' => Hash::make('123456'),
                'id_rol'   => $recepcionistaRol->id,
            ]
        );

        // AREA DE MANTENIMIENTO
        User::updateOrCreate(
            ['username' => 'mantenimiento'],
            [
                'numero_carnet' => '00000004',
                'email'    => 'mantenimiento@mail.com',
                'password' => Hash::make('123456'),
                'id_rol'   => $mantenimientoRol->id,
            ]
        );
    }
}
