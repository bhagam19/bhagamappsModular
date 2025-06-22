<!-- Versión Escritorio -->
<div id="appsContenedor" class="text-center mt-5 pt-5 d-none d-md-block">
    <h2 class="mb-4" style="color: #3a3f8c; font-weight: bold; font-size: 20px; text-shadow: 0 0 10px #666;">
        Aplicaciones disponibles
    </h2>
    <div class="d-flex flex-wrap justify-content-center">
        @forelse ($apps as $app)
            <div class="boton mx-4 my-5 p-3 border rounded shadow-sm text-center"
                style="width: 100px; border-color: #0a0e46; background: #bfc3f8; transition: all 0.3s ease; cursor: {{ $app->habilitada ? 'pointer' : 'default' }};"
                @if ($app->habilitada) onmouseover="this.style.background='#d0d4fc';this.style.borderColor='#3a3f8c';this.style.boxShadow='1px 1px 10px 2px #3a3f8c';this.style.transform='translateY(-5px)'; this.querySelector('p').style.color='#3a3f8c'; this.querySelector('p').style.textShadow='0 0 10px #3a3f8c'; this.querySelector('img').style.border='2px solid #3a3f8c'; this.querySelector('img').style.boxShadow='0 0 15px #3a3f8c';"
                    onmouseout="this.style.background='#bfc3f8';this.style.borderColor='#0a0e46';this.style.boxShadow='0 0 10px rgba(51, 79, 88, 0.2)';this.style.transform='none'; this.querySelector('p').style.color='#6c757d'; this.querySelector('p').style.textShadow='none'; this.querySelector('img').style.border='1px solid #666'; this.querySelector('img').style.boxShadow='1px 1px 5px #8a8a8a';" @endif>
                @if ($app->habilitada)
                    <a href="{{ $app->ruta }}" class="text-decoration-none d-block">
                        <img src="{{ asset($app->imagen) }}" alt="{{ $app->nombre }}"
                            style="height: 60px; width: 60px; margin: 0 auto; border-radius: 15px; border: 1px solid #666; box-shadow: 1px 1px 5px #8a8a8a; transition: all 0.3s ease;">
                        <p class="mt-2" style="font-size: 10px; color: #6c757d; transition: color 0.3s ease;">
                            {{ $app->nombre }}
                        </p>
                    </a>
                @else
                    <div class="opacity-50" style="pointer-events: none;">
                        <img src="{{ asset($app->imagen) }}" alt="{{ $app->nombre }}"
                            style="height: 60px; width: 60px; margin: 0 auto; border-radius: 15px; border: 1px solid #666; box-shadow: 1px 1px 5px #8a8a8a;">
                        <p class="mt-2" style="font-size: 10px; color: #999;">{{ $app->nombre }}</p>
                    </div>
                @endif
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
            <div class="boton d-flex align-items-center my-2 p-2 border rounded shadow-sm"
                style="width: 100%; height: 100px; border-color: #0a0e46; background: #bfc3f8; cursor: {{ $app->habilitada ? 'pointer' : 'default' }};"
                @if ($app->habilitada) onmouseover="this.style.background='#d0d4fc';this.style.borderColor='#3a3f8c';this.style.boxShadow='1px 1px 10px 2px #3a3f8c';this.style.transform='translateY(-3px)'; this.querySelector('p').style.color='#3a3f8c'; this.querySelector('p').style.textShadow='0 0 10px #3a3f8c'; this.querySelector('img').style.border='2px solid #3a3f8c'; this.querySelector('img').style.boxShadow='0 0 15px #3a3f8c';"
                    onmouseout="this.style.background='#bfc3f8';this.style.borderColor='#0a0e46';this.style.boxShadow='0 0 10px rgba(51, 79, 88, 0.2)';this.style.transform='none'; this.querySelector('p').style.color='black'; this.querySelector('p').style.textShadow='none'; this.querySelector('img').style.border='1px solid #ebe6ec'; this.querySelector('img').style.boxShadow='1px 1px 5px #ebe6ec';" @endif>
                @if ($app->habilitada)
                    <a href="{{ $app->ruta }}" class="d-flex align-items-center text-decoration-none w-100 h-100">
                        <img src="{{ asset($app->imagen) }}" alt="{{ $app->nombre }}"
                            style="height: 90px; width: 90px; border-radius: 5px; box-shadow: 1px 1px 5px #ebe6ec;">
                        <p class="mb-0 ms-3 flex-grow-1" style="font-size: 1.5em; color: black; text-align: left;">
                            {{ $app->nombre }}
                        </p>
                    </a>
                @else
                    <div class="d-flex align-items-center opacity-50 w-100 h-100" style="pointer-events: none;">
                        <img src="{{ asset($app->imagen) }}" alt="{{ $app->nombre }}"
                            style="height: 90px; width: 90px; border-radius: 5px; box-shadow: 1px 1px 5px #ebe6ec;">
                        <p class="mb-0 ms-3 flex-grow-1" style="font-size: 1.5em; color: #666; text-align: left;">
                            {{ $app->nombre }}
                        </p>
                    </div>
                @endif
            </div>
        @empty
            <p class="text-muted text-center">No tienes aplicaciones disponibles.</p>
        @endforelse
    </div>
</div>
