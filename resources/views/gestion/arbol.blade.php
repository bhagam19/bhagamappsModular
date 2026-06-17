<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Estructura Institucional — Guía 34 MEN
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

            @foreach ($gestiones as $gestion)
                <div x-data="{ abierto: false }" class="mb-4 bg-white rounded-lg shadow">
                    <button
                        @click="abierto = !abierto"
                        class="w-full flex items-center justify-between px-5 py-4 text-left focus:outline-none"
                    >
                        <div class="flex items-center gap-3">
                            <span class="inline-flex items-center justify-center w-12 h-8 rounded bg-indigo-600 text-white text-xs font-bold tracking-wide">
                                {{ $gestion->codigo }}
                            </span>
                            <span class="text-base font-semibold text-gray-800">{{ $gestion->nombre }}</span>
                        </div>
                        <svg :class="abierto ? 'rotate-90' : ''" class="w-4 h-4 text-gray-400 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </button>

                    <div x-show="abierto" x-transition class="border-t border-gray-100 px-5 pb-4">
                        @foreach ($gestion->procesos as $proceso)
                            <div x-data="{ expandido: false }" class="mt-3">
                                <button
                                    @click="expandido = !expandido"
                                    class="w-full flex items-center gap-2 text-left py-2 px-3 rounded hover:bg-gray-50 transition"
                                >
                                    <span class="inline-flex items-center justify-center w-16 h-6 rounded bg-indigo-100 text-indigo-700 text-xs font-semibold">
                                        {{ $proceso->codigo }}
                                    </span>
                                    <span class="text-sm font-medium text-gray-700">{{ $proceso->nombre }}</span>
                                    <svg :class="expandido ? 'rotate-90' : ''" class="ml-auto w-3 h-3 text-gray-400 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </button>

                                <div x-show="expandido" x-transition class="mt-1 pl-8 space-y-1">
                                    @foreach ($proceso->componentes as $componente)
                                        <div class="flex items-center gap-2 py-1 px-2 rounded text-sm text-gray-600">
                                            <span class="text-indigo-400 font-mono text-xs w-20 shrink-0">{{ $componente->codigo }}</span>
                                            <span>{{ $componente->nombre }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach

        </div>
    </div>
</x-app-layout>
