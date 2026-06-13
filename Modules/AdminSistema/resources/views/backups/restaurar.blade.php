@extends('adminlte::page')

@section('title', 'Restaurar Snapshot Institucional — IEE')

@section('content_header')
    <h1>
        <i class="fas fa-undo-alt mr-2 text-warning"></i>Restaurar Snapshot Institucional
        <small class="text-muted ml-2" style="font-size:0.6em;">Administración del Sistema</small>
    </h1>
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('apps.apps.index') }}">Inicio</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.backups.index') }}">Backups</a></li>
        <li class="breadcrumb-item active">Restaurar</li>
    </ol>
@endsection

@section('content')
    @livewire('backups.restaurar-backup')
@endsection

@section('footer')
    @include('adminsistema::components.footer')
@endsection
