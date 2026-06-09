<aside class="main-sidebar {{ config('adminlte.classes_sidebar', 'sidebar-dark-primary elevation-4') }}">

    {{-- Sidebar brand logo --}}
    @if(config('adminlte.logo_img_xl'))
        @include('adminlte::partials.common.brand-logo-xl')
    @else
        @include('adminlte::partials.common.brand-logo-xs')
    @endif

    {{-- Sidebar menu --}}
    <div class="sidebar">
        <nav class="pt-2">
            <ul class="nav nav-pills nav-sidebar flex-column {{ config('adminlte.classes_sidebar_nav', '') }}"
                data-widget="treeview" role="menu"
                @if(config('adminlte.sidebar_nav_animation_speed') != 300)
                    data-animation-speed="{{ config('adminlte.sidebar_nav_animation_speed') }}"
                @endif
                @if(!config('adminlte.sidebar_nav_accordion'))
                    data-accordion="false"
                @endif>
                {{-- Configured sidebar links --}}
                @each('adminlte::partials.sidebar.menu-item', $adminlte->menu('sidebar'), 'item')

                {{-- Mis Módulos — fuente: App::visiblesPara($user) --}}
                @auth
                    @php
                        $appsNavLateral = \Modules\Apps\Entities\App::visiblesPara(auth()->user());
                    @endphp
                    @if($appsNavLateral->isNotEmpty())
                        <li class="nav-header">MIS MÓDULOS</li>
                        @foreach($appsNavLateral as $appNav)
                            <li class="nav-item">
                                <a href="{{ url($appNav->ruta) }}"
                                   class="nav-link {{ request()->is(ltrim($appNav->ruta, '/') . '*') ? 'active' : '' }}">
                                    <i class="{{ $appNav->icono ?? 'fas fa-cube' }} nav-icon"></i>
                                    <p>{{ $appNav->nombre }}</p>
                                </a>
                            </li>
                        @endforeach
                    @endif
                @endauth
            </ul>
        </nav>
    </div>

</aside>
