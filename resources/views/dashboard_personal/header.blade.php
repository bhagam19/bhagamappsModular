<div id="appsEncabezadoGral">
    <div class="appsName">
        
        <div id="appsBtnMenu" class="appsBtnMenu">
            <span></span>
            <span></span>
            <span></span>
        </div>
        
        <div class="appsLogo">
           <img src="{{ asset('vendor/adminlte/dist/img/aIcon.png') }}" alt="Logo" />
        </div>

        <div class="appsTituloCinta">
            Bhagam<br>Apps
        </div>

    </div>

    <div class="appsContenidoCinta">
        <ul>
            <li>Aplicaciones Educativas</li>
            <li>Versión: 2025.05.17</li>
        </ul>
    </div>

    @if (Route::has('login'))
        
        @auth   
            <div class="log-reg" style="display: flex;">               
                <div class="appsInicioSesionCinta" title="Click para iniciar sesión.">
                    @include('dashboard_personal.menu-nav')
                </div>
            </div>
        @else
            <div class="log-reg" style="display: flex;">            
                <div class="appsInicioSesionCinta" title="Click para iniciar sesión.">
                    <a href="{{ route('login') }}">Login</a>
                </div>
                @if (Route::has('register'))
                    <div class="appsInicioSesionCinta" title="Click para registrarse.">
                        <a href="{{ route('register') }}">Register</a>
                    </div>
                @endif
            </div>
        @endauth
        
    @endif
    
</div>
