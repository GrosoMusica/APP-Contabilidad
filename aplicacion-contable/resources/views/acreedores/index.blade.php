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
        .card-header {
            background-color: #ffc107 !important; /* Color amarillo */
            color: #212529;
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
                                            <div class="acreedor-fecha-creacion">
                                                <i class="fas fa-calendar-alt me-2"></i>
                                                <span class="fecha-label">Creado:</span>
                                                <span class="fecha-valor">{{ \Carbon\Carbon::parse($acreedor->created_at)->format('d M, Y') }}</span>
                                            </div>
                                        </div>
                                        <div class="col-md-6 text-end">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="text-muted mb-1">Saldo Total</div>
                                                    <h3 class="text-success">U$D {{ number_format($acreedor->saldo, 2) }}</h3>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="text-muted mb-1">Pagado hasta la fecha</div>
                                                    @php
                                                        // Calcular el total pagado para este acreedor
                                                        $totalPagadoAcreedor = 0;
                                                        foreach($acreedor->financiaciones as $financiacion) {
                                                            if($financiacion->estado != 'sin_cuota') {
                                                                $totalPagadoAcreedor += $financiacion->monto_pagado_acreedor ?? 0;
                                                            }
                                                        }
                                                    @endphp
                                                    <h4 class="text-primary">U$D {{ number_format($totalPagadoAcreedor, 2) }}</h4>
                                                    <div class="d-flex justify-content-end mt-2">
                                                        <button class="btn btn-sm btn-outline-success me-2" title="Registrar pago">
                                                            <i class="fas fa-dollar-sign"></i> Abonar
                                                        </button>
                                                        <a href="{{ route('acreedores.export-distribucion', ['acreedor' => $acreedor->id]) }}" 
                                                            class="btn btn-sm btn-outline-primary" 
                                                            title="Generar PDF"
                                                            target="_blank">
                                                            <i class="fas fa-file-pdf"></i> PDF
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Distribución de Ingresos con indicador de mes actual -->
                                    <div class="border-bottom pb-2 mb-3 d-flex justify-content-between align-items-center">
                                        <h5 class="mb-0 d-flex align-items-center">
                                            <i class="fas fa-chart-pie text-primary me-2"></i>
                                            Distribución de Ingresos
                                        </h5>
                                        <div class="text-muted">
                                            <i class="fas fa-calendar-alt me-1"></i> Mes Actual: {{ ucfirst(now()->locale('es')->isoFormat('MMMM YYYY')) }}
                                        </div>
                                    </div>
                                    
                                    <!-- Botones para mostrar/ocultar pendientes y totales -->
                                    <div class="mb-3 d-flex justify-content-end">
                                        <button id="togglePendientes-{{ $acreedor->id }}" class="btn btn-sm btn-outline-danger me-2">
                                            <i class="fas fa-eye me-1"></i> Mostrar Pendientes
                                        </button>
                                        <button id="toggleTotales-{{ $acreedor->id }}" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-list-alt me-1"></i> Ver Totales
                                        </button>
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
                                                        @if($item->estado != 'sin_cuota')  {{-- Solo mostrar financiaciones con cuotas --}}
                                                            @php
                                                                // Calcular el monto que le corresponde al acreedor según su porcentaje
                                                                $montoAcreedorCuota = $item->cuota->monto * ($item->porcentaje / 100);
                                                                
                                                                // Actualizar los totales según el estado
                                                                if ($item->estado == 'pagado' || $item->estado == 'pagada') {
                                                                    $montoPagadoTotal += $montoAcreedorCuota;
                                                                    $montoTotalMes += $item->cuota->monto; // Total general incluye el monto completo
                                                                } elseif ($item->estado == 'parcial') {
                                                                    $montoPagadoTotal += $item->monto_pagado_acreedor;
                                                                    $montoPendienteTotal += $item->monto_pendiente_acreedor;
                                                                    $montoTotalMes += $item->cuota->monto;
                                                                } else {
                                                                    // Pendiente
                                                                    $montoPendienteTotal += $montoAcreedorCuota;
                                                                    $montoTotalMes += $item->cuota->monto;
                                                                }
                                                                
                                                                // Verificar si hay cuotas pagadas
                                                                if ($item->estado == 'pagado' || $item->estado == 'pagada') {
                                                                    $estadoGeneral = 'pagado';
                                                                } elseif ($item->estado == 'parcial' && $estadoGeneral != 'pagado') {
                                                                    $estadoGeneral = 'parcial';
                                                                }
                                                            @endphp
                                                            
                                                            <tr class="table-row-fixed table-row-striped">
                                                                <td style="width: 40%" class="align-middle">
                                                                    <div>{{ $item->nombre_comprador }}</div>
                                                                </td>
                                                                <td style="width: 15%" class="text-center align-middle">
                                                                    <span class="porcentaje-badge">{{ $item->porcentaje }}%</span>
                                                                </td>
                                                                <td style="width: 20%" class="text-center align-middle">
                                                                    <span class="badge-estado-{{ $item->estado }}">
                                                                        {{ strtoupper($item->estado) }}
                                                                    </span>
                                                                </td>
                                                                <td style="width: 25%" class="monto-valor align-middle">
                                                                    <div class="monto-cell">
                                                                        @if($item->estado == 'pagada' || $item->estado == 'pagado')
                                                                            <span class="monto-pagado">U$D {{ number_format($item->cuota->monto * ($item->porcentaje / 100), 2) }}</span>
                                                                            <span class="monto-total totales-text" style="display: none;">Total: U$D {{ number_format($item->cuota->monto, 2) }}</span>
                                                                        @elseif($item->estado == 'parcial')
                                                                            <span class="monto-parcial">U$D {{ number_format($item->monto_pagado_acreedor, 2) }}</span>
                                                                            <span class="monto-pendiente pendientes-text" style="display: none;">Pend: U$D {{ number_format($item->monto_pendiente_acreedor, 2) }}</span>
                                                                        @else
                                                                            <span class="monto-pendiente">U$D 0.00</span>
                                                                            <span class="monto-total totales-text" style="display: none;">Total: U$D {{ number_format($item->cuota->monto * ($item->porcentaje / 100), 2) }}</span>
                                                                        @endif
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                        @endif
                                                    @endforeach
                                                </tbody>
                                                <tfoot class="table-light">
                                                    <tr>
                                                        <td colspan="2" class="align-middle"><strong class="fs-5">Total Mes:</strong></td>
                                                        <td class="text-center align-middle" id="estadoTotalMes-{{ $acreedor->id }}">
                                                            {{-- Etiqueta de estado eliminada --}}
                                                        </td>
                                                        <td class="text-end fs-5 fw-bold align-middle" id="montoTotalMes-{{ $acreedor->id }}">
                                                            @if($estadoGeneral == 'pagado')
                                                                <span class="monto-pagado">U$D {{ number_format($montoPagadoTotal, 2) }}</span>
                                                                <div class="monto-total totales-text" style="display: none;">Total: U$D {{ number_format($montoTotalMes, 2) }}</div>
                                                            @elseif($estadoGeneral == 'parcial')
                                                                <span class="monto-parcial">U$D {{ number_format($montoPagadoTotal, 2) }}</span>
                                                                <div class="monto-pendiente pendientes-text" style="display: none;">Pend: U$D {{ number_format($montoPendienteTotal, 2) }}</div>
                                                            @else
                                                                <span class="monto-pendiente">U$D 0.00</span>
                                                                <div class="monto-total totales-text" style="display: none;">Total: U$D {{ number_format($montoPendienteTotal, 2) }}</div>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
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
                        <!-- Selector de mes tipo navegación -->
                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center">
                                <label class="form-label mb-0">Seleccionar Mes</label>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mt-2 border p-2 rounded month-selector">
                                <a href="#" class="btn btn-sm btn-outline-secondary" id="prevMonth">
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                                <span class="fw-bold" id="currentMonth">{{ now()->locale('es')->isoFormat('MMMM YYYY') }}</span>
                                <a href="#" class="btn btn-sm btn-outline-secondary" id="nextMonth">
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            </div>
                        </div>
                        
                        <!-- Admin (id=1) separado - Asegurando que no desaparezca -->
                        @php
                            $adminAcreedor = $acreedores->where('id', 1)->first();
                            $otrosAcreedores = $acreedores->where('id', '!=', 1);
                        @endphp
                        
                        @if($adminAcreedor)
                        <div class="alert alert-info mb-4">
                            <h6 class="mb-2"><i class="fas fa-user-shield me-1"></i> Administración</h6>
                            <div class="d-flex justify-content-between align-items-center">
                                <span>{{ $adminAcreedor->nombre }}</span>
                                <span class="fw-bold">U$D {{ number_format($adminAcreedor->saldo, 2) }}</span>
                            </div>
                        </div>
                        @endif
                        
                        <h5 class="mb-3">Saldos por Cobrar</h5>
                        <ul class="list-group">
                            @php
                                $totalSaldos = 0;
                            @endphp
                            
                            @foreach($otrosAcreedores as $acreedor)
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
                                <strong>Total Liquidaciones:</strong>
                                <strong>U$D {{ number_format($totalSaldos, 2) }}</strong>
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <button type="button" class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#createAcreedorModal">
                                <i class="fas fa-plus-circle me-1"></i> Agregar Acreedor
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Después de las cajas de Gestión de Acreedores y Resumen -->
        <div class="row mt-4">
            <div class="col-12">
                <x-saldo-acreedores :acreedores="$acreedores" />
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
    
    <!-- Modal para crear nuevo acreedor -->
    <div class="modal fade" id="createAcreedorModal" tabindex="-1" aria-labelledby="createAcreedorModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('gestion.acreedores.store') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="createAcreedorModalLabel">Crear Nuevo Acreedor</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="nombre" class="form-label">Nombre del Acreedor</label>
                            <input type="text" class="form-control" id="nombre" name="nombre" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Guardar</button>
                    </div>
                </form>
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
            // Variables para el selector de mes
            let currentDate = moment();
            
            // Actualizar el texto del mes seleccionado
            function updateMonthDisplay() {
                $('#currentMonth').text(currentDate.locale('es').format('MMMM YYYY'));
            }
            
            // Navegación entre meses
            $('#prevMonth').click(function(e) {
                e.preventDefault();
                currentDate.subtract(1, 'month');
                updateMonthDisplay();
                // Aquí irá la lógica para cargar datos del mes seleccionado
            });
            
            $('#nextMonth').click(function(e) {
                e.preventDefault();
                currentDate.add(1, 'month');
                updateMonthDisplay();
                // Aquí irá la lógica para cargar datos del mes seleccionado
            });
        });

        document.addEventListener('DOMContentLoaded', function() {
            // Botón para mostrar/ocultar pendientes
            const togglePendientesBtn = document.getElementById('togglePendientes-{{ $acreedor->id }}');
            const pendientesText = document.querySelectorAll('.pendientes-text');
            
            togglePendientesBtn.addEventListener('click', function() {
                const isHidden = pendientesText[0]?.style.display === 'none';
                
                pendientesText.forEach(el => {
                    el.style.display = isHidden ? 'block' : 'none';
                });
                
                this.innerHTML = isHidden ? 
                    '<i class="fas fa-eye-slash me-1"></i> Ocultar Pendientes' : 
                    '<i class="fas fa-eye me-1"></i> Mostrar Pendientes';
                    
                this.classList.toggle('btn-outline-danger');
                this.classList.toggle('btn-danger');
            });
            
            // Botón para mostrar/ocultar totales
            const toggleTotalesBtn = document.getElementById('toggleTotales-{{ $acreedor->id }}');
            const totalesText = document.querySelectorAll('.totales-text');
            
            toggleTotalesBtn.addEventListener('click', function() {
                const isHidden = totalesText[0]?.style.display === 'none';
                
                totalesText.forEach(el => {
                    el.style.display = isHidden ? 'block' : 'none';
                });
                
                this.innerHTML = isHidden ? 
                    '<i class="fas fa-list-alt me-1"></i> Ocultar Totales' : 
                    '<i class="fas fa-list-alt me-1"></i> Ver Totales';
                    
                this.classList.toggle('btn-outline-primary');
                this.classList.toggle('btn-primary');
            });
        });

        // Recargar la página si viene de una redirección con mensaje de éxito
        if (document.querySelector('.alert-success')) {
            // Verificar si no ha recargado recientemente
            if (!localStorage.getItem('recentlyReloaded')) {
                // Marcar como recargado recientemente
                localStorage.setItem('recentlyReloaded', 'true');
                // Recargar la página después de un breve retraso
                setTimeout(function() {
                    window.location.reload(true); // true para forzar recarga desde el servidor
                }, 100);
            } else {
                // Limpiar el indicador después de unos segundos
                setTimeout(function() {
                    localStorage.removeItem('recentlyReloaded');
                }, 5000);
            }
        }
    </script>
</body>
</html> 