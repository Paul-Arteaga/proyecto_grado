@extends('layout.navbar')

@section('titulo', 'Mi Perfil')

@section('contenido')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
  
  <h1 class="text-3xl font-bold text-gray-900 mb-6">Mi Perfil</h1>

  <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    
    {{-- Tarjeta: Mis Reservas --}}
    <a href="{{ route('perfil.reservas') }}" class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition cursor-pointer">
      <div class="flex items-center space-x-4">
        <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center">
          <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
          </svg>
        </div>
        <div>
          <h2 class="text-xl font-semibold text-gray-900">Mis Reservas</h2>
          <p class="text-gray-600">Ver todas tus reservas</p>
        </div>
      </div>
    </a>

    {{-- Tarjeta: Mis Datos --}}
    <a href="{{ route('perfil.datos') }}" class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition cursor-pointer">
      <div class="flex items-center space-x-4">
        <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center">
          <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
          </svg>
        </div>
        <div>
          <h2 class="text-xl font-semibold text-gray-900">Mis Datos</h2>
          <p class="text-gray-600">Gestionar mi información personal</p>
        </div>
      </div>
    </a>

  </div>

  {{-- Información del Usuario --}}
  <div class="mt-8 bg-white rounded-lg shadow-md p-6">
    <h2 class="text-xl font-semibold text-gray-800 mb-4">Información Personal</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div>
        <p class="text-sm text-gray-500">Usuario</p>
        <p class="text-lg font-medium text-gray-900">{{ $user->username }}</p>
      </div>
      <div>
        <p class="text-sm text-gray-500">Número de Carnet</p>
        <p class="text-lg font-medium text-gray-900">{{ $user->numero_carnet ?? 'N/A' }}</p>
      </div>
      @if($user->email)
      <div>
        <p class="text-sm text-gray-500">Email</p>
        <p class="text-lg font-medium text-gray-900">{{ $user->email }}</p>
      </div>
      @endif
      <div>
        <p class="text-sm text-gray-500">Rol</p>
        <p class="text-lg font-medium text-gray-900">{{ $user->rol ? ucfirst($user->rol->nombre) : 'Sin rol' }}</p>
      </div>
    </div>
  </div>

</div>
@endsection






