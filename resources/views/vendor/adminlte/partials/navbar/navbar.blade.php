@inject('layoutHelper', 'JeroenNoten\LaravelAdminLte\Helpers\LayoutHelper')

<nav
    class="main-header navbar
    {{ config('adminlte.classes_topnav_nav', 'navbar-expand') }}
    {{ config('adminlte.classes_topnav', 'navbar-white navbar-light') }}">

    {{-- Navbar left links --}}
    <ul class="navbar-nav">
        {{-- Left sidebar toggler link --}}
        @include('adminlte::partials.navbar.menu-item-left-sidebar-toggler')

        {{-- Configured left links --}}
        @each('adminlte::partials.navbar.menu-item', $adminlte->menu('navbar-left'), 'item')

        {{-- Custom left links --}}
        @yield('content_top_nav_left')
    </ul>

    <style>
        .appsContenidoCinta {
            font-weight: normal;
            color: var(--OnPasivo);
            /* Color base */
            text-align: center;
            transition: color 0.3s ease, text-shadow 0.3s ease;
            margin: 0 50px 0 0;
        }

        .appsContenidoCinta ul {
            list-style: none;
            display: flex;
            gap: 20px;
            padding: 0;
            margin: 0;
        }

        .appsContenidoCinta li {
            margin: 0 5%;
            padding: 0 100px;
        }

        .appsContenidoCinta li a {
            font-weight: bold;
            text-decoration: none;
            color: var(--RojoOnPasivo);
            /* Color base */
            transition: color 0.3s ease, text-shadow 0.3s ease;
        }
    </style>

    {{-- @include('dashboard_personal.header') --}}



    {{-- Navbar right links --}}
    <ul class="navbar-nav ml-auto">
        {{-- Custom right links --}}
        @yield('content_top_nav_right')

        {{-- Configured right links --}}
        @each('adminlte::partials.navbar.menu-item', $adminlte->menu('navbar-right'), 'item')

        {{-- Notifications dropdown --}}
        {{--
        @auth
            @php
                $esAdmin = auth()->user()->hasRole('Administrador') || auth()->user()->hasRole('Rector');
            @endphp

            @if ($esAdmin)
                <li class="nav-item dropdown">
                    @livewire('hmb.notificaciones-dropdown')
                </li>
            @endif

        @endauth
        --}}

        {{-- User menu link --}}
        @if (Auth::user())
            @if (config('adminlte.usermenu_enabled'))
                @include('adminlte::partials.navbar.menu-item-dropdown-user-menu')
            @else
                @include('adminlte::partials.navbar.menu-item-logout-link')
            @endif
        @endif

        {{-- Right sidebar toggler link --}}
        @if ($layoutHelper->isRightSidebarEnabled())
            @include('adminlte::partials.navbar.menu-item-right-sidebar-toggler')
        @endif



    </ul>

</nav>
