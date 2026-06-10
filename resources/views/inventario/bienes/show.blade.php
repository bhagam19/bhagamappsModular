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
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Ficha del Bien</h2>
            </div>
            @can(\App\Auth\Capacidad::InventarioBienesEditar->value)
            <a href="{{ route('inventario.bienes.edit', $bien) }}"
               class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition-colors">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                Editar
            </a>
            @endcan
        </div>
    </x-slot>

    @php
        $coloresBien = [
            'nuevo'   => ['badge' => 'bg-blue-100 text-blue-700',   'hero' => '#3b82f6', 'glow' => 'rgba(59,130,246,.25)'],
            'bueno'   => ['badge' => 'bg-green-100 text-green-700', 'hero' => '#22c55e', 'glow' => 'rgba(34,197,94,.25)'],
            'regular' => ['badge' => 'bg-yellow-100 text-yellow-700','hero' => '#eab308','glow' => 'rgba(234,179,8,.25)'],
            'malo'    => ['badge' => 'bg-red-100 text-red-700',     'hero' => '#ef4444', 'glow' => 'rgba(239,68,68,.25)'],
        ];
        $coloresMant = [
            'al_dia'                 => ['badge' => 'bg-green-100 text-green-700',  'hex' => '#22c55e'],
            'requiere_mantenimiento' => ['badge' => 'bg-yellow-100 text-yellow-700','hex' => '#eab308'],
            'mantenimiento_urgente'  => ['badge' => 'bg-orange-100 text-orange-700','hex' => '#f97316'],
            'dar_de_baja'            => ['badge' => 'bg-red-100 text-red-700',      'hex' => '#ef4444'],
        ];
        $estadoBienKey  = $bien->estado_bien?->value ?? 'bueno';
        $estadoMantKey  = $bien->estado_mantenimiento?->value ?? 'al_dia';
        $heroBienColor  = $coloresBien[$estadoBienKey]['hero']  ?? '#6366f1';
        $heroBienGlow   = $coloresBien[$estadoBienKey]['glow']  ?? 'rgba(99,102,241,.25)';
        $heroMantColor  = $coloresMant[$estadoMantKey]['hex']   ?? '#22c55e';
        $esUrgente      = in_array($estadoMantKey, ['mantenimiento_urgente', 'dar_de_baja']);
    @endphp

    <style>
        .ficha-hero {
            background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 60%, #0c1445 100%);
        }
        .ficha-dots {
            background-image: radial-gradient(circle, rgba(255,255,255,.2) 1px, transparent 1px);
            background-size: 20px 20px;
        }
        .ficha-glow {
            box-shadow: 0 0 40px {{ $heroBienGlow }}, 0 0 80px {{ $heroBienGlow }};
        }
        .ficha-field {
            display: flex; flex-direction: column; gap: .2rem;
        }
        .ficha-label {
            font-size: .7rem; font-weight: 600; text-transform: uppercase;
            letter-spacing: .06em; color: #94a3b8;
        }
        .ficha-value {
            font-size: .875rem; color: #1e293b; line-height: 1.5;
        }
        .ficha-value.mono { font-family: ui-monospace, SFMono-Regular, monospace; }
        .ficha-value.empty { color: #cbd5e1; font-style: italic; }

        .ficha-section {
            background: #fff; border-radius: .875rem;
            border: 1px solid #f1f5f9;
        }
        .ficha-section-header {
            display: flex; align-items: center; gap: .625rem;
            padding: 1rem 1.25rem; border-bottom: 1px solid #f8fafc;
        }
        .ficha-section-icon {
            width: 1.75rem; height: 1.75rem; border-radius: .5rem;
            display: flex; align-items: center; justify-content: center; flex-shrink: 0;
        }
        .ficha-section-title {
            font-size: .7rem; font-weight: 700; text-transform: uppercase;
            letter-spacing: .07em; color: #64748b;
        }
        .ficha-grid { display: grid; gap: 1.25rem; padding: 1.25rem; }

        .status-card {
            border-radius: .75rem; padding: 1.25rem;
            display: flex; align-items: flex-start; gap: 1rem;
        }
        .status-dot {
            width: .625rem; height: .625rem; border-radius: 50%;
            margin-top: .35rem; flex-shrink: 0;
        }
        .pulse-anim { animation: ficha-pulse 2s ease-in-out infinite; }
        @keyframes ficha-pulse { 0%,100%{opacity:1} 50%{opacity:.3} }

        .img-thumb {
            aspect-ratio: 1; border-radius: .75rem; overflow: hidden;
            background: #f8fafc; border: 1px solid #f1f5f9;
            position: relative; cursor: zoom-in; transition: transform .15s;
        }
        .img-thumb:hover { transform: scale(1.02); }
        .img-delete {
            position: absolute; top: .4rem; right: .4rem;
            background: rgba(239,68,68,.85); color: #fff; border: none;
            border-radius: .375rem; padding: .25rem .5rem;
            font-size: .65rem; font-weight: 600; cursor: pointer;
            opacity: 0; transition: opacity .15s;
        }
        .img-thumb:hover .img-delete { opacity: 1; }

        .meta-badge {
            display: inline-flex; align-items: center; gap: .375rem;
            padding: .25rem .625rem; border-radius: 9999px;
            font-size: .7rem; font-weight: 600; background: #f8fafc;
            color: #64748b; border: 1px solid #f1f5f9;
        }
        .meta-dot { width: .375rem; height: .375rem; border-radius: 50%; background: #cbd5e1; }
    </style>

    <div class="py-6">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-4">

            {{-- ── FLASH ── --}}
            @if(session('status'))
            @php
                $mensajes = [
                    'bien-actualizado'  => 'Bien actualizado correctamente.',
                    'imagen-registrada' => 'Imagen registrada correctamente.',
                    'imagen-eliminada'  => 'Imagen eliminada correctamente.',
                ];
            @endphp
            <div class="flex items-center gap-2.5 px-4 py-3 rounded-xl text-sm font-medium
                        bg-green-50 text-green-700 border border-green-200">
                <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                {{ $mensajes[session('status')] ?? session('status') }}
            </div>
            @endif

            {{-- ══════════════════════════════════════
                 HERO
            ══════════════════════════════════════ --}}
            <div class="ficha-hero rounded-2xl shadow-2xl overflow-hidden">
                <div class="relative p-6 sm:p-8">
                    <div class="absolute inset-0 ficha-dots" style="opacity:.05;"></div>

                    {{-- Barra de color según estado --}}
                    <div class="absolute top-0 left-0 right-0 h-1 ficha-glow"
                         style="background:{{ $heroBienColor }};"></div>

                    <div class="relative flex flex-col sm:flex-row sm:items-start gap-5">

                        {{-- Ícono / Avatar del bien --}}
                        <div class="shrink-0 w-16 h-16 rounded-2xl flex items-center justify-center"
                             style="background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.1);">
                            <svg class="h-8 w-8" style="color:{{ $heroBienColor }};"
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                      d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>
                        </div>

                        {{-- Nombre y código --}}
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-mono font-bold tracking-widest mb-1"
                               style="color:{{ $heroBienColor }};">
                                {{ $bien->codigo_institucional }}
                            </p>
                            <h1 class="text-2xl font-black text-white leading-snug truncate">
                                {{ $bien->nombre }}
                            </h1>
                            @if($bien->descripcion)
                            <p class="mt-1 text-sm leading-relaxed" style="color:#94a3b8;">
                                {{ $bien->descripcion }}
                            </p>
                            @endif
                        </div>

                        {{-- Badges de estado --}}
                        <div class="shrink-0 flex flex-col gap-2">
                            <div class="flex items-center gap-2 px-3 py-2 rounded-xl"
                                 style="background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.1);">
                                <span class="w-2 h-2 rounded-full shrink-0"
                                      style="background:{{ $heroBienColor }};"></span>
                                <div>
                                    <p class="text-xs" style="color:#64748b;">Estado físico</p>
                                    <p class="text-sm font-bold text-white leading-none">
                                        {{ $bien->estado_bien?->etiqueta() ?? '—' }}
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2 px-3 py-2 rounded-xl"
                                 style="background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.1);">
                                <span class="w-2 h-2 rounded-full shrink-0 {{ $esUrgente ? 'pulse-anim' : '' }}"
                                      style="background:{{ $heroMantColor }};"></span>
                                <div>
                                    <p class="text-xs" style="color:#64748b;">Mantenimiento</p>
                                    <p class="text-sm font-bold text-white leading-none">
                                        {{ $bien->estado_mantenimiento?->etiqueta() ?? '—' }}
                                    </p>
                                </div>
                            </div>
                        </div>

                    </div>

                    {{-- Meta fechas --}}
                    <div class="relative mt-5 pt-4 flex flex-wrap gap-3"
                         style="border-top:1px solid rgba(255,255,255,.07);">
                        <span class="meta-badge">
                            <span class="meta-dot"></span>
                            Registrado {{ $bien->created_at->format('d/m/Y H:i') }}
                        </span>
                        <span class="meta-badge">
                            <span class="meta-dot"></span>
                            Actualizado {{ $bien->updated_at->format('d/m/Y H:i') }}
                        </span>
                        @if($bien->codigo_origen)
                        <span class="meta-badge" style="font-family:monospace;">
                            <span class="meta-dot"></span>
                            Origen: {{ $bien->codigo_origen }}
                        </span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- ══════════════════════════════════════
                 FILA: Clasificación + Adquisición
            ══════════════════════════════════════ --}}
            <div class="grid sm:grid-cols-2 gap-4">

                {{-- Clasificación --}}
                <div class="ficha-section">
                    <div class="ficha-section-header">
                        <div class="ficha-section-icon" style="background:#eef2ff;">
                            <svg class="h-3.5 w-3.5" style="color:#6366f1;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                            </svg>
                        </div>
                        <span class="ficha-section-title">Clasificación</span>
                    </div>
                    <div class="ficha-grid" style="grid-template-columns:1fr;">
                        <div class="ficha-field">
                            <span class="ficha-label">Categoría</span>
                            @if($bien->categoria)
                                @can(\App\Auth\Capacidad::InventarioCategoriasVer->value)
                                <a href="{{ route('inventario.categorias.show', $bien->categoria) }}"
                                   class="ficha-value inline-flex items-center gap-1.5 text-indigo-600 hover:text-indigo-800 font-medium">
                                    {{ $bien->categoria->nombre }}
                                    <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                    </svg>
                                </a>
                                @else
                                <span class="ficha-value">{{ $bien->categoria->nombre }}</span>
                                @endcan
                            @else
                                <span class="ficha-value empty">Sin categoría</span>
                            @endif
                        </div>
                        <div class="ficha-field">
                            <span class="ficha-label">Ubicación física</span>
                            @if($bien->ubicacion)
                                @can(\App\Auth\Capacidad::InventarioUbicacionesVer->value)
                                <a href="{{ route('inventario.ubicaciones.show', $bien->ubicacion) }}"
                                   class="ficha-value inline-flex items-center gap-1.5 text-indigo-600 hover:text-indigo-800 font-medium">
                                    {{ $bien->ubicacion->nombre }}
                                    <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                    </svg>
                                </a>
                                @else
                                <span class="ficha-value">{{ $bien->ubicacion->nombre }}</span>
                                @endcan
                            @else
                                <span class="ficha-value empty">Sin ubicación asignada</span>
                            @endif
                        </div>
                        @if($bien->ubicacion?->responsable)
                        <div class="ficha-field">
                            <span class="ficha-label">Responsable de ubicación</span>
                            <span class="ficha-value">{{ $bien->ubicacion->responsable->nombre ?? '—' }}</span>
                        </div>
                        @endif
                    </div>
                </div>

                {{-- Adquisición --}}
                <div class="ficha-section">
                    <div class="ficha-section-header">
                        <div class="ficha-section-icon" style="background:#f0fdf4;">
                            <svg class="h-3.5 w-3.5" style="color:#22c55e;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <span class="ficha-section-title">Adquisición</span>
                    </div>
                    <div class="ficha-grid" style="grid-template-columns:1fr 1fr;">
                        <div class="ficha-field">
                            <span class="ficha-label">Fecha</span>
                            <span class="ficha-value {{ !$bien->fecha_adquisicion ? 'empty' : '' }}">
                                {{ $bien->fecha_adquisicion?->format('d/m/Y') ?? 'No registrada' }}
                            </span>
                        </div>
                        <div class="ficha-field">
                            <span class="ficha-label">Valor</span>
                            <span class="ficha-value {{ $bien->valor_adquisicion === null ? 'empty' : 'font-semibold' }}">
                                @if($bien->valor_adquisicion !== null)
                                    ${{ number_format((float) $bien->valor_adquisicion, 2, ',', '.') }}
                                @else
                                    No registrado
                                @endif
                            </span>
                        </div>
                        @if($bien->observaciones)
                        <div class="ficha-field" style="grid-column:span 2;">
                            <span class="ficha-label">Observaciones</span>
                            <span class="ficha-value">{{ $bien->observaciones }}</span>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- ══════════════════════════════════════
                 Características técnicas
            ══════════════════════════════════════ --}}
            <div class="ficha-section">
                <div class="ficha-section-header">
                    <div class="ficha-section-icon" style="background:#fff7ed;">
                        <svg class="h-3.5 w-3.5" style="color:#f97316;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M9 3H5a2 2 0 00-2 2v4m6-6h10a2 2 0 012 2v4M9 3v18m0 0h10a2 2 0 002-2V9M9 21H5a2 2 0 01-2-2V9m0 0h18"/>
                        </svg>
                    </div>
                    <span class="ficha-section-title">Características técnicas</span>
                    @if(!$bien->detalle)
                    <span class="ml-auto text-xs text-gray-300 italic">Sin detalle registrado</span>
                    @endif
                </div>
                <div class="ficha-grid" style="grid-template-columns:repeat(2,1fr);">
                    <div class="ficha-field">
                        <span class="ficha-label">Número de serie</span>
                        <span class="ficha-value mono {{ !$bien->detalle?->serie ? 'empty' : '' }}">
                            {{ $bien->detalle?->serie ?? 'No registrado' }}
                        </span>
                    </div>
                    <div class="ficha-field">
                        <span class="ficha-label">Color</span>
                        <span class="ficha-value {{ !$bien->detalle?->color ? 'empty' : '' }}">
                            {{ $bien->detalle?->color ?? 'No registrado' }}
                        </span>
                    </div>
                    <div class="ficha-field">
                        <span class="ficha-label">Material</span>
                        <span class="ficha-value {{ !$bien->detalle?->material ? 'empty' : '' }}">
                            {{ $bien->detalle?->material ?? 'No registrado' }}
                        </span>
                    </div>
                    <div class="ficha-field">
                        <span class="ficha-label">Tamaño / Dimensiones</span>
                        <span class="ficha-value {{ !$bien->detalle?->tamano ? 'empty' : '' }}">
                            {{ $bien->detalle?->tamano ?? 'No registrado' }}
                        </span>
                    </div>
                    @if($bien->detalle?->caracteristicas_especiales)
                    <div class="ficha-field" style="grid-column:span 2;">
                        <span class="ficha-label">Características especiales</span>
                        <span class="ficha-value">{{ $bien->detalle->caracteristicas_especiales }}</span>
                    </div>
                    @endif
                </div>
            </div>

            {{-- ══════════════════════════════════════
                 Imágenes
            ══════════════════════════════════════ --}}
            <div class="ficha-section">
                <div class="ficha-section-header">
                    <div class="ficha-section-icon" style="background:#f5f3ff;">
                        <svg class="h-3.5 w-3.5" style="color:#8b5cf6;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <span class="ficha-section-title">Evidencia fotográfica</span>
                    <span class="ml-auto text-xs font-semibold px-2 py-0.5 rounded-full"
                          style="background:#f5f3ff;color:#8b5cf6;">
                        {{ $bien->imagenes->count() }} {{ $bien->imagenes->count() === 1 ? 'imagen' : 'imágenes' }}
                    </span>
                </div>

                <div class="p-4 space-y-4">

                    @if($bien->imagenes->isNotEmpty())
                    <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-5 gap-3">
                        @foreach($bien->imagenes->sortBy('orden') as $imagen)
                        <div class="img-thumb group">
                            <img src="{{ $imagen->ruta }}"
                                 alt="{{ $bien->nombre }}"
                                 class="w-full h-full object-cover"
                                 onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
                            <div style="display:none;position:absolute;inset:0;"
                                 class="items-center justify-center flex-col gap-1">
                                <svg class="h-6 w-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                          d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01"/>
                                </svg>
                                <span class="text-xs text-gray-300">Sin imagen</span>
                            </div>
                            <span class="absolute bottom-1.5 left-1.5 text-xs font-mono font-semibold px-1.5 py-0.5 rounded"
                                  style="background:rgba(0,0,0,.55);color:rgba(255,255,255,.85);">
                                #{{ $imagen->orden }}
                            </span>
                            @can(\App\Auth\Capacidad::InventarioBienesEditar->value)
                            <form method="POST"
                                  action="{{ route('inventario.bienes.imagenes.destroy', [$bien, $imagen]) }}"
                                  onsubmit="return confirm('¿Eliminar esta imagen?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="img-delete">✕ Eliminar</button>
                            </form>
                            @endcan
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div class="flex flex-col items-center justify-center py-10 rounded-xl"
                         style="background:#fafafa;border:2px dashed #f1f5f9;">
                        <svg class="h-10 w-10 text-gray-200 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                                  d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <p class="text-sm text-gray-300">Sin evidencia fotográfica registrada</p>
                    </div>
                    @endif

                    @can(\App\Auth\Capacidad::InventarioBienesEditar->value)
                    <form method="POST" action="{{ route('inventario.bienes.imagenes.store', $bien) }}"
                          class="flex flex-wrap items-end gap-3 pt-3"
                          style="border-top:1px solid #f8fafc;">
                        @csrf
                        <div class="flex-1 min-w-48">
                            <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1.5">
                                Ruta de imagen
                            </label>
                            <x-text-input name="ruta" type="text"
                                class="mt-0 block w-full"
                                placeholder="/storage/bienes/imagen.jpg"
                                required maxlength="500" />
                            <x-input-error :messages="$errors->get('ruta')" class="mt-1" />
                        </div>
                        <div class="w-24">
                            <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1.5">
                                Orden
                            </label>
                            <x-text-input name="orden" type="number"
                                class="mt-0 block w-full"
                                value="{{ ($bien->imagenes->max('orden') ?? -1) + 1 }}"
                                min="0" required />
                            <x-input-error :messages="$errors->get('orden')" class="mt-1" />
                        </div>
                        <button type="submit"
                                class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition-colors">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Agregar imagen
                        </button>
                    </form>
                    @endcan

                </div>
            </div>

            {{-- ══════════════════════════════════════
                 Custodio actual (IMPL-INV-003)
            ══════════════════════════════════════ --}}
            @can(\App\Auth\Capacidad::InventarioResponsablesVer->value)
            <div class="ficha-section">
                <div class="ficha-section-header" style="justify-content:space-between;">
                    <div style="display:flex;align-items:center;gap:.625rem;">
                        <div class="ficha-section-icon" style="background:#f0fdf4;">
                            <svg class="h-3.5 w-3.5" style="color:#22c55e;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                        </div>
                        <span class="ficha-section-title">Custodio</span>
                    </div>
                    <a href="{{ route('inventario.bienes.responsable.historial', $bien) }}"
                       class="text-xs text-indigo-600 hover:text-indigo-800">Ver historial →</a>
                </div>
                <div class="p-4">
                    @if($bien->responsableActual?->usuario)
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-900">{{ $bien->responsableActual->usuario->name }}</p>
                            <p class="text-xs text-gray-500">{{ $bien->responsableActual->usuario->email }}</p>
                            <p class="text-xs text-gray-400 mt-1">
                                Desde {{ $bien->responsableActual->fecha_asignacion->format('d/m/Y') }}
                            </p>
                        </div>
                        @can(\App\Auth\Capacidad::InventarioResponsablesTransferir->value)
                        <a href="{{ route('inventario.bienes.responsable.transferir', $bien) }}"
                           class="text-xs px-2 py-1 rounded border border-amber-300 text-amber-700 hover:bg-amber-50 transition-colors">
                            Transferir
                        </a>
                        @endcan
                    </div>
                    @else
                    <div class="flex items-center justify-between">
                        <p class="text-sm text-gray-400 italic">Sin custodio asignado.</p>
                        @can(\App\Auth\Capacidad::InventarioResponsablesAsignar->value)
                        <a href="{{ route('inventario.bienes.responsable.asignar', $bien) }}"
                           class="text-xs px-2 py-1 rounded border border-indigo-300 text-indigo-700 hover:bg-indigo-50 transition-colors">
                            Asignar
                        </a>
                        @endcan
                    </div>
                    @endif
                </div>
            </div>
            @endcan

        </div>
    </div>
</x-admin-layout>
