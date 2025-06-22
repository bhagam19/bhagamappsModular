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
            <h5 class="border rounded col-12 col-md-4 p-1 shadow-sm bg-white fw-bold">Inventario de Bienes</h5>
            @livewire('bienes.bienes-index')
        </div>
    </div>
@endsection

@section('footer')
    @include('inventario::components.footer')
@endsection
