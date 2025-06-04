<header>
    <div class="encabezado-acta" style="display: flex; align-items: center; justify-content: center;">

        <div style="display: flex; flex-wrap: wrap; align-items: center; width: 100%;">
            <div style="width: 16.6666%; text-align: center;">
                <img src="{{ asset('vendor/adminlte/dist/img/escudo.png') }}" alt="Escudo"
                    style="max-width: 100%; height: auto; position: relative; width: 90px; height: 90px;" />
            </div>

            <div style="width: 83.3333%; line-height: 1;">
                <p style="text-align: center; margin-bottom: 0.5rem; font-weight: bold; font-size: 12px;">
                    INSTITUCIÓN EDUCATIVA ENTRERRÍOS
                </p>
                <p style="text-align: justify; margin-bottom: 0.5rem; font-style: italic; font-size: 11px;">
                    Constituida y autorizados sus estudios por Resolución Departamental 1490 del 20 de febrero
                    de 2003 y mediante la cual se le concede <span style="font-weight: bold;">Reconocimiento de
                        Carácter Oficial</span>; autorizados sus estudios para Educación Formal de Adultos por
                    Resolución
                    12339 del 13 de junio de 2006; y aclaradas sus jornadas y modelos por Resolución
                    Departamental S201500286893 del 1 de julio de 2015.
                </p>
                <p style="text-align: right; margin-bottom: 0; font-weight: bold; font-size: 10px;">
                    DANE 105264000013 - NIT 811044496-0
                </p>
            </div>

            {{-- Separador de colores --}}
            <div id="separadorColores"
                style="margin-top: 0.5rem; margin-bottom: 0.5rem; margin-left: auto; margin-right: auto; width: 100%;">
                <div style="background-color: #013801; height: 0.8mm; margin: 0;"></div>
                <div style="background-color: white; height: 0.8mm; margin: 0;"></div>
                <div style="background-color: #01018a; height: 0.8mm; margin: 0;"></div>
            </div>

            <p
                style="display: flex; justify-content: center; align-items: center; font-weight: bold; padding-top: 0.25rem; padding-bottom: 0.25rem; width: 100%; margin-bottom: 0.5rem;">
                ACTA DE ENTREGA DE INVENTARIO - {{ $nombreCompleto }} - PÁGINA {{ $pagina }}
            </p>
        </div>
    </div>
</header>
