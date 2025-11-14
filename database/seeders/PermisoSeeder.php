<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permiso;

class PermisoSeeder extends Seeder
{
    public function run(): void
    {
        $permisos = ['rol','usuario','categoria','vehiculo','tarifa','reserva'];

        foreach ($permisos as $nombre) {
            Permiso::updateOrCreate(['nombre' => $nombre], []);
        }
    }
}
