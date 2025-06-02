{{-- Encabezado --}}
<header>
    <div class="encabezado-acta d-flex align-items-center justify-content-center">

        <div class="row align-items-center">
            <div class="col-2 text-center">
                <img class="img-escudo img-fluid" src="{{ asset('vendor/adminlte/dist/img/escudo.png') }}"
                    alt="Escudo" />
            </div>

            <div class="col-10" style="line-height: 1;">
                <p class="text-center mb-2 font-weight-bold" style="font-size: 12px;">INSTITUCIÓN EDUCATIVA ENTRERRÍOS</p>
                <p class="text-justify mb-2 font-italic" style="font-size: 11px;">
                    Constituida y autorizados sus estudios por Resolución Departamental 1490 del 20 de febrero
                    de 2003 y mediante la cual se le concede <span class="font-weight-bold">Reconocimiento de
                        Carácter Oficial</span>; autorizados sus estudios para Educación Formal de Adultos por
                    Resolución
                    12339 del 13 de junio de 2006; y aclaradas sus jornadas y modelos por Resolución
                    Departamental S201500286893 del 1 de julio de 2015.
                </p>
                <p class="text-right mb-0 font-weight-bold" style="font-size: 10px;">DANE 105264000013 - NIT
                    811044496-0</p>
            </div>

            {{-- Separador de colores --}}
            <div id="separadorColores" class="separador-colores my-2 mx-auto w-100">
                <hr class="linea-verde" />
                <hr class="linea-blanca" />
                <hr class="linea-azul" />
            </div>

            <p class="d-flex justify-content-center align-items-center font-weight-bold pt-1 pb-1 w-100 mb-2 ">
                ACTA DE ENTREGA DE INVENTARIO - {{ $nombreCompleto }} {{-- - PÁGINA {{ $pagina }} --}}
            </p>
        </div>
    </div>
</header>
