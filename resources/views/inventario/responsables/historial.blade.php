<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="{{ route('inventario.bienes.show', $bien) }}"
                   class="flex items-center gap-1.5 text-sm text-gray-400 hover:text-gray-700 transition-colors">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    {{ $bien->nombre }}
                </a>
                <span class="text-gray-300">/</span>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Responsable y Custodio</h2>
            </div>
            <div class="flex items-center gap-2">
                @if($actual)
                    @can(\App\Auth\Capacidad::InventarioResponsablesTransferir->value)
                    <a href="{{ route('inventario.bienes.responsable.transferir', $bien) }}"
                       class="inline-flex items-center px-4 py-2 bg-amber-500 text-white text-sm font-medium rounded-md hover:bg-amber-600 transition-colors">
                        Transferir custodio
                    </a>
                    @endcan
                @else
                    @can(\App\Auth\Capacidad::InventarioResponsablesAsignar->value)
                    <a href="{{ route('inventario.bienes.responsable.asignar', $bien) }}"
                       class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700 transition-colors">
                        Asignar responsable
                    </a>
                    @endcan
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if(session('status'))
            <div class="p-4 bg-green-100 border border-green-300 text-green-800 sm:rounded-lg text-sm">
                @php
                    $mensajes = [
                        'responsable-asignado'   => 'Responsable asignado correctamente.',
                        'responsable-transferido' => 'Custodio transferido correctamente.',
                    ];
                @endphp
                {{ $mensajes[session('status')] ?? session('status') }}
            </div>
            @endif

            {{-- Responsable vigente --}}
            <div class="bg-white shadow sm:rounded-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-2">
                    <div class="h-2 w-2 rounded-full {{ $actual ? 'bg-green-500' : 'bg-gray-300' }}"></div>
                    <h3 class="font-semibold text-gray-800">Custodio actual</h3>
                </div>
                <div class="px-6 py-5">
                    @if($actual)
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="font-medium text-gray-900">{{ $actual->usuario->name }}</p>
                            <p class="text-sm text-gray-500">{{ $actual->usuario->email }}</p>
                            <p class="text-xs text-gray-400 mt-1">
                                Asignado el {{ $actual->fecha_asignacion->format('d/m/Y') }}
                                por {{ $actual->asignadoPor->name }}
                            </p>
                        </div>
                        <span class="text-xs px-2 py-0.5 rounded-full bg-green-100 text-green-700 font-medium">Vigente</span>
                    </div>
                    @else
                    <p class="text-sm text-gray-500 italic">Este bien no tiene responsable asignado.</p>
                    @endif
                </div>
            </div>

            {{-- Historial --}}
            <div class="bg-white shadow sm:rounded-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h3 class="font-semibold text-gray-800">Historial de responsables</h3>
                </div>
                @if($historial->isEmpty())
                <div class="px-6 py-10 text-center text-sm text-gray-500">
                    No hay registros de responsables para este bien.
                </div>
                @else
                <div class="divide-y divide-gray-50">
                    @foreach($historial as $registro)
                    <div class="px-6 py-4 flex items-start justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-900">{{ $registro->usuario->name }}</p>
                            <p class="text-xs text-gray-500">{{ $registro->usuario->email }}</p>
                            <p class="text-xs text-gray-400 mt-0.5">
                                {{ $registro->fecha_asignacion->format('d/m/Y') }}
                                @if($registro->fecha_retiro)
                                    → {{ $registro->fecha_retiro->format('d/m/Y') }}
                                @endif
                                · Registrado por {{ $registro->asignadoPor->name }}
                            </p>
                        </div>
                        @if($registro->estaVigente())
                        <span class="text-xs px-2 py-0.5 rounded-full bg-green-100 text-green-700 font-medium">Vigente</span>
                        @else
                        <span class="text-xs px-2 py-0.5 rounded-full bg-gray-100 text-gray-500">Retirado</span>
                        @endif
                    </div>
                    @endforeach
                </div>
                @endif
            </div>

        </div>
    </div>
</x-admin-layout>
