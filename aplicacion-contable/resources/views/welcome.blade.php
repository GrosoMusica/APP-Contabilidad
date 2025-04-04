<!-- resources/views/welcome.blade.php -->
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Aplicación Contable</title>
    <!-- Fonts -->
    <link href="https://fonts.bunny.net/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Nunito', sans-serif;
            background-color: #f8f9fa;
        }
        .menu-button {
            height: 180px;
            border-radius: 10px;
            transition: transform 0.3s ease;
            margin-bottom: 20px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .menu-button:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0,0,0,0.15);
        }
        .menu-icon {
            font-size: 3rem;
            margin-bottom: 10px;
        }
        .menu-title {
            font-weight: bold;
            font-size: 1.2rem;
        }
    </style>
</head>
<body class="antialiased">
    <!-- Navegación -->
    @include('partials.top_bar')

    <!-- Contenido de la página -->
    <div class="container mt-5">
        <div class="row mb-4">
            <div class="col-12 text-center">
                <h1>Bienvenido a la Aplicación Contable</h1>
                <p class="lead">Seleccione una opción para comenzar</p>
            </div>
        </div>
        
        <div class="row">
            <!-- Botón 1: Compradores -->
            <div class="col-md-3">
                <a href="{{ route('compradores.index') }}" class="text-decoration-none">
                    <div class="menu-button bg-primary text-white">
                        <i class="fas fa-users menu-icon"></i>
                        <span class="menu-title">COMPRADORES</span>
                    </div>
                </a>
            </div>
            
            <!-- Botón 2: Lotes -->
            <div class="col-md-3">
                <a href="{{ route('lotes.index') }}" class="text-decoration-none">
                    <div class="menu-button bg-success text-white">
                        <i class="fas fa-map-marker-alt menu-icon"></i>
                        <span class="menu-title">LOTES</span>
                    </div>
                </a>
            </div>
            
            <!-- Botón 3: Financiaciones -->
            <div class="col-md-3">
                <a href="#" class="text-decoration-none">
                    <div class="menu-button bg-info text-white">
                        <i class="fas fa-money-bill-wave menu-icon"></i>
                        <span class="menu-title">FINANCIACIONES</span>
                    </div>
                </a>
            </div>
            
            <!-- Botón 4: Acreedores -->
            <div class="col-md-3">
                <a href="#" class="text-decoration-none">
                    <div class="menu-button bg-warning text-dark">
                        <i class="fas fa-handshake menu-icon"></i>
                        <span class="menu-title">ACREEDORES</span>
                    </div>
                </a>
            </div>
        </div>
        
        <div class="row mt-3">
            <!-- Botón 5: Crear Operación -->
            <div class="col-md-3">
                <a href="{{ route('entries.create') }}" class="text-decoration-none">
                    <div class="menu-button bg-danger text-white">
                        <i class="fas fa-plus-circle menu-icon"></i>
                        <span class="menu-title">CREAR OPERACIÓN</span>
                    </div>
                </a>
            </div>
            
            <!-- Botón 6: Informes -->
            <div class="col-md-3">
                <a href="#" class="text-decoration-none">
                    <div class="menu-button bg-secondary text-white">
                        <i class="fas fa-chart-bar menu-icon"></i>
                        <span class="menu-title">INFORMES</span>
                    </div>
                </a>
            </div>
            
            <!-- Botón 7: Pagos -->
            <div class="col-md-3">
                <a href="#" class="text-decoration-none">
                    <div class="menu-button bg-dark text-white">
                        <i class="fas fa-credit-card menu-icon"></i>
                        <span class="menu-title">PAGOS</span>
                    </div>
                </a>
            </div>
            
            <!-- Botón 8: Configuración -->
            <div class="col-md-3">
                <a href="#" class="text-decoration-none">
                    <div class="menu-button" style="background-color: #6f42c1; color: white;">
                        <i class="fas fa-cog menu-icon"></i>
                        <span class="menu-title">CONFIGURACIÓN</span>
                    </div>
                </a>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>