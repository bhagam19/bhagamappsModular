@extends('adminlte::page')

@section('title', 'Detalle Respaldo ' . $fecha . ' — IEE')

@section('content_header')
    <h1>
        <i class="fas fa-file-archive mr-2"></i>Detalle del Respaldo
        <small class="text-muted ml-2" style="font-size:0.6em;">{{ $fecha }}</small>
    </h1>
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('apps.apps.index') }}">Inicio</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.backups.index') }}">Backups</a></li>
        <li class="breadcrumb-item active">{{ $fecha }}</li>
    </ol>
@endsection

@section('content')
    @livewire('backups.backup-detalle', ['fecha' => $fecha])
@endsection

@section('footer')
    @include('adminsistema::components.footer')
@endsection
