@extends('adminlte::page')

@section('title', 'Panel de Administración')

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
            <h5 class="border rounded col-12 col-md-4 p-2 shadow-sm bg-white fw-bold">Lista de Permisos</h5>
            @livewire('permissions.permissions-index')
        </div>
    </div>
@endsection

@section('footer')
    @include('users::components.footer')
@endsection
