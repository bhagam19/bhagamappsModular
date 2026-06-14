@extends('adminlte::page')

@section('title', 'Importar Snapshot Institucional — IEE')

@section('content_header')
    <h1>
        <i class="fas fa-file-import mr-2 text-info"></i>Importar Snapshot Institucional
        <small class="text-muted ml-2" style="font-size:0.6em;">Administración del Sistema</small>
    </h1>
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('apps.apps.index') }}">Inicio</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.backups.index') }}">Backups</a></li>
        <li class="breadcrumb-item active">Importar Snapshot</li>
    </ol>
@endsection

@section('content')
    @livewire('backups.importar-snapshot')
@endsection

@section('footer')
    @include('adminsistema::components.footer')
@endsection
