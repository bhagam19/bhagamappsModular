@extends('adminlte::page')

@section('title', 'Administrador')

@section('content_header')
    <h1>INICIO</h1>
@stop

@section('content')
    <p>Aplicaciones</p>
    @include('apps::index', ['apps' => $apps])
@stop

@push('css')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
@endpush


@section('js')
    <script>
        console.log("Hi, I'm using the Laravel-AdminLTE package!");
    </script>
@stop
