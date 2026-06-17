<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Planeación Institucional — Objetivos, Metas e Indicadores
        </h2>
    </x-slot>

    <div class="py-6" x-data>
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @foreach ($gestiones as $gestion)
                <div class="bg-white shadow rounded-lg overflow-hidden">

                    {{-- Gestión --}}
                    <div x-data="{ abierto: false }">
                        <button @click="abierto = !abierto"
                            class="w-full flex items-center justify-between px-6 py-4 bg-indigo-700 text-white text-left hover:bg-indigo-800 transition">
                            <span class="font-bold text-lg">
                                {{ $gestion->codigo }} — {{ $gestion->nombre }}
                            </span>
                            <svg :class="abierto ? 'rotate-180' : ''" class="w-5 h-5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>

                        <div x-show="abierto" x-cloak class="divide-y divide-gray-100">

                            @foreach ($gestion->procesos as $proceso)
                                <div x-data="{ abierto: false }" class="px-6 py-3">

                                    {{-- Proceso --}}
                                    <button @click="abierto = !abierto"
                                        class="w-full flex items-center justify-between text-left py-1 group">
                                        <span class="font-semibold text-indigo-700 group-hover:text-indigo-900 text-sm">
                                            {{ $proceso->codigo }} — {{ $proceso->nombre }}
                                        </span>
                                        <svg :class="abierto ? 'rotate-180' : ''" class="w-4 h-4 text-indigo-400 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                        </svg>
                                    </button>

                                    <div x-show="abierto" x-cloak class="mt-2 ml-4 space-y-3">

                                        @forelse ($proceso->objetivos as $objetivo)
                                            <div x-data="{ abierto: false }" class="border-l-2 border-indigo-200 pl-3">

                                                {{-- Objetivo --}}
                                                <button @click="abierto = !abierto"
                                                    class="w-full flex items-start justify-between text-left gap-2 group">
                                                    <div>
                                                        <span class="inline-block text-xs font-mono bg-indigo-50 text-indigo-600 px-1.5 py-0.5 rounded mr-1">{{ $objetivo->codigo }}</span>
                                                        <span class="text-sm text-gray-800 group-hover:text-indigo-700">{{ $objetivo->nombre }}</span>
                                                    </div>
                                                    <svg :class="abierto ? 'rotate-180' : ''" class="w-4 h-4 text-gray-400 flex-shrink-0 mt-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                                    </svg>
                                                </button>

                                                <div x-show="abierto" x-cloak class="mt-2 ml-4 space-y-2">

                                                    @forelse ($objetivo->metas as $meta)
                                                        <div class="border-l-2 border-amber-200 pl-3">

                                                            {{-- Meta --}}
                                                            <div class="text-sm text-gray-700">
                                                                <span class="inline-block text-xs font-mono bg-amber-50 text-amber-700 px-1.5 py-0.5 rounded mr-1">{{ $meta->codigo }}</span>
                                                                {{ $meta->nombre }}
                                                                @if ($meta->valor_objetivo)
                                                                    <span class="ml-1 text-xs text-gray-400">({{ $meta->valor_objetivo }} {{ $meta->unidad }})</span>
                                                                @endif
                                                            </div>

                                                            {{-- Indicadores --}}
                                                            @if ($meta->indicadores->isNotEmpty())
                                                                <div class="mt-1 ml-4 flex flex-wrap gap-1">
                                                                    @foreach ($meta->indicadores as $ind)
                                                                        <span class="inline-flex items-center text-xs bg-emerald-50 text-emerald-700 border border-emerald-200 px-2 py-0.5 rounded-full">
                                                                            <span class="font-mono mr-1">{{ $ind->codigo }}</span>
                                                                            {{ $ind->nombre }}
                                                                            <span class="ml-1 text-emerald-400">· {{ $ind->frecuencia }}</span>
                                                                        </span>
                                                                    @endforeach
                                                                </div>
                                                            @endif

                                                        </div>
                                                    @empty
                                                        <p class="text-xs text-gray-400 italic">Sin metas registradas.</p>
                                                    @endforelse

                                                </div>
                                            </div>
                                        @empty
                                            <p class="text-xs text-gray-400 italic ml-2">Sin objetivos registrados.</p>
                                        @endforelse

                                    </div>
                                </div>
                            @endforeach

                        </div>
                    </div>

                </div>
            @endforeach

        </div>
    </div>
</x-app-layout>
