@extends('adminlte::page')

@section('title', 'Categorías — Inventario')

@section('content_header')
    <h1>Catálogo de Categorías</h1>
@endsection

@section('content')
    <div class="card">
        <div class="card-body">
            @livewire('catalogos.categorias-index')
        </div>
    </div>
@endsection

@section('footer')
    @include('inventario::components.footer')
@endsection
