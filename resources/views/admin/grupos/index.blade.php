@extends('adminlte::page')

@section('title', 'Panel de Administración')

@section('content_header')
    <h1>Lista de Grupos</h1>    
@endsection

@section('content')

    @if (session('info'))
        <div class="alert alert-success">
            <strong>{{ session('info') }}</strong>
        </div>
    @endif

    <div class="card">        

        <div class="card-header">
            <a href="{{ route('admin.grupos.create') }}" class="btn btn-success">Agregar Grupo</a>
        </div>

        <div class="card-body">

            <p>Para editar, doble click en el campo que desee modificar.</p>
            
            <table class="table table-striped">
                <thead>
                   <tr>
                        <th>Id</th>
                        <th>Nombre</th>
                        <th colspan="2">Acciones</th>
                    </tr>
                   </tr>
                </thead>
                <tbody>
                    @foreach ($grupos as $grupo)
                        <tr>  
                            <td>{{ $grupo->id }}</td>                          
                            <!-- Componente Livewire para Nombre -->
                            <td>
                                @livewire('grupo.editar-nombre-grupo', ['grupo' => $grupo], key('nombre-'.$grupo->id))
                            </td>                           

                            
                            <td width="10px">
                                <form action="{{ route('admin.grupos.destroy', $grupo) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
                                </form>
                        </tr>                        
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection