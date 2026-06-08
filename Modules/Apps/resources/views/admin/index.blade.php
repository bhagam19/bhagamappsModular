@extends('adminlte::page')

@section('title', 'Administración de Aplicaciones')

@section('content_header')
    <h1>
        <i class="fas fa-th-large mr-2"></i>
        Administración de Aplicaciones
    </h1>
@stop

@section('content')
    @livewire('apps.apps-index')
@stop

@section('footer')
    @include('dashboard_personal.footer')
@stop
