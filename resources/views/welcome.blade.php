<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=0.6, maximum-scale=1.0, minimum-scale=0.2">
    
    <link rel="icon" href="{{ asset('aIcon.ico') }}" type="image/x-icon">

    {{-- Carga de estilos y scripts comunes --}}
    @include('dashboard_personal.css-js')

    <title>@yield('title', "Bhagam's Apps")</title>
</head>
<body>
    <header>       
        @include('dashboard_personal.header')           
    </header>

    <main>
        @yield('content')
    </main>

    <footer class="footer-flotante">
        @include('dashboard_personal.footer')         
        @include('dashboard_personal.selectorTemas')  
    </footer>
    @livewireScripts
</body>
</html>


