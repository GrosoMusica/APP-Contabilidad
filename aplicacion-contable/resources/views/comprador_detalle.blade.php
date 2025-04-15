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
        /* Estilos adicionales para indicadores de pago */
        .payment-status {
            padding: 5px 10px;
            border-radius: 4px;
            font-weight: bold;
            display: inline-block;
            margin-top: 10px;
        }
        .status-paid {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .status-partial {
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffeeba;
        }
        .status-pending {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .field-label {
            color: #6c757d;
            font-size: 0.9rem;
            margin-bottom: 0.2rem;
        }
        .field-value {
            font-size: 1.1rem;
            font-weight: 500;
            margin-bottom: 1rem;
        }
        .icon-field {
            color: #007bff;
            width: 25px;
            text-align: center;
            margin-right: 5px;
        }
    </style>
</head>
<body>
    @include('partials.top_bar')
    <div class="container mt-5">
        <div class="row">
            <!-- Datos del Comprador con solo iconos -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        Datos del Comprador
                    </div>
                    <div class="card-body">
                        <style>
                            .field-value {
                                font-size: 1.1rem;
                                font-weight: 500;
                                margin-bottom: 1rem;
                            }
                            .icon-field {
                                color: #007bff;
                                width: 25px;
                                text-align: center;
                                margin-right: 5px;
                            }
                        </style>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <div class="field-value">
                                        <i class="fas fa-user icon-field"></i>
                                        {{ $comprador->nombre }}
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <div class="field-value">
                                        <i class="fas fa-map-marker-alt icon-field"></i>
                                        {{ $comprador->direccion }}
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <div class="field-value">
                                        <i class="fas fa-id-card icon-field"></i>
                                        {{ $comprador->dni }}
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <div class="field-value">
                                        <i class="fas fa-phone icon-field"></i>
                                        {{ $comprador->telefono }}
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <div class="field-value">
                                        <i class="fas fa-envelope icon-field"></i>
                                        {{ $comprador->email }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Balance con lógica corregida para excluir pagos excedentes -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        Balance
                    </div>
                    <div class="card-body">
                        @php
                            // 1. Buscar la financiación por comprador_id
                            $financiacion = \App\Models\Financiacion::where('comprador_id', $comprador->id)->first();
                            $montoTotalFinanciacion = $financiacion ? $financiacion->monto_a_financiar : 0;
                            
                            // 2. Calcular el monto abonado siguiendo el flujo de datos exacto
                            $pagosRealizados = 0;
                            
                            if ($financiacion) {
                                // a. Obtener los IDs de todas las cuotas que pertenecen a esta financiación
                                $cuotasIds = \App\Models\Cuota::where('financiacion_id', $financiacion->id)
                                                             ->pluck('id')
                                                             ->toArray();
                                
                                // b. Sumar todos los montos de pagos asociados a esas cuotas
                                //    EXCLUYENDO aquellos marcados como pagos excedentes
                                if (!empty($cuotasIds)) {
                                    $pagosRealizados = \App\Models\Pago::whereIn('cuota_id', $cuotasIds)
                                                                      ->where(function($query) {
                                                                          $query->where('es_pago_excedente', '!=', 1)
                                                                                ->orWhereNull('es_pago_excedente');
                                                                      })
                                                                      ->sum('monto_usd');
                                }
                            }
                            
                            // 3. Cálculo de información de cuotas para la barra de progreso
                            $totalCuotas = $cuotas->count();
                            $cuotasPagadas = $cuotas->where('estado', 'pagada')->count();
                            $cuotasPendientes = $totalCuotas - $cuotasPagadas;
                            $porcentajePagado = ($totalCuotas > 0) ? ($cuotasPagadas / $totalCuotas) * 100 : 0;
                        @endphp
                        
                        <p class="mb-3"><strong>Abonado Hasta la Fecha:</strong> U$D {{ number_format($pagosRealizados, 2) }} / U$D {{ number_format($montoTotalFinanciacion, 2) }}</p>
                        
                        <hr>
                        
                        <div class="mt-3">
                            <p class="mb-1"><strong>Resumen de Cuotas</strong></p>
                            <div class="d-flex justify-content-between small mb-2">
                                <span>Total: {{ $totalCuotas }}</span>
                                <span>Pagadas: {{ $cuotasPagadas }}</span>
                                <span>Pendientes: {{ $cuotasPendientes }}</span>
                            </div>
                            
                            <!-- Barra de progreso -->
                            <div class="progress" style="height: 20px;">
                                <div class="progress-bar bg-success" role="progressbar" 
                                     style="width: {{ $porcentajePagado }}%;" 
                                     aria-valuenow="{{ $porcentajePagado }}" 
                                     aria-valuemin="0" 
                                     aria-valuemax="100">
                                    {{ round($porcentajePagado) }}%
                                </div>
                            </div>
                        </div>
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
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Manzana:</strong> {{ $comprador->lote->manzana }}</p>
                                <p><strong>Lote:</strong> {{ $comprador->lote->lote }}</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Loteo:</strong> {{ $comprador->lote->loteo }}</p>
                                <p><span style="font-size: 1.2rem; font-style: italic;">{{ $comprador->lote->mts_cuadrados }} mt<sup>2</sup></span></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Cuota Actual con modificaciones -->
            <div class="col-md-6">
                <div class="card">
                    @php
                        // Fechas básicas para comparación
                        $hoy = \Carbon\Carbon::now();
                        $inicioMes = \Carbon\Carbon::now()->startOfMonth();
                        $finMes = \Carbon\Carbon::now()->endOfMonth();
                        
                        // Encontrar ÚNICAMENTE la cuota correspondiente al mes actual
                        $cuotaActual = $cuotas->filter(function($cuota) use ($inicioMes, $finMes) {
                            return $cuota->fecha_de_vencimiento >= $inicioMes && 
                                   $cuota->fecha_de_vencimiento <= $finMes;
                        })->first();
                        
                        // Definir la clase de color según el estado
                        $headerColorClass = 'bg-secondary'; // Color predeterminado para "fuera de tiempo"
                        
                        if ($cuotaActual) {
                            if ($cuotaActual->estado === 'pendiente') {
                                $headerColorClass = 'bg-danger';
                            } elseif ($cuotaActual->estado === 'parcial') {
                                $headerColorClass = 'bg-warning text-dark';
                            } elseif ($cuotaActual->estado === 'pagada') {
                                $headerColorClass = 'bg-success';
                            }
                        }
                    @endphp

                    <div class="card-header {{ $headerColorClass }}" id="cuotaActualHeader">
                        <div class="d-flex justify-content-between align-items-center">
                            @if($cuotaActual)
                                <span>Cuota del Mes #{{ $cuotaActual->numero_de_cuota }} ({{ $hoy->format('F Y') }})</span>
                            @else
                                <span>Sin cuotas este mes</span>
                            @endif
                            <button id="mostrarCuotasBtn" class="btn btn-sm btn-light">
                                <i class="fas fa-th me-1"></i> Mostrar vista de cuotas
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        @if($cuotaActual)
                            <div class="d-flex justify-content-between mb-3">
                                <div>
                                    <p class="mb-1">Monto: U$D {{ number_format($cuotaActual->monto, 2) }}</p>
                                    <p>Vencimiento: {{ $cuotaActual->fecha_de_vencimiento->format('d-m-Y') }}</p>
                                    
                                    @if($cuotaActual->estado === 'pagada')
                                        <div class="payment-status status-paid">
                                            <i class="fas fa-check-circle me-1"></i> PAGADA
                                        </div>
                                    @elseif($cuotaActual->estado === 'parcial')
                                        @php
                                            $totalPagado = $cuotaActual->pagos->sum('monto_usd');
                                            $saldoPendiente = $cuotaActual->monto - $totalPagado;
                                            $porcentajePagado = ($totalPagado / $cuotaActual->monto) * 100;
                                        @endphp
                                        <div class="payment-status status-partial">
                                            <i class="fas fa-exclamation-circle me-1"></i>
                                            PAGO PARCIAL ({{ number_format($porcentajePagado, 0) }}%)
                                        </div>
                                        <p class="text-warning mt-2">
                                            Pendiente: U$D {{ number_format($saldoPendiente, 2) }}
                                        </p>
                                    @else
                                        <div class="payment-status status-pending">
                                            <i class="fas fa-times-circle me-1"></i> PENDIENTE
                                        </div>
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
                                <p class="text-secondary"><i class="fas fa-calendar-times fa-2x mb-2"></i></p>
                                <p>No hay cuotas programadas para este mes.</p>
                                <p class="small text-muted">Verifique el cronograma de pagos para más detalles.</p>
                            </div>
                        @endif
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