<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Acta de Entrega</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .contenedor-acta {
            page-break-after: always;
            display: flex;
            flex-direction: column;
            margin: 1cm 2cm !important;
            padding: 1cm 2cm !important;
            font-family: 'Helvetica', Times, serif;
            font-size: 11pt;
            max-width: 900px;
            min-height: 1200px;
        }

        .contenido-principal {
            flex: 1 0 auto;
        }

        /* Aquí tus estilos para la página del acta */
        .img-escudo {
            position: relative;
            width: 90px;
            height: 90px;
        }

        /* Separadores de colores */
        .linea-verde {
            background-color: #013801;
            height: 0.8mm;
            border: none;
            margin: 0;
        }

        .linea-blanca {
            background-color: white;
            height: 0.8mm;
            border: none;
            margin: 0;
        }

        .linea-azul {
            background-color: #01018a;
            height: 0.8mm;
            border: none;
            margin: 0;
        }
    </style>
</head>

<body>

    <div class="contenedor-acta bg-white shadow rounded border">
        <div class="contenido-principal flex-grow-1 flex-column">

            <header>
                @include('inventario::livewire.actas.encabezado')
            </header>

            <footer>
                @include('inventario::livewire.actas.footer')
            </footer>

            <main>
                @include('inventario::livewire.actas.texto-inicial')
                @include('inventario::livewire.actas.tabla-bienes')
                @include('inventario::livewire.actas.firmas')
            </main>

        </div>
    </div>

</body>

</html>
