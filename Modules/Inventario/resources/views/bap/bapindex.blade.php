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
            @livewire('bap.bap-index')
        </div>
    </div>
@endsection
