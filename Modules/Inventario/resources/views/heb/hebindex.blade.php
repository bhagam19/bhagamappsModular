@extends('adminlte::page')

@section('title', 'Inventario')

@section('content_header')

@endsection

@section('content')

    @if (session('info'))
        <div class="alert alert-success">
            <strong>{{ session('info') }}</strong>
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <h5 class="border col-12 col-md-4 rounded p-1 shadow-sm bg-white fw-bold">Historial de Eliminación de Bienes</h5>
            @livewire('heb.heb-index')
        </div>
    </div>
@endsection

@section('footer')
    @include('inventario::components.footer')
@endsection
