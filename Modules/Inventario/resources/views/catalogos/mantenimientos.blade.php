@extends('adminlte::page')

@section('title', 'Mantenimientos — Inventario')

@section('content_header')
    <h1>Catálogo de Tipos de Mantenimiento</h1>
@endsection

@section('content')
    <div class="card">
        <div class="card-body">
            @livewire('catalogos.mantenimientos-index')
        </div>
    </div>
@endsection

@section('footer')
    @include('inventario::components.footer')
@endsection
