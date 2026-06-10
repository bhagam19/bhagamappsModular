<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Inventario de Bienes
            </h2>
            @can(\App\Auth\Capacidad::InventarioBienesCrear->value)
            <a href="{{ route('inventario.bienes.create') }}"
               class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700 transition-colors">
                Registrar bien
            </a>
            @endcan
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">

            @if (session('status'))
                <div class="p-4 bg-green-100 border border-green-300 text-green-800 sm:rounded-lg text-sm">
                    @php
                        $mensajes = [
                            'bien-creado'      => 'Bien registrado correctamente.',
                            'bien-actualizado' => 'Bien actualizado correctamente.',
                        ];
                    @endphp
                    {{ $mensajes[session('status')] ?? session('status') }}
                </div>
            @endif

            {{-- Filtros --}}
            <div class="bg-white shadow sm:rounded-lg p-4">
                <form method="GET" action="{{ route('inventario.bienes.index') }}"
                      class="flex flex-wrap items-end gap-3">

                    <div class="flex-1 min-w-48">
                        <label class="block text-xs font-medium text-gray-600 mb-1">Nombre</label>
                        <input type="search" name="q" value="{{ request('q') }}"
                               placeholder="Buscar por nombre..."
                               class="block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
                    </div>

                    <div class="w-44">
                        <label class="block text-xs font-medium text-gray-600 mb-1">Categoría</label>
                        <select name="categoria"
                                class="block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
                            <option value="">Todas</option>
                            @foreach($categorias as $cat)
                                <option value="{{ $cat->id }}"
                                    {{ request('categoria') == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="w-40">
                        <label class="block text-xs font-medium text-gray-600 mb-1">Estado físico</label>
                        <select name="estado"
                                class="block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
                            <option value="">Todos</option>
                            @foreach(\App\Enums\Inventario\EstadoBien::cases() as $e)
                                <option value="{{ $e->value }}"
                                    {{ request('estado') === $e->value ? 'selected' : '' }}>
                                    {{ $e->etiqueta() }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="w-52">
                        <label class="block text-xs font-medium text-gray-600 mb-1">Mantenimiento</label>
                        <select name="mantenimiento"
                                class="block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
                            <option value="">Todos</option>
                            @foreach(\App\Enums\Inventario\EstadoMantenimiento::cases() as $e)
                                <option value="{{ $e->value }}"
                                    {{ request('mantenimiento') === $e->value ? 'selected' : '' }}>
                                    {{ $e->etiqueta() }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    @if(request('ubicacion'))
                    <input type="hidden" name="ubicacion" value="{{ request('ubicacion') }}">
                    @endif

                    <div class="flex gap-2">
                        <button type="submit"
                                class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700 transition-colors">
                            Filtrar
                        </button>
                        <a href="{{ route('inventario.bienes.index') }}"
                           class="px-4 py-2 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-50 transition-colors">
                            Limpiar
                        </a>
                    </div>

                </form>

                @if($ubicacionFiltro)
                <div class="mt-3 pt-3 border-t border-gray-100 flex items-center gap-2">
                    <span class="text-xs text-indigo-700 bg-indigo-50 border border-indigo-200 px-2 py-1 rounded-full">
                        Ubicación: {{ $ubicacionFiltro->nombre }}
                    </span>
                    <a href="{{ route('inventario.bienes.index', array_diff_key(request()->all(), ['ubicacion' => ''])) }}"
                       class="text-xs text-gray-400 hover:text-gray-600">
                        × Quitar
                    </a>
                </div>
                @endif

            </div>

            {{-- Contador --}}
            <p class="text-xs text-gray-500 px-1">
                {{ $bienes->total() }} {{ $bienes->total() === 1 ? 'registro' : 'registros' }} encontrados
            </p>

            {{-- Tabla --}}
            <div class="bg-white shadow sm:rounded-lg overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Código</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Nombre</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Categoría</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Ubicación</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Estado</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Mantenimiento</th>
                            @can(\App\Auth\Capacidad::InventarioResponsablesVer->value)
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Custodio</th>
                            @endcan
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($bienes as $bien)
                        @php
                            $coloresBien = [
                                'nuevo'   => 'bg-blue-100 text-blue-700',
                                'bueno'   => 'bg-green-100 text-green-700',
                                'regular' => 'bg-yellow-100 text-yellow-700',
                                'malo'    => 'bg-red-100 text-red-700',
                            ];
                            $coloresMant = [
                                'al_dia'                  => 'bg-green-100 text-green-700',
                                'requiere_mantenimiento'  => 'bg-yellow-100 text-yellow-700',
                                'mantenimiento_urgente'   => 'bg-orange-100 text-orange-700',
                                'dar_de_baja'             => 'bg-red-100 text-red-700',
                            ];
                            $urgenteMant = in_array($bien->estado_mantenimiento?->value, ['mantenimiento_urgente', 'dar_de_baja']);
                        @endphp
                        <tr @class([
                            'bg-yellow-50' => $bien->estado_bien?->value === 'regular',
                            'bg-red-50'    => $bien->estado_bien?->value === 'malo',
                        ])>
                            <td class="px-6 py-4 text-xs font-mono text-gray-500 whitespace-nowrap">{{ $bien->codigo_institucional }}</td>
                            <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $bien->nombre }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ $bien->categoria?->nombre ?? '—' }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ $bien->ubicacion?->nombre ?? '—' }}</td>
                            <td class="px-6 py-4">
                                <span class="text-xs px-2 py-0.5 rounded-full {{ $coloresBien[$bien->estado_bien?->value] ?? 'bg-gray-100 text-gray-500' }}">
                                    {{ $bien->estado_bien?->etiqueta() ?? '—' }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-xs px-2 py-0.5 rounded-full font-{{ $urgenteMant ? 'semibold' : 'normal' }} {{ $coloresMant[$bien->estado_mantenimiento?->value] ?? 'bg-gray-100 text-gray-500' }}">
                                    @if($urgenteMant)&#9888; @endif{{ $bien->estado_mantenimiento?->etiqueta() ?? '—' }}
                                </span>
                            </td>
                            @can(\App\Auth\Capacidad::InventarioResponsablesVer->value)
                            <td class="px-6 py-4 text-sm text-gray-600 whitespace-nowrap">
                                @if($bien->responsableActual?->usuario)
                                <a href="{{ route('inventario.bienes.responsable.historial', $bien) }}"
                                   class="text-indigo-600 hover:text-indigo-800 text-xs">
                                    {{ $bien->responsableActual->usuario->name }}
                                </a>
                                @else
                                <a href="{{ route('inventario.bienes.responsable.historial', $bien) }}"
                                   class="text-xs text-gray-400 italic hover:text-gray-600">Sin custodio</a>
                                @endif
                            </td>
                            @endcan
                            <td class="px-6 py-4 text-right text-sm space-x-3 whitespace-nowrap">
                                @can(\App\Auth\Capacidad::InventarioBienesVer->value)
                                <a href="{{ route('inventario.bienes.show', $bien) }}"
                                   class="text-gray-500 hover:text-gray-800">Ver</a>
                                @endcan
                                @can(\App\Auth\Capacidad::InventarioBienesEditar->value)
                                <a href="{{ route('inventario.bienes.edit', $bien) }}"
                                   class="text-indigo-600 hover:text-indigo-800">Editar</a>
                                @endcan
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="px-6 py-10 text-center text-gray-500">
                                @if(request()->hasAny(['q', 'categoria', 'estado']))
                                    No se encontraron bienes con los filtros aplicados.
                                @else
                                    No hay bienes registrados en el inventario.
                                @endif
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Paginación --}}
            @if($bienes->hasPages())
            <div>
                {{ $bienes->links() }}
            </div>
            @endif

        </div>
    </div>
</x-admin-layout>
