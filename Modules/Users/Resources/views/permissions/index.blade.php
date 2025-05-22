@extends('adminlte::page')

@section('title', 'Panel de Administración')

@section('content_header')
    <h1>Gestión de Permisos</h1>
@endsection

@section('content')

    @if (session('info'))
        <div class="alert alert-success">
            <strong>{{ session('info') }}</strong>
        </div>
    @endif

    <div class="card">        
        <div class="card-body"> 
            @livewire('permissions.permissions-index')
        </div>
    </div>
@endsection
