<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Rol;

class RolSeeder extends Seeder
{
    public function run(): void
    {
        // 1 = admin
        Rol::firstOrCreate(['id' => 1], ['nombre' => 'admin']);

        // 2 = usuario
        Rol::firstOrCreate(['id' => 2], ['nombre' => 'usuario']);

        // 3 = recepcionista
        Rol::firstOrCreate(['id' => 3], ['nombre' => 'recepcionista']);

        // 4 = area de mantenimiento
        Rol::firstOrCreate(['id' => 4], ['nombre' => 'area de mantenimiento']);
    }
}
