<div id="appsContenedor" class="grid grid-cols-2 md:grid-cols-4 gap-4">
    @forelse ($apps as $app)
        <div class="boton {{ $app->habilitada ? '' : 'deshabilitado' }} p-4 border rounded text-center">
            @if ($app->habilitada)
                <a href="{{ $app->ruta }}">
                    <img src="{{ asset($app->imagen) }}" alt="{{ $app->nombre }}" class="w-16 h-16 mx-auto">
                    <p class="mt-2">{{ $app->nombre }}</p>
                </a>
            @else
                <div class="opacity-50 cursor-not-allowed">
                    <img src="{{ asset($app->imagen) }}" alt="{{ $app->nombre }}" class="w-16 h-16 mx-auto">
                    <p class="mt-2">{{ $app->nombre }}</p>
                </div>
            @endif
        </div>
    @empty
        <p>No tienes aplicaciones disponibles.</p>
    @endforelse
</div>
