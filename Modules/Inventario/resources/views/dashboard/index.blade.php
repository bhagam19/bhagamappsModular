@extends('adminlte::page')

@section('title', 'Dashboard Ejecutivo — Inventario IEE')

@section('content_header')
    <h1>
        <i class="fas fa-boxes mr-2"></i>Inventario IEE
        <small class="text-muted ml-2" style="font-size:0.6em;">Dashboard Ejecutivo</small>
    </h1>
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('apps.apps.index') }}">Inicio</a></li>
        <li class="breadcrumb-item active">Inventario — Dashboard</li>
    </ol>
@endsection

@section('plugins.js')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
@endsection

@section('content')
    @livewire('dashboard.inventario-dashboard')
@endsection

@section('footer')
    @include('inventario::components.footer')
@endsection
