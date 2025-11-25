@extends('layout.navbar')

@section('titulo', 'Mis Datos')

@section('contenido')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
  
  <div class="flex justify-between items-center mb-6">
    <h1 class="text-3xl font-bold text-gray-900">Mis Datos</h1>
    <a href="{{ route('perfil.index') }}" class="text-blue-600 hover:text-blue-800 font-medium">
      ← Volver al Perfil
    </a>
  </div>

  <div class="bg-white rounded-lg shadow-md p-6">
    <h2 class="text-xl font-semibold text-gray-800 mb-6">Información Personal</h2>
    
    <div class="space-y-6">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Usuario</label>
          <input type="text" value="{{ $user->username }}" disabled
                 class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 text-gray-700">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Número de Carnet</label>
          <input type="text" value="{{ $user->numero_carnet ?? 'N/A' }}" disabled
                 class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 text-gray-700">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
          <input type="email" value="{{ $user->email ?? 'No registrado' }}" disabled
                 class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 text-gray-700">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Rol</label>
          <input type="text" value="{{ $user->rol ? ucfirst($user->rol->nombre) : 'Sin rol' }}" disabled
                 class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 text-gray-700">
        </div>
      </div>

      <div class="pt-6 border-t border-gray-200">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Estadísticas</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div class="bg-blue-50 rounded-lg p-4">
            <p class="text-sm text-gray-600">Total Reservas</p>
            <p class="text-2xl font-bold text-blue-600">{{ $user->reservas()->count() }}</p>
          </div>
          <div class="bg-green-50 rounded-lg p-4">
            <p class="text-sm text-gray-600">Reservas Confirmadas</p>
            <p class="text-2xl font-bold text-green-600">{{ $user->reservas()->where('estado', 'confirmada')->count() }}</p>
          </div>
          <div class="bg-yellow-50 rounded-lg p-4">
            <p class="text-sm text-gray-600">Reservas Pendientes</p>
            <p class="text-2xl font-bold text-yellow-600">{{ $user->reservas()->where('estado', 'pendiente')->count() }}</p>
          </div>
        </div>
      </div>
    </div>
  </div>

</div>
@endsection


