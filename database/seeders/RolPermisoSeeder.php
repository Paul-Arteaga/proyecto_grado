<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Rol;
use App\Models\Permiso;
use App\Models\RolPermiso;

class RolPermisoSeeder extends Seeder
{
    public function run(): void
    {
        // IDs por nombre (según RolSeeder y PermisoSeeder)
        $rolAdmin     = Rol::where('nombre', 'administrador')->value('id');
        $rolTrabajador= Rol::where('nombre', 'trabajador')->value('id');
        $rolCliente   = Rol::where('nombre', 'cliente')->value('id');

        $permRol      = Permiso::where('nombre', 'rol')->value('id');
        $permUsuario  = Permiso::where('nombre', 'usuario')->value('id');
        $permCategoria= Permiso::where('nombre', 'categoria')->value('id');
        $permVehiculo = Permiso::where('nombre', 'vehiculo')->value('id');
        $permTarifa   = Permiso::where('nombre', 'tarifa')->value('id');
        $permReserva  = Permiso::where('nombre', 'reserva')->value('id');

        // Helper para upsert pivot
        $attach = function($idRol, $idPermiso) {
            if ($idRol && $idPermiso) {
                RolPermiso::updateOrCreate(
                    ['id_rol' => $idRol, 'id_permiso' => $idPermiso],
                    [] // sin columnas extra
                );
            }
        };

        // Permisos administrador: todos (ajusta a tu gusto)
        foreach ([$permRol,$permUsuario,$permCategoria,$permVehiculo,$permTarifa,$permReserva] as $p) {
            $attach($rolAdmin, $p);
        }

        // Permisos trabajador (ejemplo: vehiculo, tarifa, reserva)
        foreach ([$permVehiculo,$permTarifa,$permReserva] as $p) {
            $attach($rolTrabajador, $p);
        }

        // Permisos cliente (ejemplo: reserva)
        $attach($rolCliente, $permReserva);
    }
}
