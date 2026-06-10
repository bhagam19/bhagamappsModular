<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="{{ route('inventario.bienes.index') }}"
                   class="flex items-center gap-1.5 text-sm text-gray-400 hover:text-gray-700 transition-colors">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Inventario
                </a>
                <span class="text-gray-300">/</span>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Bienes asignados — {{ $usuario->name }}
                </h2>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-4">

            <div class="bg-white shadow sm:rounded-lg px-6 py-4 text-sm text-gray-600 flex items-center gap-3">
                <div>
                    <p class="font-medium text-gray-900">{{ $usuario->name }}</p>
                    <p class="text-xs text-gray-500">{{ $usuario->email }}</p>
                </div>
                <span class="ml-auto text-xs text-gray-400">
                    {{ $bienes->total() }} {{ $bienes->total() === 1 ? 'bien asignado' : 'bienes asignados' }}
                </span>
            </div>

            <div class="bg-white shadow sm:rounded-lg overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Código</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Bien</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Categoría</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Ubicación</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Desde</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($bienes as $registro)
                        <tr>
                            <td class="px-6 py-4 text-xs font-mono text-gray-500 whitespace-nowrap">{{ $registro->bien->codigo_institucional }}</td>
                            <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $registro->bien->nombre }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ $registro->bien->categoria?->nombre ?? '—' }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ $registro->bien->ubicacion?->nombre ?? '—' }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600 whitespace-nowrap">{{ $registro->fecha_asignacion->format('d/m/Y') }}</td>
                            <td class="px-6 py-4 text-right text-sm whitespace-nowrap">
                                @can(\App\Auth\Capacidad::InventarioBienesVer->value)
                                <a href="{{ route('inventario.bienes.show', $registro->bien) }}"
                                   class="text-gray-500 hover:text-gray-800">Ver bien</a>
                                @endcan
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-10 text-center text-sm text-gray-500">
                                Este usuario no tiene bienes asignados actualmente.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($bienes->hasPages())
            <div>{{ $bienes->links() }}</div>
            @endif

        </div>
    </div>
</x-admin-layout>
