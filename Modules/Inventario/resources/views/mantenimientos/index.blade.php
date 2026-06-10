@extends('adminlte::page')

@section('title', 'Mantenimientos Programados — Inventario')

@section('content_header')
    <h1>Mantenimientos Programados de Bienes</h1>
@endsection

@section('content')
    <div class="card">
        <div class="card-body">
            @livewire('mantenimientos.mantenimientos-programados-index')
        </div>
    </div>
@endsection

@section('footer')
    @include('inventario::components.footer')
@endsection
