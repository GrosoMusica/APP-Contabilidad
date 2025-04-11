<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Gestión de Acreedores - Sistema Contable</title>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Estilos globales de la aplicación -->
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <!-- Estilos específicos para acreedores -->
    <link rel="stylesheet" href="{{ asset('css/acreedores.css') }}">
    <style>
        .card {
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }
        .card-header {
            background-color: #9acd32; /* Amarillo-verde claro */
            color: white;
            font-weight: bold;
            border-top-left-radius: 8px;
            border-top-right-radius: 8px;
        }
        .balance-card {
            height: 100%;
            display: flex;
            flex-direction: column;
        }
        .balance-card .card-body {
            flex: 1;
        }
        .selected-row {
            background-color: rgba(154, 205, 50, 0.15) !important; /* Amarillo-verde claro suave */
        }
        .acreedor-header {
            font-size: 1.5rem;
            font-weight: 600;
            color: #343a40;
        }
        .acreedor-saldo {
            font-size: 2rem;
            font-weight: bold;
            color: #28a745;
            text-align: center;
            margin: 15px 0;
        }
        .acreedor-mini-datos {
            font-size: 0.9rem;
            color: #6c757d;
        }
        .financiacion-item {
            padding: 10px;
            border-bottom: 1px solid #e9ecef;
        }
        .financiacion-item:last-child {
            border-bottom: none;
        }
        .porcentaje-badge {
            background-color: #9acd32;
            color: white;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 0.85rem;
        }
        .badge-estado-pagado {
            background-color: #28a745;
            color: white;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 0.8rem;
        }
        .badge-estado-parcial {
            background-color: #ffc107;
            color: #212529;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 0.8rem;
        }
        .badge-estado-pendiente {
            background-color: #dc3545;
            color: white;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 0.8rem;
        }
        .badge-estado-na {
            background-color: #6c757d;
            color: white;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 0.8rem;
        }
        .monto-pagado {
            color: #28a745;
            font-weight: 500;
        }
        .monto-parcial {
            color: #fd7e14;
            font-weight: 500;
        }
        .monto-pendiente {
            color: #dc3545;
            font-size: 0.85rem;
        }
        .monto-valor {
            font-size: 0.9rem;
            text-align: right;
        }
        .btn-top-right {
            position: absolute;
            top: 15px;
            right: 15px;
        }
    </style>
</head>
<body>
    <!-- Barra de navegación superior -->
    @include('partials.top_bar')
    
    <div class="container-fluid py-4">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        
        <div class="row">
            <!-- Panel principal (75%) -->
            <div class="col-lg-9">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span>Gestión de Acreedores</span>
                    </div>
                    <div class="card-body">
                        <!-- Pestañas de navegación para acreedores -->
                        <ul class="nav nav-tabs acreedor-tabs mb-3" id="acreedoresTabs" role="tablist">
                            @foreach($acreedores as $index => $acreedor)
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link {{ $index == 0 ? 'active' : '' }}" 
                                            id="tab-{{ $acreedor->id }}" 
                                            data-bs-toggle="tab" 
                                            data-bs-target="#content-{{ $acreedor->id }}" 
                                            type="button" 
                                            role="tab" 
                                            aria-controls="content-{{ $acreedor->id }}" 
                                            aria-selected="{{ $index == 0 ? 'true' : 'false' }}"
                                            data-id="{{ $acreedor->id }}">
                                        {{ $acreedor->nombre }}
                                        <span class="badge rounded-pill bg-success ms-1">
                                            @php
                                                $totalPagado = 0;
                                                foreach($acreedor->financiaciones as $financiacion) {
                                                    $totalPagado += $financiacion->monto_pagado_acreedor ?? 0;
                                                }
                                            @endphp
                                            U$D {{ number_format($totalPagado, 2) }}
                                        </span>
                                    </button>
                                </li>
                            @endforeach
                        </ul>
                        
                        <!-- Contenido de las pestañas -->
                        <div class="tab-content" id="acreedoresTabContent">
                            @foreach($acreedores as $index => $acreedor)
                                <div class="tab-pane fade {{ $index == 0 ? 'show active' : '' }}" 
                                     id="content-{{ $acreedor->id }}" 
                                     role="tabpanel" 
                                     aria-labelledby="tab-{{ $acreedor->id }}">
                                    
                                    <!-- Información del acreedor -->
                                    <div class="row mb-4">
                                        <div class="col-md-6">
                                            <h4>{{ $acreedor->nombre }}</h4>
                                            <div class="text-muted">Creado: {{ \Carbon\Carbon::parse($acreedor->created_at)->format('d M, Y') }}</div>
                                        </div>
                                        <div class="col-md-6 text-end">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="text-muted mb-1">Saldo Total</div>
                                                    <h3 class="text-success">U$D {{ number_format($acreedor->saldo, 2) }}</h3>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="text-muted mb-1">Pagado hasta la fecha</div>
                                                    <h4 class="text-primary">U$D {{ number_format($totalPagado, 2) }}</h4>
                                                    <div class="d-flex justify-content-end mt-2">
                                                        <button class="btn btn-sm btn-outline-success me-2" title="Registrar pago">
                                                            <i class="fas fa-dollar-sign"></i> Abonar
                                                        </button>
                                                        <button class="btn btn-sm btn-outline-primary" title="Generar PDF">
                                                            <i class="fas fa-file-pdf"></i> PDF
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Distribución de Ingresos y Selector de Mes en la misma línea -->
                                    <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3">
                                        <h5 class="mb-0 d-flex align-items-center">
                                            <i class="fas fa-chart-pie text-primary me-2"></i>
                                            Distribución de Ingresos
                                        </h5>
                                        <div class="d-flex align-items-center">
                                            <button class="btn btn-sm btn-outline-secondary me-2 btn-mes-anterior" data-acreedor="{{ $acreedor->id }}">
                                                <i class="fas fa-chevron-left"></i>
                                            </button>
                                            
                                            <h5 class="mb-0 d-flex align-items-center mx-2">
                                                <i class="fas fa-calendar-alt text-primary me-2"></i>
                                                <span id="mesActual-{{ $acreedor->id }}">{{ \Carbon\Carbon::now()->locale('es')->isoFormat('MMMM [de] YYYY') }}</span>
                                            </h5>
                                            
                                            <button class="btn btn-sm btn-outline-secondary ms-2 btn-mes-siguiente" data-acreedor="{{ $acreedor->id }}">
                                                <i class="fas fa-chevron-right"></i>
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <!-- Tabla de distribución de ingresos para este acreedor -->
                                    <div class="mb-4">
                                        <div class="table-responsive">
                                            <table class="table table-sm">
                                                <tbody>
                                                    @php
                                                        $montoTotalMes = 0;
                                                        $montoPagadoTotal = 0;
                                                        $montoPendienteTotal = 0;
                                                        $estadoGeneral = 'pendiente';
                                                    @endphp
                                                    
                                                    @foreach($acreedor->financiaciones as $item)
                                                        @php
                                                            $montoTotalMes += $item->monto_porcentaje ?? 0;
                                                            $montoPagadoTotal += $item->monto_pagado_acreedor ?? 0;
                                                            $montoPendienteTotal += $item->monto_pendiente_acreedor ?? 0;
                                                            
                                                            // Verificar si hay cuotas pagadas
                                                            if (isset($item->estado) && $item->estado == 'pagado') {
                                                                $estadoGeneral = 'pagado';
                                                            } elseif (isset($item->estado) && $item->estado == 'parcial' && $estadoGeneral != 'pagado') {
                                                                $estadoGeneral = 'parcial';
                                                            }
                                                        @endphp
                                                        
                                                        <tr>
                                                            <td style="width: 40%">
                                                                <div>{{ $item->nombre_comprador }}</div>
                                                                @if(isset($item->cuota))
                                                                    <small class="text-muted">Cuota #{{ $item->cuota->numero ?? '-' }}</small>
                                                                @endif
                                                            </td>
                                                            <td style="width: 15%" class="text-center">
                                                                <span class="porcentaje-badge">{{ $item->porcentaje }}%</span>
                                                            </td>
                                                            <td style="width: 20%" class="text-center">
                                                                @if(isset($item->estado))
                                                                    <span class="badge-estado-{{ $item->estado == 'sin_cuota' ? 'pendiente' : $item->estado }}">
                                                                        {{ $item->estado == 'sin_cuota' ? 'PENDIENTE' : strtoupper($item->estado) }}
                                                                    </span>
                                                                @else
                                                                    <span class="badge-estado-pendiente">PENDIENTE</span>
                                                                @endif
                                                            </td>
                                                            <td style="width: 25%" class="monto-valor">
                                                                @if(isset($item->estado))
                                                                    @if($item->estado == 'pagado')
                                                                        <span class="monto-pagado">U$D {{ number_format($item->monto_pagado_acreedor, 2) }}</span>
                                                                    @elseif($item->estado == 'parcial')
                                                                        <span class="monto-parcial">U$D {{ number_format($item->monto_pagado_acreedor, 2) }}</span>
                                                                        <div class="monto-pendiente">Pend: U$D {{ number_format($item->monto_pendiente_acreedor, 2) }}</div>
                                                                    @else
                                                                        <span class="monto-pendiente">U$D 0.00</span>
                                                                        <div class="text-muted small">Total: U$D {{ number_format($item->monto_porcentaje, 2) }}</div>
                                                                    @endif
                                                                @else
                                                                    <span class="text-muted">-</span>
                                                                @endif
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                                <tfoot class="table-light">
                                                    <tr>
                                                        <td colspan="2"><strong>Total Mes:</strong></td>
                                                        <td class="text-center" id="estadoTotalMes-{{ $acreedor->id }}">
                                                            <span class="badge-estado-{{ $estadoGeneral }}">
                                                                {{ strtoupper($estadoGeneral) }}
                                                            </span>
                                                        </td>
                                                        <td class="text-end" id="montoTotalMes-{{ $acreedor->id }}">
                                                            @if($estadoGeneral == 'pagado')
                                                                <span class="monto-pagado">U$D {{ number_format($montoTotalMes, 2) }}</span>
                                                            @elseif($estadoGeneral == 'parcial')
                                                                <span class="monto-parcial">U$D {{ number_format($montoPagadoTotal, 2) }}</span>
                                                                <div class="monto-pendiente">Pend: U$D {{ number_format($montoPendienteTotal, 2) }}</div>
                                                            @else
                                                                <span class="monto-pendiente">U$D 0.00</span>
                                                                <div class="text-muted small">Total: U$D {{ number_format($montoTotalMes, 2) }}</div>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                    </div>
                                    
                                    <!-- Tarjetas de financiaciones -->
                                    <div class="row">
                                        @if(count($acreedor->financiaciones) > 0)
                                            @foreach($acreedor->financiaciones as $item)
                                                <div class="col-md-6 col-lg-4 mb-3">
                                                    <div class="card h-100">
                                                        <div class="card-body">
                                                            <h6 class="d-flex justify-content-between">
                                                                {{ $item->nombre_comprador }}
                                                                <span class="porcentaje-badge">{{ $item->porcentaje }}%</span>
                                                            </h6>
                                                            <div class="mt-2">
                                                                @if(isset($item->estado))
                                                                    <div class="d-flex justify-content-between align-items-center">
                                                                        <span>Estado:</span>
                                                                        <span class="badge-estado-{{ isset($item->estado) ? ($item->estado == 'sin_cuota' ? 'pendiente' : $item->estado) : 'pendiente' }}">
                                                                            {{ isset($item->estado) ? ($item->estado == 'sin_cuota' ? 'PENDIENTE' : strtoupper($item->estado)) : 'PENDIENTE' }}
                                                                        </span>
                                                                    </div>
                                                                    <div class="d-flex justify-content-between align-items-center mt-1">
                                                                        <span>Saldo:</span>
                                                                        <span class="monto-{{ $item->estado }}">
                                                                            U$D {{ number_format($item->monto_pagado_acreedor ?? 0, 2) }}
                                                                        </span>
                                                                    </div>
                                                                @else
                                                                    <div class="text-muted text-center mt-2">Sin datos para el mes actual</div>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        @else
                                            <div class="col-12">
                                                <p class="text-muted">No hay financiaciones asignadas.</p>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                            
                            <!-- Contenido de la pestaña para nuevo acreedor -->
                            <div class="tab-pane fade" id="content-new" role="tabpanel" aria-labelledby="tab-new">
                                <!-- Formulario para agregar nuevo acreedor -->
                                <div class="p-4">
                                    <h4 class="mb-3">Agregar Nuevo Acreedor</h4>
                                    <form action="{{ url('/acreedores') }}" method="POST">
                                        @csrf
                                        <div class="mb-3">
                                            <label for="nombre" class="form-label">Nombre del Acreedor</label>
                                            <input type="text" class="form-control" id="nombre" name="nombre" required>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="descripcion" class="form-label">Descripción (opcional)</label>
                                            <textarea class="form-control" id="descripcion" name="descripcion" rows="3"></textarea>
                                        </div>
                                        
                                        <button type="submit" class="btn btn-primary">Guardar Acreedor</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Panel lateral (25%) -->
            <div class="col-lg-3">
                <div class="card">
                    <div class="card-header">
                        Resumen
                    </div>
                    <div class="card-body">
                        <h5 class="mb-3">Acreedores Activos</h5>
                        <div class="d-flex justify-content-between align-items-center">
                            <span>Total:</span>
                            <span class="fw-bold">{{ count($acreedores) }}</span>
                        </div>
                        
                        <hr>
                        
                        <h5 class="mb-3">Saldos por Cobrar</h5>
                        <ul class="list-group">
                            @php
                                $totalSaldos = 0;
                            @endphp
                            
                            @foreach($acreedores as $acreedor)
                                @php
                                    $totalSaldos += $acreedor->saldo;
                                @endphp
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    {{ $acreedor->nombre }}
                                    <span>U$D {{ number_format($acreedor->saldo, 2) }}</span>
                                </li>
                            @endforeach
                        </ul>
                        
                        <div class="alert alert-success mt-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <strong>Total:</strong>
                                <strong>U$D {{ number_format($totalSaldos, 2) }}</strong>
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <a href="{{ url('/acreedores/create') }}" class="btn btn-primary w-100">
                                <i class="fas fa-plus-circle me-1"></i> Agregar Acreedor
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal de confirmación para eliminar -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirmar Eliminación</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    ¿Está seguro que desea eliminar este acreedor? Esta acción no se puede deshacer.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-danger" id="confirmDelete">Eliminar</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/locale/es.js"></script>
    
    <script>
        $(document).ready(function() {
            // Almacenar el mes actual para cada acreedor
            const mesesActuales = {};
            
            @foreach($acreedores as $acreedor)
                mesesActuales[{{ $acreedor->id }}] = moment();
            @endforeach
            
            // Función para actualizar el mes mostrado
            function actualizarMesSeleccionado(acreedorId) {
                const mesFormateado = mesesActuales[acreedorId].locale('es').format('MMMM [de] YYYY');
                $(`#mesActual-${acreedorId}`).text(mesFormateado.charAt(0).toUpperCase() + mesFormateado.slice(1));
                
                // Aquí se cargarían los datos del mes seleccionado mediante AJAX
                // Por ahora, solo mostraremos un mensaje indicando el cambio
                console.log(`Cargando datos para el acreedor ${acreedorId} del mes: ${mesesActuales[acreedorId].format('YYYY-MM')}`);
            }
            
            // Botón mes anterior
            $('.btn-mes-anterior').on('click', function() {
                const acreedorId = $(this).data('acreedor');
                mesesActuales[acreedorId] = mesesActuales[acreedorId].subtract(1, 'month');
                actualizarMesSeleccionado(acreedorId);
            });
            
            // Botón mes siguiente
            $('.btn-mes-siguiente').on('click', function() {
                const acreedorId = $(this).data('acreedor');
                mesesActuales[acreedorId] = mesesActuales[acreedorId].add(1, 'month');
                actualizarMesSeleccionado(acreedorId);
            });
            
            // Auto-cerrar las alertas después de 5 segundos
            setTimeout(function() {
                $('.alert').alert('close');
            }, 5000);
        });
    </script>
</body>
</html> 