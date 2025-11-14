<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Rol;

class RolSeeder extends Seeder
{
    public function run(): void
    {
        // 1 = admin
        Rol::firstOrCreate(['id' => 1], ['nombre' => 'administrador']);

        // 2 = encargado
        Rol::firstOrCreate(['id' => 2], ['nombre' => 'encargado']);

        // 3 = cliente
        Rol::firstOrCreate(['id' => 3], ['nombre' => 'cliente']);
    }
}
