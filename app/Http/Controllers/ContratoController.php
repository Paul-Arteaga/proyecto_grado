<?php

namespace App\Http\Controllers;

use App\Models\ConfiguracionContrato;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ContratoController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    public function index()
    {
        $config = ConfiguracionContrato::activa();

        return view('contrato.index', compact('config'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'contrato' => 'required|file|mimes:pdf,doc,docx|max:20480',
        ]);

        $archivo = $request->file('contrato');

        $path = $archivo->store('contratos', 'public');

        // Eliminar contrato anterior si existe
        if ($config = ConfiguracionContrato::activa()) {
            if ($config->archivo && Storage::disk('public')->exists($config->archivo)) {
                Storage::disk('public')->delete($config->archivo);
            }
        }

        ConfiguracionContrato::create([
            'archivo' => $path,
            'nombre_original' => $archivo->getClientOriginalName(),
            'mime' => $archivo->getMimeType(),
            'updated_by' => Auth::id(),
        ]);

        return redirect()->route('contrato.index')->with('success', 'Contrato actualizado correctamente.');
    }
}






