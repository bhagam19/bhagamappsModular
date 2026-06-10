@extends('adminlte::page')

@section('title', 'Historial de Ubicaciones — Inventario')

@section('content_header')
    <h1>Historial de Ubicaciones de Bienes</h1>
@endsection

@section('content')
    <div class="card">
        <div class="card-body">
            @livewire('ubicaciones.historial-ubicaciones-bien')
        </div>
    </div>
@endsection

@section('footer')
    @include('inventario::components.footer')
@endsection
