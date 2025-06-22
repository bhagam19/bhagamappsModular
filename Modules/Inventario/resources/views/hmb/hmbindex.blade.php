@extends('adminlte::page')

@section('title', 'Inventario')

@section('content_header')
    @include('inventario::components.encabezado')
@endsection

@section('content')

    <h3>Historial de Modificaciones de Bienes</h3>

    @if (session('info'))
        <div class="alert alert-success">
            <strong>{{ session('info') }}</strong>
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            @livewire('hmb.hmb-index')
        </div>
    </div>
@endsection
