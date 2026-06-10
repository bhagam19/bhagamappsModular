@extends('adminlte::page')

@section('title', 'Ubicaciones — Inventario')

@section('content_header')
    <h1>Catálogo de Ubicaciones</h1>
@endsection

@section('content')
    <div class="card">
        <div class="card-body">
            @livewire('catalogos.ubicaciones-index')
        </div>
    </div>
@endsection

@section('footer')
    @include('inventario::components.footer')
@endsection
