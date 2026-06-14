@extends('adminlte::page')

@section('title', 'Activity Log — Auditoría Institucional')

@section('content_header')
    <h1>
        <i class="fas fa-history text-secondary mr-2"></i>
        Activity Log
        <small class="text-muted" style="font-size:0.55em;">Auditoría institucional transversal</small>
    </h1>
@endsection

@section('content')
    @livewire('activity-log-index')
@endsection
