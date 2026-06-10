@extends('adminlte::page')

@section('title', 'Dependencias — Inventario')

@section('content_header')
    <h1>Catálogo de Dependencias</h1>
@endsection

@section('content')
    <div class="card">
        <div class="card-body">
            @livewire('catalogos.dependencias-index')
        </div>
    </div>
@endsection

@section('footer')
    @include('inventario::components.footer')
@endsection
