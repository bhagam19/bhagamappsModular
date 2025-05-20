@extends('adminlte::page')

@section('title', 'Panel de Administración')

@section('content_header')
    <h1>Crear Grupo</h1>
@endsection

@section('content')
    @livewire('grupo.crear-grupo')
@endsection
