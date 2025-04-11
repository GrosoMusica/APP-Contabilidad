<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Informes - Sistema Contable</title>
    <!-- Fonts -->
    <link href="https://fonts.bunny.net/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
    <!-- Estilos globales de la aplicación -->
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <style>
        body {
            font-family: 'Nunito', sans-serif;
        }
        .equal-height-card {
            height: 100%;
            display: flex;
            flex-direction: column;
        }
        .equal-height-card .card-body {
            flex: 1;
        }
    </style>
</head>
<body class="antialiased">
    <!-- Navegación -->
    @include('partials.top_bar')

    <div class="container mt-4">
        <!-- <div class="row mb-4">
            <div class="col-md-12">
                <h2 class="text-secondary">Panel de Informes</h2>
            </div>
        </div> -->

        <div class="card informe-section">
                      <div class="card-body">
                @if(isset($error))
                    <div class="alert alert-danger">
                        {{ $error }}
                    </div>
                    <div class="small text-muted bg-light p-3">
                        <pre>{{ $trace ?? '' }}</pre>
                    </div>
                @else
                    <!-- Selector de mes (siempre visible) -->
                    @if(isset($diagnostico))
                        @php
                            $mesActual = now()->month;
                            $anoActual = now()->year;
                            $esElMesActual = ($diagnostico['mes_consultado'] == $mesActual && $diagnostico['ano_consultado'] == $anoActual);
                        @endphp
                        
                        <!-- Fila con las 5 cajas de información -->
                        <div class="row mb-3">
                            <!-- Caja 1: Balance -->
                            <div class="col">
                                <div class="card bg-info text-white h-100">
                                    <div class="card-body text-center">
                                        <i class="fas fa-chart-line fa-2x mb-2"></i>
                                        <h6 class="card-title">Balance</h6>
                                        @php
                                            // Calcular número de cuotas pagadas y total de cuotas del mes
                                            $cuotasPagadas = 0;
                                            $totalCuotasMes = 0;
                                            
                                            if(isset($diagnostico['pasos'][1]['resultado'])) {
                                                foreach($diagnostico['pasos'][1]['resultado'] as $cuota) {
                                                    $fechaVencimiento = new DateTime($cuota->fecha_de_vencimiento);
                                                    if($fechaVencimiento->format('m') == $diagnostico['mes_consultado'] && 
                                                       $fechaVencimiento->format('Y') == $diagnostico['ano_consultado']) {
                                                        $totalCuotasMes++;
                                                        if($cuota->estado == 'pagada') {
                                                            $cuotasPagadas++;
                                                        }
                                                    }
                                                }
                                            }
                                        @endphp
                                        <h4>{{ $cuotasPagadas }} / {{ $totalCuotasMes }}</h4>
                                        <small>{{ $cuotasPagadas == $totalCuotasMes ? 'Todas al día' : 'Cuotas pagadas' }}</small>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Caja 2: Pagos Recibidos (verde) -->
                            <div class="col">
                                <div class="card bg-success text-white h-100">
                                    <div class="card-body text-center">
                                        <i class="fas fa-dollar-sign fa-2x mb-2"></i>
                                        <h6 class="card-title">Recibido</h6>
                                        @php
                                            $montoRecibido = 0;
                                            $saldoExcedente = 0;
                                            $pagosContados = 0;
                                            
                                            if(isset($diagnostico['pasos'][1]['resultado']) && isset($diagnostico['pasos'][2]['resultado'])) {
                                                // Crear un mapa de cuotas por ID para búsqueda rápida
                                                $cuotasPorId = [];
                                                foreach($diagnostico['pasos'][1]['resultado'] as $cuota) {
                                                    $cuotasPorId[$cuota->cuota_id] = $cuota;
                                                }
                                                
                                                // Procesar pagos
                                                foreach($diagnostico['pasos'][2]['resultado'] as $pago) {
                                                    // Verificar si es un pago que debe ignorarse
                                                    if(property_exists($pago, 'es_pago_excedente') && $pago->es_pago_excedente == 1) {
                                                        continue; // No considerar estos pagos
                                                    }
                                                    
                                                    $pagosContados++;
                                                    
                                                    // Verificar si hay excedente
                                                    if(isset($cuotasPorId[$pago->cuota_id])) {
                                                        $cuota = $cuotasPorId[$pago->cuota_id];
                                                        
                                                        if($pago->monto_usd > $cuota->monto) {
                                                            // Hay excedente
                                                            $montoRecibido += $cuota->monto;
                                                            $saldoExcedente += ($pago->monto_usd - $cuota->monto);
                                                        } else {
                                                            // No hay excedente
                                                            $montoRecibido += $pago->monto_usd;
                                                        }
                                                    } else {
                                                        // Si no encontramos la cuota, sumamos el monto completo
                                                        $montoRecibido += $pago->monto_usd;
                                                    }
                                                }
                                            }
                                        @endphp
                                        <h4>U$D {{ number_format($montoRecibido, 2) }}</h4>
                                        @if($saldoExcedente > 0)
                                            <div class="text-info fw-bold">* U$D {{ number_format($saldoExcedente, 2) }} (Saldo Excedente)</div>
                                        @endif
                                        <small>{{ $pagosContados }} pagos</small>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Caja 3: Pendientes (rojo) -->
                            <div class="col">
                                <div class="card bg-danger text-white h-100">
                                    <div class="card-body text-center">
                                        <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
                                        <h6 class="card-title">Pendientes</h6>
                                        @php
                                            // Mantener el cálculo de totalMes para que esté disponible para otras partes de la vista
                                            $totalMes = 0;
                                            
                                            if(isset($diagnostico['pasos'][1]['resultado'])) {
                                                foreach($diagnostico['pasos'][1]['resultado'] as $cuota) {
                                                    $fechaVencimiento = new DateTime($cuota->fecha_de_vencimiento);
                                                    if($fechaVencimiento->format('m') == $diagnostico['mes_consultado'] && 
                                                       $fechaVencimiento->format('Y') == $diagnostico['ano_consultado']) {
                                                        $totalMes += $cuota->monto;
                                                    }
                                                }
                                            }
                                            
                                            // Usar directamente el valor calculado en el controlador
                                            $montoPendiente = $diagnostico['totales']['deuda'];
                                            
                                            // Calcular cuantas cuotas están pendientes
                                            $cuotasPendientes = 0;
                                            if(isset($diagnostico['pasos'][1]['resultado'])) {
                                                foreach($diagnostico['pasos'][1]['resultado'] as $cuota) {
                                                    $fechaVencimiento = new DateTime($cuota->fecha_de_vencimiento);
                                                    if($fechaVencimiento->format('m') == $diagnostico['mes_consultado'] && 
                                                       $fechaVencimiento->format('Y') == $diagnostico['ano_consultado']) {
                                                        if($cuota->estado == 'pendiente' || $cuota->estado == 'parcial') {
                                                            $cuotasPendientes++;
                                                        }
                                                    }
                                                }
                                            }
                                        @endphp
                                        <h4>U$D {{ number_format($montoPendiente, 2) }}</h4>
                                        <small>{{ $cuotasPendientes }} pendientes</small>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Caja 4: Total Mes (azul) -->
                            <div class="col">
                                <div class="card bg-primary text-white h-100">
                                    <div class="card-body text-center">
                                        <i class="fas fa-calendar-alt fa-2x mb-2"></i>
                                        <h6 class="card-title">Total Mes</h6>
                                        <h4>U$D {{ number_format($totalMes, 2) }}</h4>
                                        <small>{{ $totalCuotasMes }} cuotas</small>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Caja 5: Navegador de mes -->
                            <div class="col">
                                <div class="card {{ $esElMesActual ? 'bg-warning' : 'bg-light' }} h-100">
                                    <div class="card-body p-2">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <a href="{{ route('informes.index', ['mes' => $diagnostico['mes_consultado'] == 1 ? 12 : $diagnostico['mes_consultado'] - 1, 'ano' => $diagnostico['mes_consultado'] == 1 ? $diagnostico['ano_consultado'] - 1 : $diagnostico['ano_consultado']]) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-chevron-left"></i>
                                            </a>
                                            <h6 class="mb-0 text-uppercase font-weight-bold">
                                                {{ Carbon\Carbon::createFromDate($diagnostico['ano_consultado'], $diagnostico['mes_consultado'], 1)->locale('es')->monthName }}
                                            </h6>
                                            <a href="{{ route('informes.index', ['mes' => $diagnostico['mes_consultado'] == 12 ? 1 : $diagnostico['mes_consultado'] + 1, 'ano' => $diagnostico['mes_consultado'] == 12 ? $diagnostico['ano_consultado'] + 1 : $diagnostico['ano_consultado']]) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-chevron-right"></i>
                                            </a>
                                        </div>
                                        <div class="text-center mt-2">
                                            <span class="badge bg-dark">{{ $diagnostico['ano_consultado'] }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Lista de Deudores con el estilo solicitado -->
                        <div class="card mt-4">
                            <div class="card-header bg-secondary text-white">
                                <h5 class="mb-0 text-center text-uppercase"><i class="fas fa-list"></i> Lista de Deudores</h5>
                            </div>
                            <div class="card-body">
                                @if(isset($diagnostico['deudores']) && count($diagnostico['deudores']) > 0)
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped" id="tablaDeudores">
                                            <thead class="thead-dark">
                                                <tr>
                                                    <th>Nombre</th>
                                                    <th><i class="fas fa-envelope"></i> Email</th>
                                                    <th><i class="fas fa-phone"></i> Teléfono</th>
                                                    <th>Valor de Cuota (U$D)</th>
                                                    <th>Deuda (U$D)</th>
                                                    <th>Acreedor</th>
                                                    <th>Acciones</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($diagnostico['deudores'] as $deudor)
                                                <tr id="fila-deudor-{{ $deudor->id }}" class="{{ $deudor->judicializado == 1 ? 'border border-danger' : '' }}">
                                                    <td>{{ $deudor->nombre }}</td>
                                                    <td>{{ $deudor->email }}</td>
                                                    <td>{{ $deudor->telefono }}</td>
                                                    <td data-sort="{{ $valorCuota ?? 0 }}">
                                                        @php
                                                            $valorCuota = 0;
                                                            if(isset($diagnostico['pasos'][1]['resultado'])) {
                                                                foreach ($diagnostico['pasos'][1]['resultado'] as $cuota) {
                                                                    if ($cuota->comprador_id == $deudor->id) {
                                                                        $valorCuota = $cuota->monto;
                                                                        break;
                                                                    }
                                                                }
                                                            }
                                                        @endphp
                                                        {{ number_format($valorCuota, 2) }}
                                                    </td>
                                                    <td data-sort="{{ $deuda ?? 0 }}">
                                                        @php
                                                            $deuda = 0;
                                                            if(isset($diagnostico['pasos'][1]['resultado'])) {
                                                                foreach ($diagnostico['pasos'][1]['resultado'] as $cuota) {
                                                                    if ($cuota->comprador_id == $deudor->id) {
                                                                        if ($cuota->estado == 'pendiente') {
                                                                            $deuda = $cuota->monto;
                                                                        } elseif ($cuota->estado == 'parcial') {
                                                                            $montoOriginal = $cuota->monto;
                                                                            $pagosRealizados = 0;
                                                                            
                                                                            if(isset($diagnostico['pasos'][2]['resultado'])) {
                                                                                foreach ($diagnostico['pasos'][2]['resultado'] as $pago) {
                                                                                    if (property_exists($pago, 'cuota_id') && $pago->cuota_id == $cuota->cuota_id && 
                                                                                        property_exists($pago, 'monto_usd')) {
                                                                                        $pagosRealizados += $pago->monto_usd;
                                                                                    }
                                                                                }
                                                                            }
                                                                            
                                                                    $deuda = $montoOriginal - $pagosRealizados;
                                                                        }
                                                                        break;
                                                                    }
                                                                }
                                                            }
                                                        @endphp
                                                        <span class="text-danger font-weight-bold">{{ number_format($deuda, 2) }}</span>
                                                    </td>
                                                    <td>{{ $deudor->acreedor_id ?? 'N/A' }}</td>
                                                    <td>
                                                        <div class="btn-group">
                                                            <a href="{{ route('comprador.show', $deudor->id) }}" class="btn btn-sm bg-warning text-dark" data-toggle="tooltip" title="Ver detalles">
                                                                <i class="fas fa-eye"></i>
                                                            </a>
                                                            <button class="btn btn-sm btn-primary" data-toggle="tooltip" title="Enviar mensaje">
                                                                <i class="fas fa-envelope"></i>
                                                            </button>
                                                            <button class="btn btn-sm btn-success btn-registrar-pago" 
                                                                    data-toggle="tooltip" 
                                                                    title="Registrar pago" 
                                                                    data-id="{{ $deudor->id }}" 
                                                                    data-cuota-id="{{ isset($cuota->cuota_id) ? $cuota->cuota_id : '' }}">
                                                                <i class="fas fa-money-bill-wave"></i>
                                                            </button>
                                                            <button class="btn btn-sm btn-danger judicializar-btn" data-id="{{ $deudor->id }}" data-estado="{{ $deudor->judicializado }}" data-toggle="tooltip" title="Judicializar">
                                                                <i class="fas fa-gavel"></i>
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <div class="alert alert-info">
                                        No hay deudores para mostrar en este período.
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>
                                                
    <!-- jQuery (necesario para DataTables) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    
    <script>
        $(document).ready(function() {
            // Inicializar DataTables
            $('#tablaDeudores').DataTable({
                "order": [[4, 'desc']], // Ordenar por la columna de deuda de mayor a menor
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.11.5/i18n/es-ES.json"
                },
                "pageLength": 25,
                "columnDefs": [
                    { "orderable": true, "targets": [0, 1, 3, 4, 5] },
                    { "orderable": false, "targets": [2, 6] },
                    { "type": "num", "targets": [3, 4] }
                ]
            });
            
            // Activar los tooltips
            $('[data-toggle="tooltip"]').tooltip();
            
            // Manejador para los botones de judicializar
            $('.judicializar-btn').click(function() {
                const deudorId = $(this).data('id');
                const estadoActual = $(this).data('estado');
                const nuevoEstado = estadoActual == 1 ? 0 : 1;
                const fila = $('#fila-deudor-' + deudorId);
                
                // Actualiza visualmente la fila
                if (nuevoEstado === 1) {
                    fila.addClass('border border-danger');
                } else {
                    fila.removeClass('border border-danger');
                }
                
                // Actualiza el data-estado del botón
                $(this).data('estado', nuevoEstado);
                
                // Aquí se haría la llamada AJAX para actualizar el estado en la BD
                console.log(`Actualizando deudor ${deudorId} a judicializado=${nuevoEstado}`);
            });
            
            // Manejador para los botones de registrar pago
            $('.btn-registrar-pago').click(function() {
                const deudorId = $(this).data('id');
                const cuotaId = $(this).data('cuota-id');
                
                // Aquí iría el código para mostrar un modal o formulario de pago
                
                // Después de procesar el pago exitosamente, recargar la página
                // para actualizar tanto la lista de deudores como las cajas de resumen
                $(document).on('pago:realizado', function() {
                    window.location.reload();
                });
            });
        });
    </script>
    <!-- Necesario para activar los tooltips -->
    <script>
    $(function () {
      $('[data-toggle="tooltip"]').tooltip()
    })
    </script>

    <!-- Formulario oculto para actualizar el estado judicializado -->
    <form id="judicializar-form" method="POST" style="display: none;">
        @csrf
        @method('PATCH')
        <input type="hidden" name="judicializado" id="judicializado-value">
    </form>

    <!-- Sección de Detalles (agregar después de la tabla de deudores) -->
    <div class="card mt-4 border-secondary">
        <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Mostrar/Ocultar Detalles</h5>
            <button class="btn btn-sm btn-light" type="button" data-bs-toggle="collapse" data-bs-target="#collapseDebug" aria-expanded="false">
                <i class="fas fa-chevron-down"></i>
            </button>
        </div>
        <div class="collapse" id="collapseDebug">
            <div class="card-body">
                @php
                    // Análisis de cuotas
                    $cuotasAnalisis = [];
                    $totalMonto = 0;
                    $totalPagado = 0;
                    $totalExcedente = 0;
                    $totalPendiente = 0;
                    $cuotasPagadas = 0;
                    $cuotasPendientes = 0;
                    $cuotasParciales = 0;
                    
                    if(isset($diagnostico['pasos'][1]['resultado'])) {
                        foreach($diagnostico['pasos'][1]['resultado'] as $cuota) {
                            $fechaVencimiento = new DateTime($cuota->fecha_de_vencimiento);
                            $enMesConsultado = ($fechaVencimiento->format('m') == $diagnostico['mes_consultado'] && 
                                               $fechaVencimiento->format('Y') == $diagnostico['ano_consultado']);
                            
                            // Calcular pagos para esta cuota
                            $pagadoEnCuota = 0;
                            $excedenteEnCuota = 0;
                            $pagosCuota = [];
                            
                            if(isset($diagnostico['pasos'][2]['resultado'])) {
                                foreach($diagnostico['pasos'][2]['resultado'] as $pago) {
                                    if($pago->cuota_id == $cuota->cuota_id) {
                                        // Verificar si es un pago que debe ignorarse
                                        if(property_exists($pago, 'es_pago_excedente') && $pago->es_pago_excedente == 1) {
                                            continue; // No considerar estos pagos
                                        }
                                        
                                        // Verificar si hay excedente
                                        if($pago->monto_usd > $cuota->monto) {
                                            $pagadoEnCuota += $cuota->monto;
                                            $excedenteEnCuota += ($pago->monto_usd - $cuota->monto);
                                            
                                            $pagosCuota[] = [
                                                'id' => $pago->id,
                                                'monto' => $pago->monto_usd,
                                                'excedente' => ($pago->monto_usd - $cuota->monto)
                                            ];
                                        } else {
                                            $pagadoEnCuota += $pago->monto_usd;
                                            
                                            $pagosCuota[] = [
                                                'id' => $pago->id,
                                                'monto' => $pago->monto_usd,
                                                'excedente' => 0
                                            ];
                                        }
                                    }
                                }
                            }
                            
                            if($enMesConsultado) {
                                $totalMonto += $cuota->monto;
                                $totalPagado += min($pagadoEnCuota, $cuota->monto);
                                $totalExcedente += $excedenteEnCuota;
                                $totalPendiente += max(0, $cuota->monto - $pagadoEnCuota);
                                
                                if($cuota->estado == 'pagada') {
                                    $cuotasPagadas++;
                                } else if($cuota->estado == 'pendiente') {
                                    $cuotasPendientes++;
                                } else if($cuota->estado == 'parcial') {
                                    $cuotasParciales++;
                                }
                            }
                            
                            $cuotasAnalisis[] = [
                                'id' => $cuota->cuota_id,
                                'monto' => $cuota->monto,
                                'estado' => $cuota->estado,
                                'comprador' => $cuota->nombre_comprador,
                                'fecha_vencimiento' => $cuota->fecha_de_vencimiento,
                                'en_mes_consultado' => $enMesConsultado,
                                'pagado' => min($pagadoEnCuota, $cuota->monto),
                                'excedente' => $excedenteEnCuota,
                                'pendiente' => max(0, $cuota->monto - $pagadoEnCuota),
                                'pagos' => $pagosCuota
                            ];
                        }
                    }
                @endphp
                
                <h6 class="mt-4">Detalle de Cuotas</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-striped table-bordered">
                        <thead class="table-dark">
                            <tr>
                                <!-- Columna ID ocultada -->
                                <th>Comprador</th>
                                <th>Monto</th>
                                <th>Estado</th>
                                <th>Vencimiento</th>
                                <!-- Columna "En mes consultado" ocultada -->
                                <th>Pagado</th>
                                <th>Excedente</th>
                                <th>Pendiente</th>
                                <th>Detalle Pagos</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($cuotasAnalisis as $c)
                            <tr class="{{ $c['en_mes_consultado'] ? 'table-primary' : '' }}">
                                <!-- Columna ID ocultada -->
                                <td>{{ $c['comprador'] }}</td>
                                <td>U$D {{ number_format($c['monto'], 2) }}</td>
                                <td>
                                    <span class="badge bg-{{ $c['estado'] == 'pagada' ? 'success' : ($c['estado'] == 'parcial' ? 'warning' : 'danger') }}">
                                        {{ $c['estado'] }}
                                    </span>
                                </td>
                                <td>{{ $c['fecha_vencimiento'] }}</td>
                                <!-- Columna "En mes consultado" ocultada -->
                                <td>U$D {{ number_format($c['pagado'], 2) }}</td>
                                <td>
                                    @if($c['excedente'] > 0)
                                        <span class="text-primary">U$D {{ number_format($c['excedente'], 2) }}</span>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>U$D {{ number_format($c['pendiente'], 2) }}</td>
                                <td>
                                    @if(count($c['pagos']) > 0)
                                        <ul class="list-unstyled mb-0">
                                        @foreach($c['pagos'] as $p)
                                            <li>
                                                ID: {{ $p['id'] }} - U$D {{ number_format($p['monto'], 2) }}
                                                @if($p['excedente'] > 0)
                                                    <span class="text-primary">(Excedente: U$D {{ number_format($p['excedente'], 2) }})</span>
                                                @endif
                                            </li>
                                        @endforeach
                                        </ul>
                                    @else
                                        <span class="text-muted">Sin pagos</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-dark">
                            <tr>
                                <!-- Ajustado para las columnas ocultas -->
                                <td><strong>TOTALES</strong></td>
                                <td>U$D {{ number_format($totalMonto, 2) }}</td>
                                <td>
                                    <span class="badge bg-success">{{ $cuotasPagadas }} pagadas</span>
                                    <span class="badge bg-warning">{{ $cuotasParciales }} parciales</span>
                                    <span class="badge bg-danger">{{ $cuotasPendientes }} pendientes</span>
                                </td>
                                <td></td>
                                <td>U$D {{ number_format($totalPagado, 2) }}</td>
                                <td>
                                    <span class="text-primary">U$D {{ number_format($totalExcedente, 2) }}</span>
                                </td>
                                <td>U$D {{ number_format($totalPendiente, 2) }}</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 