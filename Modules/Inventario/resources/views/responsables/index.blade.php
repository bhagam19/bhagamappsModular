@extends('adminlte::page')

@section('title', 'Responsables y Custodios — Inventario')

@section('content_header')
    <h1>Gestión de Responsables y Custodios</h1>
@endsection

@section('content')
    <div class="card">
        <div class="card-body">
            @livewire('responsables.responsables-index')
        </div>
    </div>
@endsection

@section('footer')
    @include('inventario::components.footer')
@endsection
