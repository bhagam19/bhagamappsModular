@extends('adminlte::page')

@section('title', 'Inventario')

@section('content_header')
    <h1>Listado de Bienes</h1>
@endsection

@section('content')

    @if (session('info'))
        <div class="alert alert-success">
            <strong>{{ session('info') }}</strong>
        </div>
    @endif

    <div class="card">        
        <div class="card-body"> 
            @livewire('bienes.bienes-index')
        </div>
    </div>
@endsection
