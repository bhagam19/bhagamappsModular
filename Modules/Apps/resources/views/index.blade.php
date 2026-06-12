<!-- Versión Escritorio -->
<div id="appsContenedor" class="text-center mt-5 pt-5 d-none d-md-block">
    <h2 class="mb-4" style="color: #3a3f8c; font-weight: bold; font-size: 20px; text-shadow: 0 0 10px #666;">
        Aplicaciones disponibles
    </h2>
    <div class="d-flex flex-wrap justify-content-center">
        @forelse ($apps as $app)
            <div class="boton mx-4 my-5 p-3 border rounded shadow-sm text-center"
                style="width: 110px; border-color: #0a0e46; background: #bfc3f8; transition: all 0.3s ease; cursor: pointer;"
                onmouseover="this.style.background='#d0d4fc';this.style.borderColor='#3a3f8c';this.style.boxShadow='1px 1px 10px 2px #3a3f8c';this.style.transform='translateY(-5px)'; this.querySelector('p').style.color='#3a3f8c'; this.querySelector('p').style.textShadow='0 0 10px #3a3f8c';"
                onmouseout="this.style.background='#bfc3f8';this.style.borderColor='#0a0e46';this.style.boxShadow='0 0 10px rgba(51,79,88,0.2)';this.style.transform='none'; this.querySelector('p').style.color='#6c757d'; this.querySelector('p').style.textShadow='none';">
                <a href="{{ url($app->ruta) }}" class="text-decoration-none d-block">
                    <div style="height: 64px; width: 64px; margin: 0 auto; border-radius: 16px; background: {{ $app->color }}; display: flex; align-items: center; justify-content: center; box-shadow: 1px 2px 6px rgba(0,0,0,0.3); transition: all 0.3s ease;">
                        <i class="{{ $app->icono }} fa-2x" style="color: #fff;"></i>
                    </div>
                    <p class="mt-2 mb-0" style="font-size: 11px; color: #6c757d; transition: color 0.3s ease; line-height: 1.2;">
                        {{ $app->nombre }}
                    </p>
                </a>
            </div>
        @empty
            <p class="text-muted">No tienes aplicaciones disponibles.</p>
        @endforelse
    </div>
</div>

<!-- Versión Móvil -->
<div id="appsContenedor" class="mt-5 pt-5 px-2 d-block d-md-none">
    <h2 class="mb-4 text-center"
        style="color: #3a3f8c; font-weight: bold; font-size: 20px; text-shadow: 0 0 10px #666;">
        Aplicaciones disponibles
    </h2>
    <div class="d-flex flex-column">
        @forelse ($apps as $app)
            <a href="{{ url($app->ruta) }}" class="text-decoration-none">
                <div class="d-flex align-items-center my-2 p-2 border rounded shadow-sm"
                    style="width: 100%; height: 90px; border-color: #0a0e46; background: #bfc3f8; cursor: pointer; transition: all 0.3s ease;">
                    <div style="height: 64px; width: 64px; min-width: 64px; border-radius: 14px; background: {{ $app->color }}; display: flex; align-items: center; justify-content: center; box-shadow: 1px 2px 6px rgba(0,0,0,0.3);">
                        <i class="{{ $app->icono }} fa-2x" style="color: #fff;"></i>
                    </div>
                    <p class="mb-0 ms-3 flex-grow-1" style="font-size: 1.3em; color: #1a1a2e; font-weight: 500;">
                        {{ $app->nombre }}
                    </p>
                </div>
            </a>
        @empty
            <p class="text-muted text-center">No tienes aplicaciones disponibles.</p>
        @endforelse
    </div>
</div>
