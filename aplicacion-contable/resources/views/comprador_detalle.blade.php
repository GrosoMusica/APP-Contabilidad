<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle del Comprador</title>
    <!-- Font Awesome - PRIMERO para evitar conflictos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Estilos personalizados para pagos -->
    <link href="{{ asset('css/pagos.css') }}" rel="stylesheet">
    <style>
        body {
            background-color: #f5f5f5; /* Fondo gris claro */
        }
        .card {
            margin-bottom: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .card-header {
            background-color: #007bff;
            color: white;
            font-weight: bold;
        }
        .card-body {
            background-color: #ffffff;
        }
        .accordion-button {
            color: #007bff;
            background-color: transparent;
            border: none;
            font-weight: bold;
            text-align: left;
            width: 100%;
            padding: 0;
        }
        .accordion-button:focus {
            box-shadow: none;
        }
        /* Estilos para la grilla de cuotas */
        #cuotasGrid {
            display: none; /* Inicialmente oculto */
        }
        .cuota-card {
            border-radius: 8px;
            overflow: hidden;
            transition: all 0.3s ease;
        }
        .cuota-header {
            padding: 8px 15px;
            font-weight: bold;
        }
        .highlight-cuota {
            animation: highlight 2s ease-in-out;
        }
        @keyframes highlight {
            0% { background-color: rgba(255, 255, 0, 0.5); }
            100% { background-color: transparent; }
        }
    </style>
</head>
<body>
    @include('partials.top_bar')
    <div class="container mt-5">
        <div class="row">
            <!-- Datos del Comprador -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        Datos del Comprador
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Nombre:</strong> {{ $comprador->nombre }}</p>
                                <p><strong>Dirección:</strong> {{ $comprador->direccion }}</p>
                                <p><strong>DNI:</strong> {{ $comprador->dni }}</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Teléfono:</strong> {{ $comprador->telefono }}</p>
                                <p><strong>Email:</strong> {{ $comprador->email }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Balance -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        Balance
                    </div>
                    <div class="card-body">
                        @php
                            // Calcular el monto real abonado sumando todos los pagos
                            $pagosRealizados = \App\Models\Pago::whereHas('cuota', function($query) use ($comprador) {
                                $query->whereHas('financiacion', function($q) use ($comprador) {
                                    $q->where('comprador_id', $comprador->id);
                                });
                            })->sum('monto_usd');
                            
                            // Calcular el saldo real pendiente
                            $saldoPendienteReal = $comprador->financiacion->monto_a_financiar - $pagosRealizados;
                        @endphp
                        
                        <p><strong>Monto Total:</strong> U$D {{ number_format($comprador->financiacion->monto_a_financiar, 2) }}</p>
                        <p><strong>Abonado Hasta la Fecha:</strong> U$D {{ number_format($pagosRealizados, 2) }}</p>
                        <p><strong>Saldo Pendiente:</strong> U$D {{ number_format($saldoPendienteReal, 2) }}</p>
                    </div>
                </div>
            </div>

            <!-- Información del Lote -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        Información del Lote
                    </div>
                    <div class="card-body">
                        <p><strong>Loteo:</strong> {{ $comprador->lote->loteo }}</p>
                        <p><strong>Manzana:</strong> {{ $comprador->lote->manzana }}</p>
                        <p><strong>Lote:</strong> {{ $comprador->lote->lote }}</p>
                    </div>
                </div>
            </div>

            <!-- Cuota Actual y Botón para mostrar todas las cuotas -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        @php
                            // Fechas básicas para comparación
                            $hoy = \Carbon\Carbon::now();
                            $inicioMes = \Carbon\Carbon::now()->startOfMonth();
                            $finMes = \Carbon\Carbon::now()->endOfMonth();
                            
                            // Encontrar la cuota actual pendiente más próxima
                            $cuotaActual = $cuotas->where('estado', '!=', 'pagada')
                                                ->where('fecha_de_vencimiento', '>=', $hoy)
                                                ->sortBy('fecha_de_vencimiento')
                                                ->first();
                            
                            // Si no hay cuotas pendientes futuras, tomar la última pendiente
                            if (!$cuotaActual) {
                                $cuotaActual = $cuotas->where('estado', '!=', 'pagada')
                                                    ->sortByDesc('fecha_de_vencimiento')
                                                    ->first();
                            }
                        @endphp

                        <div class="d-flex justify-content-between align-items-center">
                            <span>Cuota Actual</span>
                            <button id="mostrarCuotasBtn" class="btn btn-sm btn-primary">
                                <i class="fas fa-th me-1"></i> Mostrar vista de cuotas
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        @if($cuotaActual)
                            <div class="d-flex justify-content-between mb-3">
                                <div>
                                    <h5>Cuota #{{ $cuotaActual->numero_de_cuota }}</h5>
                                    <p class="mb-1">Monto: U$D {{ number_format($cuotaActual->monto, 2) }}</p>
                                    <p>Vencimiento: {{ $cuotaActual->fecha_de_vencimiento->format('d-m-Y') }}</p>
                                    
                                    @if($cuotaActual->estado === 'parcial')
                                        @php
                                            $totalPagado = $cuotaActual->pagos->sum('monto_usd');
                                            $saldoPendiente = $cuotaActual->monto - $totalPagado;
                                        @endphp
                                        <p class="text-warning">
                                            <i class="fas fa-exclamation-circle me-1"></i>
                                            Pagado parcialmente. Pendiente: U$D {{ number_format($saldoPendiente, 2) }}
                                        </p>
                                    @endif
                                </div>
                                <div>
                                    <a href="{{ route('pagos.index', ['comprador_id' => $comprador->id]) }}" class="btn btn-success">
                                        <i class="fas fa-money-bill-wave me-1"></i> Registrar Pago
                                    </a>
                                </div>
                            </div>
                        @else
                            <div class="text-center my-3">
                                <p class="text-success"><i class="fas fa-check-circle fa-2x mb-2"></i></p>
                                <p>¡Todas las cuotas han sido pagadas!</p>
                            </div>
                        @endif
                        
                        <hr class="my-3">
                        
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="mb-1"><strong>Resumen de Cuotas</strong></p>
                                <p class="small mb-0">Total Cuotas: {{ $cuotas->count() }}</p>
                                <p class="small mb-0">Pagadas: {{ $cuotas->where('estado', 'pagada')->count() }}</p>
                                <p class="small mb-0">Pendientes: {{ $cuotas->where('estado', '!=', 'pagada')->count() }}</p>
                            </div>
                            <div>
                                <a href="{{ route('pagos.index', ['comprador_id' => $comprador->id]) }}" class="btn btn-outline-primary">
                                    <i class="fas fa-list me-1"></i> Ver Detalle de Pagos
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Acreedores -->
            <div class="col-md-12 mt-4">
                <x-acreedores :acreedores="$acreedores" :comprador="$comprador" />
            </div>
            
            <!-- Grilla de Cuotas (inicialmente oculta) -->
            <div class="row mt-4" id="cuotasGrid" style="display: none;">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Vista Completa de Cuotas</h5>
                                <a href="{{ route('pagos.index', ['comprador_id' => $comprador->id]) }}" class="btn btn-sm btn-success">
                                    <i class="fas fa-money-bill-wave me-1"></i> Registrar Pagos
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            <x-cuotas-grid :cuotas="$cuotas" :showRegistrarPago="false" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Mostrar/ocultar la grilla de cuotas
            const mostrarCuotasBtn = document.getElementById('mostrarCuotasBtn');
            const cuotasGrid = document.getElementById('cuotasGrid');
            
            mostrarCuotasBtn.addEventListener('click', function() {
                if (cuotasGrid.style.display === 'none' || cuotasGrid.style.display === '') {
                    cuotasGrid.style.display = 'block';
                    this.innerHTML = '<i class="fas fa-times me-1"></i> Ocultar vista de cuotas';
                    // Scroll suave hasta la grilla
                    cuotasGrid.scrollIntoView({ behavior: 'smooth', block: 'start' });
                } else {
                    cuotasGrid.style.display = 'none';
                    this.innerHTML = '<i class="fas fa-th me-1"></i> Mostrar vista de cuotas';
                }
            });
            
            // Scroll hacia la cuota pagada (si existe en la sesión)
            @if(session('cuota_pagada_id'))
                setTimeout(function() {
                    // Mostrar el grid
                    cuotasGrid.style.display = 'block';
                    mostrarCuotasBtn.innerHTML = '<i class="fas fa-times me-1"></i> Ocultar vista de cuotas';
                    
                    // Buscar la cuota en la grilla
                    const cuotaCards = document.querySelectorAll('.cuota-card');
                    for (let i = 0; i < cuotaCards.length; i++) {
                        if (cuotaCards[i].querySelector('[data-cuota-id="{{ session("cuota_pagada_id") }}"]')) {
                            cuotaCards[i].scrollIntoView({ behavior: 'smooth', block: 'center' });
                            cuotaCards[i].classList.add('highlight-cuota');
                            break;
                        }
                    }
                }, 500);
            @endif
        });
    </script>
</body>
</html> 