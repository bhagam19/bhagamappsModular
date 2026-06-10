@extends('adminlte::page')

@section('title', 'Almacenamientos — Inventario')

@section('content_header')
    <h1>Catálogo de Almacenamientos</h1>
@endsection

@section('content')
    <div class="card">
        <div class="card-body">
            @livewire('catalogos.almacenamientos-index')
        </div>
    </div>
@endsection

@section('footer')
    @include('inventario::components.footer')
@endsection
