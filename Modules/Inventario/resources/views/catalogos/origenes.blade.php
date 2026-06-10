@extends('adminlte::page')

@section('title', 'Orígenes — Inventario')

@section('content_header')
    <h1>Catálogo de Orígenes de Bien</h1>
@endsection

@section('content')
    <div class="card">
        <div class="card-body">
            @livewire('catalogos.origenes-index')
        </div>
    </div>
@endsection

@section('footer')
    @include('inventario::components.footer')
@endsection
