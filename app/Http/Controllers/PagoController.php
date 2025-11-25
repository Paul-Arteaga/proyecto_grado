<?php

namespace App\Http\Controllers;

use App\Models\ConfiguracionPago;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PagoController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin'); // Solo admin puede gestionar pagos
    }

    /**
     * Muestra la configuración de pagos
     */
    public function index()
    {
        $configuracion = ConfiguracionPago::activa();
        return view('pago.index', compact('configuracion'));
    }

    /**
     * Actualiza la configuración de pagos
     */
    public function update(Request $request)
    {
        $request->validate([
            'qr_imagen_vehiculos' => 'nullable|image|mimes:png,jpg,jpeg|max:5120',
            'qr_imagen_accesorios' => 'nullable|image|mimes:png,jpg,jpeg|max:5120',
            'instrucciones_pago' => 'nullable|string|max:2000',
            'numero_cuenta' => 'nullable|string|max:50',
        ]);

        $configuracion = ConfiguracionPago::activa();
        
        if (!$configuracion) {
            $configuracion = new ConfiguracionPago();
            $configuracion->activo = true;
        }

        // Si se sube una nueva imagen QR para vehículos, eliminar la anterior
        if ($request->hasFile('qr_imagen_vehiculos')) {
            if ($configuracion->qr_imagen_vehiculos) {
                Storage::disk('public')->delete($configuracion->qr_imagen_vehiculos);
            }
            $configuracion->qr_imagen_vehiculos = $request->file('qr_imagen_vehiculos')->store('pagos/qr', 'public');
        }

        // Si se sube una nueva imagen QR para accesorios, eliminar la anterior
        if ($request->hasFile('qr_imagen_accesorios')) {
            if ($configuracion->qr_imagen_accesorios) {
                Storage::disk('public')->delete($configuracion->qr_imagen_accesorios);
            }
            $configuracion->qr_imagen_accesorios = $request->file('qr_imagen_accesorios')->store('pagos/qr', 'public');
        }

        $configuracion->instrucciones_pago = $request->instrucciones_pago;
        $configuracion->numero_cuenta = $request->numero_cuenta;
        $configuracion->save();

        return redirect()->route('pago.index')->with('success', 'Configuración de pagos actualizada correctamente.');
    }
}
