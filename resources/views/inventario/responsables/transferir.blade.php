<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('inventario.bienes.responsable.historial', $bien) }}"
               class="flex items-center gap-1.5 text-sm text-gray-400 hover:text-gray-700 transition-colors">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Custodio
            </a>
            <span class="text-gray-300">/</span>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Transferir custodio</h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8 space-y-4">

            @if($actual)
            <div class="bg-amber-50 border border-amber-200 rounded-lg px-5 py-4 text-sm text-amber-800">
                <p class="font-medium mb-1">Custodio actual</p>
                <p>{{ $actual->usuario->name }} — {{ $actual->usuario->email }}</p>
                <p class="text-xs text-amber-600 mt-1">Asignado desde {{ $actual->fecha_asignacion->format('d/m/Y') }}</p>
            </div>
            @else
            <div class="bg-red-50 border border-red-200 rounded-lg px-5 py-4 text-sm text-red-800">
                Este bien no tiene un responsable vigente. No se puede realizar la transferencia.
            </div>
            @endif

            <div class="bg-white shadow sm:rounded-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100">
                    <p class="text-sm text-gray-600">
                        Bien: <span class="font-medium text-gray-800">{{ $bien->nombre }}</span>
                        <span class="font-mono text-xs text-gray-400 ml-2">{{ $bien->codigo_institucional }}</span>
                    </p>
                </div>

                <form method="POST" action="{{ route('inventario.bienes.responsable.transferir.store', $bien) }}" class="px-6 py-6 space-y-5">
                    @csrf

                    @if($errors->any())
                    <div class="p-4 bg-red-50 border border-red-200 rounded-md text-sm text-red-700">
                        {{ $errors->first() }}
                    </div>
                    @endif

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Fecha de retiro del custodio actual <span class="text-red-500">*</span></label>
                        <input type="date" name="fecha_retiro_anterior"
                               value="{{ old('fecha_retiro_anterior', now()->toDateString()) }}" required
                               min="{{ $actual?->fecha_asignacion->toDateString() }}"
                               class="block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm @error('fecha_retiro_anterior') border-red-300 @enderror">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nuevo responsable <span class="text-red-500">*</span></label>
                        <select name="user_id" required
                                class="block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm @error('user_id') border-red-300 @enderror">
                            <option value="">Seleccionar usuario...</option>
                            @foreach($usuarios as $usuario)
                            <option value="{{ $usuario->id }}" {{ old('user_id') == $usuario->id ? 'selected' : '' }}>
                                {{ $usuario->name }} — {{ $usuario->email }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Fecha de asignación del nuevo responsable <span class="text-red-500">*</span></label>
                        <input type="date" name="fecha_asignacion"
                               value="{{ old('fecha_asignacion', now()->toDateString()) }}" required
                               class="block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm @error('fecha_asignacion') border-red-300 @enderror">
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-2 border-t border-gray-100">
                        <a href="{{ route('inventario.bienes.responsable.historial', $bien) }}"
                           class="px-4 py-2 text-sm text-gray-600 hover:text-gray-900">
                            Cancelar
                        </a>
                        <button type="submit" {{ !$actual ? 'disabled' : '' }}
                                class="px-4 py-2 bg-amber-500 text-white text-sm font-medium rounded-md hover:bg-amber-600 transition-colors disabled:opacity-50">
                            Confirmar transferencia
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-admin-layout>
