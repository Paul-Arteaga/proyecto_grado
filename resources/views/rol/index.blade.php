@extends('layout.navbar')

@section('titulo','roles')

@section('contenido')        
    <main class="content">
    <div class="header-section">
      <h1>Gestión de Roles</h1>
      <button class="btn-add">+ Agregar Rol</button>
    </div>

    <table class="roles-table">
      <thead>
        <tr>
          <th>ID</th>
          <th>Nombre del Rol</th>       
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>1</td>
          <td>Administrador</td>         
          <td>
            <button class="btn-edit">Editar</button>
            <button class="btn-delete">Eliminar</button>
          </td>
        </tr>    
      </tbody>
    </table>
  </main>
@endsection
