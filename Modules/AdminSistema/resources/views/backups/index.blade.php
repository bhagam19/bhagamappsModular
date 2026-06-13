@extends('adminlte::page')

@section('title', 'Centro de Administración de Backups — IEE')

@section('content_header')
    <h1>
        <i class="fas fa-database mr-2"></i>Centro de Administración de Backups
        <small class="text-muted ml-2" style="font-size:0.6em;">Administración del Sistema</small>
    </h1>
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('apps.apps.index') }}">Inicio</a></li>
        <li class="breadcrumb-item active">Backups</li>
    </ol>
@endsection

@section('content')
    @livewire('backups.backup-dashboard')
@endsection

@section('footer')
    @include('adminsistema::components.footer')
@endsection
