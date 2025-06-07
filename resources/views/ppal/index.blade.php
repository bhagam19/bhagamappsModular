@extends('adminlte::page')

@section('title', 'Administrador')

@section('content_header')
    <h1>Panel de Administración</h1>
@stop

@section('content')
    <p>Bienvenido al panel de Administración.</p>
    @include('apps::index', ['apps' => $apps])
@stop

@section('css')
    {{-- Add here extra stylesheets --}}
    {{-- <link rel="stylesheet" href="/css/admin_custom.css"> --}}
@stop

@section('js')
    <script>
        console.log("Hi, I'm using the Laravel-AdminLTE package!");
    </script>
@stop
