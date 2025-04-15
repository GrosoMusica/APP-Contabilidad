<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="{{ url('/') }}">
            <i class="fas fa-home"></i>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('compradores.*') || request()->routeIs('comprador.*') ? 'active' : '' }}" href="{{ route('compradores.index') }}">COMPRADORES</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('lotes.*') ? 'active' : '' }}" href="{{ route('lotes.index') }}">LOTES</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('pagos.*') ? 'active' : '' }}" href="{{ route('pagos.index') }}">PAGOS</a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle {{ request()->routeIs('informes.*') || request()->routeIs('morosos') || request()->routeIs('proximos.finalizar') ? 'active' : '' }}" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        INFORMES
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="{{ route('informes.index') }}">MES ACTUAL</a></li>
                        <li><a class="dropdown-item" href="{{ route('morosos') }}">MOROSOS</a></li>
                        <li><a class="dropdown-item" href="{{ route('proximos.finalizar') }}">PRÓXIMOS A FINALIZAR</a></li>
                    </ul>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle {{ request()->routeIs('gestion.acreedores.*') ? 'active' : '' }}" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        ACREEDORES
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="{{ route('gestion.acreedores.index') }}">Listado</a></li>
                        <li><a class="dropdown-item" href="{{ route('gestion.acreedores.pagos') }}">Balance de Pagos</a></li>
                    </ul>
                </li>
                <li class="nav-item">
                    <a class="nav-link btn btn-danger text-white px-3 mx-2 {{ request()->routeIs('entries.create') ? 'active' : '' }}" href="{{ route('entries.create') }}">CREAR OPERACIÓN</a>
                </li>
            </ul>
            <div class="d-flex ms-auto">
                <span class="navbar-text text-light">
                    <i class="fas fa-calendar-alt me-1"></i> 
                    @php
                        // Configurar locale a español
                        \Carbon\Carbon::setLocale('es');
                        $fecha = \Carbon\Carbon::now();
                        // Formatear la fecha: "4 de Abril de 2025"
                        echo $fecha->format('j') . ' de ' . ucfirst($fecha->translatedFormat('F')) . ' de ' . $fecha->format('Y');
                    @endphp
                </span>
            </div>
        </div>
    </div>
</nav> 