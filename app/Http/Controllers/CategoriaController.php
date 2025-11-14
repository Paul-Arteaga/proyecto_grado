<?php

namespace App\Http\Controllers;

use App\Http\Requests\CategoriaStoreRequest;
use App\Http\Requests\CategoriaUpdateRequest;
use App\Models\Categoria;
use App\Models\Vehiculo;
use App\Models\Tarifa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CategoriaController extends Controller
{
    public function __construct()
    {
        // 🔒 obliga a estar autenticado para cualquier acción de este controlador
        $this->middleware('auth');
    }

    // ✅ 1. Listado (con búsqueda y paginación)
    public function index(Request $request)
    {
        $q = Categoria::query()
            ->when($request->filled('search'), function ($qq) use ($request) {
                $qq->where(function ($w) use ($request) {
                    $w->where('nombre', 'like', '%'.$request->search.'%')
                      ->orWhere('descripcion', 'like', '%'.$request->search.'%');
                });
            })
            ->latest('id');

        $categorias = $q->paginate(12)->withQueryString();

        return view('categoria.index', compact('categorias'));
    }

    public function create()
    {
        return view('categoria.create');
    }

    public function store(CategoriaStoreRequest $request)
    {
        $data = $request->validated();

        if ($request->hasFile('imagen')) {
            $data['imagen'] = $request->file('imagen')->store('categorias', 'public');
        }

        $cat = Categoria::create($data);

        return redirect()->route('categoria.edit', $cat)
                         ->with('ok', 'Categoría creada correctamente.');
    }

    public function edit(Categoria $categoria)
    {
        $categoria->load(['vehiculos','tarifas']);
        return view('categoria.edit', compact('categoria'));
    }

    public function update(CategoriaUpdateRequest $request, Categoria $categoria)
    {
        $data = array_filter($request->validated(), fn($v) => !is_null($v));

        if ($request->hasFile('imagen')) {
            if (!empty($categoria->imagen) && Storage::disk('public')->exists($categoria->imagen)) {
                Storage::disk('public')->delete($categoria->imagen);
            }
            $data['imagen'] = $request->file('imagen')->store('categorias', 'public');
        }

        $categoria->update($data);

        return back()->with('ok', 'Categoría actualizada correctamente.');
    }

    public function syncVehiculos(Request $request, Categoria $categoria)
    {
        $data = $request->validate([
            'vehiculos'   => ['array'],
            'vehiculos.*' => ['integer','exists:vehiculos,id'],
        ]);

        $ids = $data['vehiculos'] ?? [];

        Vehiculo::where('categoria_id', $categoria->id)
            ->whereNotIn('id', $ids)
            ->update(['categoria_id' => null]);

        if (!empty($ids)) {
            Vehiculo::whereIn('id', $ids)->update(['categoria_id' => $categoria->id]);
        }

        return response()->json(['ok' => true, 'message' => 'Vehículos vinculados correctamente.']);
    }

    public function syncTarifas(Request $request, Categoria $categoria)
    {
        $data = $request->validate([
            'tarifas'   => ['array'],
            'tarifas.*' => ['integer','exists:tarifas,id'],
        ]);

        $ids = $data['tarifas'] ?? [];

        Tarifa::where('categoria_id', $categoria->id)
            ->whereNotIn('id', $ids)
            ->update(['categoria_id' => null]);

        if (!empty($ids)) {
            Tarifa::whereIn('id', $ids)->update(['categoria_id' => $categoria->id]);
        }

        return response()->json(['ok' => true, 'message' => 'Tarifas vinculadas correctamente.']);
    }

    public function verificarCompatibilidad(Request $request, Categoria $categoria)
    {
        $data = $request->validate([
            'tarifas'   => ['array'],
            'tarifas.*' => ['integer','exists:tarifas,id'],
        ]);

        $tarifasIds = $data['tarifas'] ?? [];
        $vehiculosCount = $categoria->vehiculos()->count();

        $compatible = $vehiculosCount > 0 && count($tarifasIds) > 0;

        return response()->json([
            'compatible' => $compatible,
            'vehiculos_count' => $vehiculosCount,
            'tarifas_count' => count($tarifasIds),
            'message' => $compatible
                ? 'Compatible.'
                : 'No hay compatibilidad con los criterios actuales.'
        ]);
    }

    public function toggle(Categoria $categoria)
    {
        $categoria->update(['activo' => !$categoria->activo]);
        return response()->json(['ok' => true, 'activo' => $categoria->activo]);
    }
}
