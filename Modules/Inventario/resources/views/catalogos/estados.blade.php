@extends('adminlte::page')

@section('title', 'Estados de Bien — Inventario')

@section('content_header')
    <h1>Catálogo de Estados de Bien</h1>
@endsection

@section('content')
    <div class="card">
        <div class="card-body">
            @livewire('catalogos.estados-index')
        </div>
    </div>
@endsection

@section('footer')
    @include('inventario::components.footer')
@endsection
