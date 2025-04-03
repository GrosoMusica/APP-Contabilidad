<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">Aplicación Contable</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('compradores.index') }}">Compradores</a>
                </li>
                <!-- Agrega más enlaces según sea necesario -->
            </ul>
            <span class="navbar-text">
                Fecha Actual: {{ now()->format('d-m-Y') }}
            </span>
        </div>
    </div>
</nav> 