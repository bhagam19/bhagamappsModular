<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Operación Institucional — Actividades y Tareas
        </h2>
    </x-slot>

    <div class="py-6" x-data>
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">

            @if (session('success'))
                <div class="bg-green-50 border border-green-200 text-green-800 text-sm rounded-lg px-4 py-3">
                    {{ session('success') }}
                </div>
            @endif

            @forelse ($metas as $meta)
                <div class="bg-white shadow rounded-lg overflow-hidden"
                     x-data="{ abierto: false, nuevaAct: false }">

                    {{-- Cabecera Meta --}}
                    <button @click="abierto = !abierto"
                        class="w-full flex items-center justify-between px-6 py-4 bg-amber-600 text-white text-left hover:bg-amber-700 transition">
                        <div class="flex items-center gap-3">
                            <span class="font-mono text-sm bg-amber-800 px-2 py-0.5 rounded">{{ $meta->codigo }}</span>
                            <span class="font-semibold">{{ $meta->nombre }}</span>
                            @if ($meta->actividades->count())
                                <span class="text-xs bg-amber-500 px-2 py-0.5 rounded-full">
                                    {{ $meta->actividades->count() }} {{ Str::plural('actividad', $meta->actividades->count()) }}
                                </span>
                            @endif
                        </div>
                        <div class="flex items-center gap-3">
                            <button @click.stop="nuevaAct = !nuevaAct"
                                class="text-xs bg-white text-amber-700 hover:bg-amber-50 font-semibold px-3 py-1 rounded transition">
                                + Actividad
                            </button>
                            <svg :class="abierto ? 'rotate-180' : ''" class="w-5 h-5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </div>
                    </button>

                    {{-- Formulario Nueva Actividad --}}
                    <div x-show="nuevaAct" x-cloak class="border-b border-amber-100 bg-amber-50 px-6 py-4">
                        <p class="text-xs font-semibold text-amber-700 mb-3 uppercase tracking-wide">Nueva Actividad para {{ $meta->codigo }}</p>
                        <form action="{{ route('operacion.actividades.store') }}" method="POST">
                            @csrf
                            <input type="hidden" name="meta_id" value="{{ $meta->id }}">
                            <input type="hidden" name="componente_id" value="{{ $meta->componente_id }}">
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Código <span class="text-red-500">*</span></label>
                                    <input type="text" name="codigo" placeholder="ACT-GD01-001" required maxlength="20"
                                        class="w-full text-sm border border-gray-300 rounded px-3 py-1.5 focus:ring-1 focus:ring-amber-400 focus:border-amber-400">
                                </div>
                                <div class="lg:col-span-2">
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Nombre <span class="text-red-500">*</span></label>
                                    <input type="text" name="nombre" required maxlength="250"
                                        class="w-full text-sm border border-gray-300 rounded px-3 py-1.5 focus:ring-1 focus:ring-amber-400 focus:border-amber-400">
                                </div>
                                <div class="md:col-span-2 lg:col-span-3">
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Descripción</label>
                                    <textarea name="descripcion" rows="2"
                                        class="w-full text-sm border border-gray-300 rounded px-3 py-1.5 focus:ring-1 focus:ring-amber-400 focus:border-amber-400"></textarea>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Estado <span class="text-red-500">*</span></label>
                                    <select name="estado" required
                                        class="w-full text-sm border border-gray-300 rounded px-3 py-1.5 focus:ring-1 focus:ring-amber-400 focus:border-amber-400">
                                        @foreach ($estados as $e)
                                            <option value="{{ $e }}" {{ $e === 'Pendiente' ? 'selected' : '' }}>{{ $e }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Avance manual (%)</label>
                                    <input type="number" name="avance_manual" value="0" min="0" max="100"
                                        class="w-full text-sm border border-gray-300 rounded px-3 py-1.5 focus:ring-1 focus:ring-amber-400 focus:border-amber-400">
                                </div>
                                <div>
                                    {{-- spacer --}}
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Fecha inicio</label>
                                    <input type="date" name="fecha_inicio"
                                        class="w-full text-sm border border-gray-300 rounded px-3 py-1.5 focus:ring-1 focus:ring-amber-400 focus:border-amber-400">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Fecha fin</label>
                                    <input type="date" name="fecha_fin"
                                        class="w-full text-sm border border-gray-300 rounded px-3 py-1.5 focus:ring-1 focus:ring-amber-400 focus:border-amber-400">
                                </div>
                            </div>
                            <div class="mt-3 flex gap-2">
                                <button type="submit"
                                    class="text-sm bg-amber-600 hover:bg-amber-700 text-white font-semibold px-4 py-1.5 rounded transition">
                                    Guardar Actividad
                                </button>
                                <button type="button" @click="nuevaAct = false"
                                    class="text-sm bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold px-4 py-1.5 rounded transition">
                                    Cancelar
                                </button>
                            </div>
                        </form>
                    </div>

                    {{-- Lista de Actividades --}}
                    <div x-show="abierto" x-cloak class="divide-y divide-gray-100">

                        @forelse ($meta->actividades as $actividad)
                            <div class="px-6 py-3"
                                 x-data="{ abiertoAct: false, editarAct: false, nuevaTarea: false }">

                                {{-- Cabecera Actividad --}}
                                <div class="flex items-center justify-between">
                                    <button @click="abiertoAct = !abiertoAct"
                                        class="flex items-center gap-2 text-left group flex-1 min-w-0">
                                        <svg :class="abiertoAct ? 'rotate-180' : ''" class="w-4 h-4 text-gray-400 flex-shrink-0 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                        </svg>
                                        <span class="font-mono text-xs bg-indigo-50 text-indigo-600 px-1.5 py-0.5 rounded flex-shrink-0">{{ $actividad->codigo }}</span>
                                        <span class="text-sm font-medium text-gray-800 group-hover:text-indigo-700 truncate">{{ $actividad->nombre }}</span>
                                    </button>
                                    <div class="flex items-center gap-2 flex-shrink-0 ml-3">
                                        @php
                                            $colorEstado = match($actividad->estado) {
                                                'Completada'  => 'bg-green-100 text-green-700',
                                                'En Proceso'  => 'bg-blue-100 text-blue-700',
                                                'Suspendida'  => 'bg-yellow-100 text-yellow-700',
                                                'Cancelada'   => 'bg-red-100 text-red-700',
                                                default       => 'bg-gray-100 text-gray-600',
                                            };
                                        @endphp
                                        <span class="text-xs font-medium {{ $colorEstado }} px-2 py-0.5 rounded-full">{{ $actividad->estado }}</span>
                                        <div class="flex items-center gap-1">
                                            <div class="w-16 bg-gray-200 rounded-full h-1.5">
                                                <div class="bg-indigo-500 h-1.5 rounded-full" style="width: {{ $actividad->avance_calculado }}%"></div>
                                            </div>
                                            <span class="text-xs text-gray-500">{{ $actividad->avance_calculado }}%</span>
                                        </div>
                                        <button @click="editarAct = !editarAct"
                                            class="text-xs text-indigo-600 hover:text-indigo-800 font-medium px-2 py-1 rounded hover:bg-indigo-50 transition">
                                            Editar
                                        </button>
                                        <button @click="abiertoAct = true; nuevaTarea = !nuevaTarea"
                                            class="text-xs text-emerald-600 hover:text-emerald-800 font-medium px-2 py-1 rounded hover:bg-emerald-50 transition">
                                            + Tarea
                                        </button>
                                    </div>
                                </div>

                                {{-- Formulario Editar Actividad --}}
                                <div x-show="editarAct" x-cloak class="mt-3 bg-indigo-50 border border-indigo-100 rounded-lg p-4">
                                    <p class="text-xs font-semibold text-indigo-700 mb-3 uppercase tracking-wide">Editar Actividad</p>
                                    <form action="{{ route('operacion.actividades.update', $actividad) }}" method="POST">
                                        @csrf @method('PUT')
                                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                                            <div class="lg:col-span-2">
                                                <label class="block text-xs font-medium text-gray-600 mb-1">Nombre <span class="text-red-500">*</span></label>
                                                <input type="text" name="nombre" value="{{ $actividad->nombre }}" required maxlength="250"
                                                    class="w-full text-sm border border-gray-300 rounded px-3 py-1.5 focus:ring-1 focus:ring-indigo-400 focus:border-indigo-400">
                                            </div>
                                            <div>
                                                <label class="block text-xs font-medium text-gray-600 mb-1">Estado <span class="text-red-500">*</span></label>
                                                <select name="estado" required
                                                    class="w-full text-sm border border-gray-300 rounded px-3 py-1.5 focus:ring-1 focus:ring-indigo-400 focus:border-indigo-400">
                                                    @foreach ($estados as $e)
                                                        <option value="{{ $e }}" {{ $actividad->estado === $e ? 'selected' : '' }}>{{ $e }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="md:col-span-2 lg:col-span-3">
                                                <label class="block text-xs font-medium text-gray-600 mb-1">Descripción</label>
                                                <textarea name="descripcion" rows="2"
                                                    class="w-full text-sm border border-gray-300 rounded px-3 py-1.5 focus:ring-1 focus:ring-indigo-400 focus:border-indigo-400">{{ $actividad->descripcion }}</textarea>
                                            </div>
                                            <div>
                                                <label class="block text-xs font-medium text-gray-600 mb-1">Avance manual (%)</label>
                                                <input type="number" name="avance_manual" value="{{ $actividad->avance_manual }}" min="0" max="100"
                                                    class="w-full text-sm border border-gray-300 rounded px-3 py-1.5 focus:ring-1 focus:ring-indigo-400 focus:border-indigo-400">
                                            </div>
                                            <div>
                                                <label class="block text-xs font-medium text-gray-600 mb-1">Fecha inicio</label>
                                                <input type="date" name="fecha_inicio" value="{{ $actividad->fecha_inicio?->format('Y-m-d') }}"
                                                    class="w-full text-sm border border-gray-300 rounded px-3 py-1.5 focus:ring-1 focus:ring-indigo-400 focus:border-indigo-400">
                                            </div>
                                            <div>
                                                <label class="block text-xs font-medium text-gray-600 mb-1">Fecha fin</label>
                                                <input type="date" name="fecha_fin" value="{{ $actividad->fecha_fin?->format('Y-m-d') }}"
                                                    class="w-full text-sm border border-gray-300 rounded px-3 py-1.5 focus:ring-1 focus:ring-indigo-400 focus:border-indigo-400">
                                            </div>
                                        </div>
                                        <div class="mt-3 flex gap-2">
                                            <button type="submit"
                                                class="text-sm bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-4 py-1.5 rounded transition">
                                                Guardar Cambios
                                            </button>
                                            <button type="button" @click="editarAct = false"
                                                class="text-sm bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold px-4 py-1.5 rounded transition">
                                                Cancelar
                                            </button>
                                        </div>
                                    </form>
                                </div>

                                {{-- Contenido Actividad --}}
                                <div x-show="abiertoAct" x-cloak class="mt-3 ml-6 space-y-2">

                                    {{-- Formulario Nueva Tarea --}}
                                    <div x-show="nuevaTarea" x-cloak class="bg-emerald-50 border border-emerald-100 rounded-lg p-4 mb-3">
                                        <p class="text-xs font-semibold text-emerald-700 mb-3 uppercase tracking-wide">Nueva Tarea para {{ $actividad->codigo }}</p>
                                        <form action="{{ route('operacion.tareas.store', $actividad) }}" method="POST">
                                            @csrf
                                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                                                <div>
                                                    <label class="block text-xs font-medium text-gray-600 mb-1">Código <span class="text-red-500">*</span></label>
                                                    <input type="text" name="codigo" placeholder="TAR-GD01-001-01" required maxlength="25"
                                                        class="w-full text-sm border border-gray-300 rounded px-3 py-1.5 focus:ring-1 focus:ring-emerald-400 focus:border-emerald-400">
                                                </div>
                                                <div class="lg:col-span-2">
                                                    <label class="block text-xs font-medium text-gray-600 mb-1">Nombre <span class="text-red-500">*</span></label>
                                                    <input type="text" name="nombre" required maxlength="250"
                                                        class="w-full text-sm border border-gray-300 rounded px-3 py-1.5 focus:ring-1 focus:ring-emerald-400 focus:border-emerald-400">
                                                </div>
                                                <div class="md:col-span-2 lg:col-span-3">
                                                    <label class="block text-xs font-medium text-gray-600 mb-1">Descripción</label>
                                                    <textarea name="descripcion" rows="2"
                                                        class="w-full text-sm border border-gray-300 rounded px-3 py-1.5 focus:ring-1 focus:ring-emerald-400 focus:border-emerald-400"></textarea>
                                                </div>
                                                <div>
                                                    <label class="block text-xs font-medium text-gray-600 mb-1">Tipo de responsable</label>
                                                    <select name="responsable_tipo" x-data x-ref="tipoResp"
                                                        @change="$dispatch('tipo-changed-{{ $actividad->id }}-new', $event.target.value)"
                                                        class="w-full text-sm border border-gray-300 rounded px-3 py-1.5 focus:ring-1 focus:ring-emerald-400 focus:border-emerald-400">
                                                        <option value="">— Sin responsable —</option>
                                                        <option value="usuario">Usuario</option>
                                                        <option value="rol">Rol</option>
                                                        <option value="dependencia">Dependencia</option>
                                                    </select>
                                                </div>
                                                <div x-data="{ tipo: '' }" @tipo-changed-{{ $actividad->id }}-new.window="tipo = $event.detail">
                                                    <label class="block text-xs font-medium text-gray-600 mb-1">Responsable</label>
                                                    <select name="responsable_id"
                                                        class="w-full text-sm border border-gray-300 rounded px-3 py-1.5 focus:ring-1 focus:ring-emerald-400 focus:border-emerald-400">
                                                        <option value="">— Seleccione —</option>
                                                        <template x-if="tipo === 'usuario'">
                                                            <template x-for="u in {{ json_encode($usuarios->map(fn($u) => ['id' => $u->id, 'nombre' => $u->nombres . ' ' . $u->apellidos])->values()) }}" :key="u.id">
                                                                <option :value="u.id" x-text="u.nombre"></option>
                                                            </template>
                                                        </template>
                                                        <template x-if="tipo === 'rol'">
                                                            <template x-for="r in {{ json_encode($roles->map(fn($r) => ['id' => $r->id, 'nombre' => $r->nombre])->values()) }}" :key="r.id">
                                                                <option :value="r.id" x-text="r.nombre"></option>
                                                            </template>
                                                        </template>
                                                        <template x-if="tipo === 'dependencia'">
                                                            <template x-for="d in {{ json_encode($dependencias->map(fn($d) => ['id' => $d->id, 'nombre' => $d->nombre])->values()) }}" :key="d.id">
                                                                <option :value="d.id" x-text="d.nombre"></option>
                                                            </template>
                                                        </template>
                                                    </select>
                                                </div>
                                                <div>
                                                    <label class="block text-xs font-medium text-gray-600 mb-1">Estado <span class="text-red-500">*</span></label>
                                                    <select name="estado" required
                                                        class="w-full text-sm border border-gray-300 rounded px-3 py-1.5 focus:ring-1 focus:ring-emerald-400 focus:border-emerald-400">
                                                        @foreach ($estados as $e)
                                                            <option value="{{ $e }}" {{ $e === 'Pendiente' ? 'selected' : '' }}>{{ $e }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div>
                                                    <label class="block text-xs font-medium text-gray-600 mb-1">Avance (%)</label>
                                                    <input type="number" name="avance" value="0" min="0" max="100"
                                                        class="w-full text-sm border border-gray-300 rounded px-3 py-1.5 focus:ring-1 focus:ring-emerald-400 focus:border-emerald-400">
                                                </div>
                                                <div>
                                                    <label class="block text-xs font-medium text-gray-600 mb-1">Fecha inicio</label>
                                                    <input type="date" name="fecha_inicio"
                                                        class="w-full text-sm border border-gray-300 rounded px-3 py-1.5 focus:ring-1 focus:ring-emerald-400 focus:border-emerald-400">
                                                </div>
                                                <div>
                                                    <label class="block text-xs font-medium text-gray-600 mb-1">Fecha fin</label>
                                                    <input type="date" name="fecha_fin"
                                                        class="w-full text-sm border border-gray-300 rounded px-3 py-1.5 focus:ring-1 focus:ring-emerald-400 focus:border-emerald-400">
                                                </div>
                                            </div>
                                            <div class="mt-3 flex gap-2">
                                                <button type="submit"
                                                    class="text-sm bg-emerald-600 hover:bg-emerald-700 text-white font-semibold px-4 py-1.5 rounded transition">
                                                    Guardar Tarea
                                                </button>
                                                <button type="button" @click="nuevaTarea = false"
                                                    class="text-sm bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold px-4 py-1.5 rounded transition">
                                                    Cancelar
                                                </button>
                                            </div>
                                        </form>
                                    </div>

                                    {{-- Lista de Tareas --}}
                                    @forelse ($actividad->tareas as $tarea)
                                        <div class="border border-gray-100 rounded-lg bg-gray-50"
                                             x-data="{ editarTarea: false }">

                                            {{-- Cabecera Tarea --}}
                                            <div class="flex items-center justify-between px-4 py-2.5">
                                                <div class="flex items-center gap-2 min-w-0">
                                                    <span class="font-mono text-xs bg-gray-200 text-gray-600 px-1.5 py-0.5 rounded flex-shrink-0">{{ $tarea->codigo }}</span>
                                                    <span class="text-sm text-gray-700 truncate">{{ $tarea->nombre }}</span>
                                                </div>
                                                <div class="flex items-center gap-2 flex-shrink-0 ml-3">
                                                    @if ($tarea->responsable_tipo)
                                                        <span class="text-xs text-gray-500 hidden md:inline">
                                                            <span class="font-medium">{{ ucfirst($tarea->responsable_tipo) }}:</span>
                                                            {{ $tarea->nombre_responsable }}
                                                        </span>
                                                    @endif
                                                    @php
                                                        $colorTarea = match($tarea->estado) {
                                                            'Completada'  => 'bg-green-100 text-green-700',
                                                            'En Proceso'  => 'bg-blue-100 text-blue-700',
                                                            'Suspendida'  => 'bg-yellow-100 text-yellow-700',
                                                            'Cancelada'   => 'bg-red-100 text-red-700',
                                                            default       => 'bg-gray-200 text-gray-600',
                                                        };
                                                    @endphp
                                                    <span class="text-xs {{ $colorTarea }} px-2 py-0.5 rounded-full font-medium">{{ $tarea->estado }}</span>
                                                    <div class="flex items-center gap-1">
                                                        <div class="w-12 bg-gray-200 rounded-full h-1.5">
                                                            <div class="bg-emerald-500 h-1.5 rounded-full" style="width: {{ $tarea->avance }}%"></div>
                                                        </div>
                                                        <span class="text-xs text-gray-500">{{ $tarea->avance }}%</span>
                                                    </div>
                                                    <button @click="editarTarea = !editarTarea"
                                                        class="text-xs text-gray-500 hover:text-indigo-700 font-medium px-2 py-1 rounded hover:bg-white transition">
                                                        Editar
                                                    </button>
                                                </div>
                                            </div>

                                            {{-- Formulario Editar Tarea --}}
                                            <div x-show="editarTarea" x-cloak class="border-t border-gray-200 bg-white px-4 py-4 rounded-b-lg">
                                                <p class="text-xs font-semibold text-gray-600 mb-3 uppercase tracking-wide">Editar Tarea</p>
                                                <form action="{{ route('operacion.tareas.update', $tarea) }}" method="POST">
                                                    @csrf @method('PUT')
                                                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                                                        <div class="lg:col-span-2">
                                                            <label class="block text-xs font-medium text-gray-600 mb-1">Nombre <span class="text-red-500">*</span></label>
                                                            <input type="text" name="nombre" value="{{ $tarea->nombre }}" required maxlength="250"
                                                                class="w-full text-sm border border-gray-300 rounded px-3 py-1.5 focus:ring-1 focus:ring-gray-400">
                                                        </div>
                                                        <div>
                                                            <label class="block text-xs font-medium text-gray-600 mb-1">Estado <span class="text-red-500">*</span></label>
                                                            <select name="estado" required
                                                                class="w-full text-sm border border-gray-300 rounded px-3 py-1.5 focus:ring-1 focus:ring-gray-400">
                                                                @foreach ($estados as $e)
                                                                    <option value="{{ $e }}" {{ $tarea->estado === $e ? 'selected' : '' }}>{{ $e }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="md:col-span-2 lg:col-span-3">
                                                            <label class="block text-xs font-medium text-gray-600 mb-1">Descripción</label>
                                                            <textarea name="descripcion" rows="2"
                                                                class="w-full text-sm border border-gray-300 rounded px-3 py-1.5 focus:ring-1 focus:ring-gray-400">{{ $tarea->descripcion }}</textarea>
                                                        </div>
                                                        <div>
                                                            <label class="block text-xs font-medium text-gray-600 mb-1">Tipo de responsable</label>
                                                            <select name="responsable_tipo" x-data x-ref="tipoRespEdit{{ $tarea->id }}"
                                                                @change="$dispatch('tipo-changed-edit-{{ $tarea->id }}', $event.target.value)"
                                                                class="w-full text-sm border border-gray-300 rounded px-3 py-1.5 focus:ring-1 focus:ring-gray-400">
                                                                <option value="">— Sin responsable —</option>
                                                                <option value="usuario" {{ $tarea->responsable_tipo === 'usuario' ? 'selected' : '' }}>Usuario</option>
                                                                <option value="rol" {{ $tarea->responsable_tipo === 'rol' ? 'selected' : '' }}>Rol</option>
                                                                <option value="dependencia" {{ $tarea->responsable_tipo === 'dependencia' ? 'selected' : '' }}>Dependencia</option>
                                                            </select>
                                                        </div>
                                                        <div x-data="{ tipo: '{{ $tarea->responsable_tipo ?? '' }}' }"
                                                             @tipo-changed-edit-{{ $tarea->id }}.window="tipo = $event.detail">
                                                            <label class="block text-xs font-medium text-gray-600 mb-1">Responsable</label>
                                                            <select name="responsable_id"
                                                                class="w-full text-sm border border-gray-300 rounded px-3 py-1.5 focus:ring-1 focus:ring-gray-400">
                                                                <option value="">— Seleccione —</option>
                                                                <template x-if="tipo === 'usuario'">
                                                                    <template x-for="u in {{ json_encode($usuarios->map(fn($u) => ['id' => $u->id, 'nombre' => $u->nombres . ' ' . $u->apellidos])->values()) }}" :key="u.id">
                                                                        <option :value="u.id" :selected="u.id === {{ $tarea->responsable_id ?? 'null' }}" x-text="u.nombre"></option>
                                                                    </template>
                                                                </template>
                                                                <template x-if="tipo === 'rol'">
                                                                    <template x-for="r in {{ json_encode($roles->map(fn($r) => ['id' => $r->id, 'nombre' => $r->nombre])->values()) }}" :key="r.id">
                                                                        <option :value="r.id" :selected="r.id === {{ $tarea->responsable_id ?? 'null' }}" x-text="r.nombre"></option>
                                                                    </template>
                                                                </template>
                                                                <template x-if="tipo === 'dependencia'">
                                                                    <template x-for="d in {{ json_encode($dependencias->map(fn($d) => ['id' => $d->id, 'nombre' => $d->nombre])->values()) }}" :key="d.id">
                                                                        <option :value="d.id" :selected="d.id === {{ $tarea->responsable_id ?? 'null' }}" x-text="d.nombre"></option>
                                                                    </template>
                                                                </template>
                                                            </select>
                                                        </div>
                                                        <div>
                                                            <label class="block text-xs font-medium text-gray-600 mb-1">Avance (%)</label>
                                                            <input type="number" name="avance" value="{{ $tarea->avance }}" min="0" max="100"
                                                                class="w-full text-sm border border-gray-300 rounded px-3 py-1.5 focus:ring-1 focus:ring-gray-400">
                                                        </div>
                                                        <div>
                                                            <label class="block text-xs font-medium text-gray-600 mb-1">Fecha inicio</label>
                                                            <input type="date" name="fecha_inicio" value="{{ $tarea->fecha_inicio?->format('Y-m-d') }}"
                                                                class="w-full text-sm border border-gray-300 rounded px-3 py-1.5 focus:ring-1 focus:ring-gray-400">
                                                        </div>
                                                        <div>
                                                            <label class="block text-xs font-medium text-gray-600 mb-1">Fecha fin</label>
                                                            <input type="date" name="fecha_fin" value="{{ $tarea->fecha_fin?->format('Y-m-d') }}"
                                                                class="w-full text-sm border border-gray-300 rounded px-3 py-1.5 focus:ring-1 focus:ring-gray-400">
                                                        </div>
                                                    </div>
                                                    <div class="mt-3 flex gap-2">
                                                        <button type="submit"
                                                            class="text-sm bg-gray-700 hover:bg-gray-800 text-white font-semibold px-4 py-1.5 rounded transition">
                                                            Guardar Cambios
                                                        </button>
                                                        <button type="button" @click="editarTarea = false"
                                                            class="text-sm bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold px-4 py-1.5 rounded transition">
                                                            Cancelar
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>

                                        </div>
                                    @empty
                                        <p class="text-xs text-gray-400 italic pl-2">Sin tareas registradas. Use "+ Tarea" para agregar.</p>
                                    @endforelse

                                </div>

                            </div>
                        @empty
                            <div class="px-6 py-4">
                                <p class="text-xs text-gray-400 italic">Sin actividades registradas. Use "+ Actividad" para agregar.</p>
                            </div>
                        @endforelse

                    </div>

                </div>
            @empty
                <div class="bg-white shadow rounded-lg px-6 py-8 text-center">
                    <p class="text-gray-400 text-sm">No hay metas activas disponibles.</p>
                </div>
            @endforelse

        </div>
    </div>
</x-app-layout>
