@extends('adminlte::page')

@section('title', 'Panel de Administración')

@section('content_header')
    <h1>Lista de Roles</h1>
@endsection

@section('content')

    @if (session('info'))
        <div class="alert alert-success">
            <strong>{{ session('info') }}</strong>
        </div>
    @endif

    <div class="card">        
        <div class="card-body"> 
            @livewire('roles.roles-index')
        </div>
    </div>
@endsection