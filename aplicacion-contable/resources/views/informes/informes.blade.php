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

        <div class="card">
            <div class="card-header bg-secondary text-white">
                <h5 class="mb-0 text-center text-uppercase"><i class="fas fa-chart-bar"></i> Informes del Sistema</h5>
            </div>
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
                        
                        <div class="card mb-3 {{ $esElMesActual ? 'bg-warning' : '' }}">
                            <div class="card-body py-2">
                                <div class="d-flex justify-content-between align-items-center">
                                    <a href="{{ route('informes.index', ['mes' => $diagnostico['mes_consultado'] == 1 ? 12 : $diagnostico['mes_consultado'] - 1, 'ano' => $diagnostico['mes_consultado'] == 1 ? $diagnostico['ano_consultado'] - 1 : $diagnostico['ano_consultado']]) }}" class="btn btn-outline-primary">
                                        <i class="fas fa-chevron-left"></i>
                                    </a>
                                    <h4 class="mb-0 text-uppercase font-weight-bold">
                                        <i class="fas fa-calendar-alt"></i> {{ Carbon\Carbon::createFromDate($diagnostico['ano_consultado'], $diagnostico['mes_consultado'], 1)->locale('es')->monthName }} {{ $diagnostico['ano_consultado'] }}
                                    </h4>
                                    <a href="{{ route('informes.index', ['mes' => $diagnostico['mes_consultado'] == 12 ? 1 : $diagnostico['mes_consultado'] + 1, 'ano' => $diagnostico['mes_consultado'] == 12 ? $diagnostico['ano_consultado'] + 1 : $diagnostico['ano_consultado']]) }}" class="btn btn-outline-primary">
                                        <i class="fas fa-chevron-right"></i>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Lista de Deudores -->
                        <h4 class="mt-4 mb-3">Lista de Deudores</h4>
                        @if(isset($diagnostico['deudores']) && count($diagnostico['deudores']) > 0)
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped" id="tablaDeudores">
                                    <thead class="thead-dark">
                                        <tr>
                                            <th>Nombre</th>
                                            <th><i class="fas fa-envelope"></i> Email</th>
                                            <th><i class="fas fa-phone"></i> Teléfono</th>
                                            <th>Valor de Cuota</th>
                                            <th>Deuda</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($diagnostico['deudores'] as $deudor)
                                        <tr id="fila-deudor-{{ $deudor->id }}" class="{{ $deudor->judicializado == 1 ? 'border border-danger' : '' }}">
                                            <td>{{ $deudor->nombre }}</td>
                                            <td>{{ $deudor->email }}</td>
                                            <td>{{ $deudor->telefono }}</td>
                                            <td class="text-muted">
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
                                                ${{ number_format($valorCuota, 2) }}
                                            </td>
                                            <td class="text-danger font-weight-bold">
                                                @php
                                                    $deuda = 0;
                                                    if(isset($diagnostico['pasos'][1]['resultado'])) {
                                                        foreach ($diagnostico['pasos'][1]['resultado'] as $cuota) {
                                                            if ($cuota->comprador_id == $deudor->id) {
                                                                if ($cuota->estado == 'pendiente') {
                                                                    // Si está pendiente, la deuda es el monto completo
                                                                    $deuda = $cuota->monto;
                                                                } elseif ($cuota->estado == 'parcial') {
                                                                    // Si es parcial, calculamos monto - pagos_realizados
                                                                    $montoOriginal = $cuota->monto;
                                                                    $pagosRealizados = 0;
                                                                    
                                                                    // Buscar los pagos relacionados con esta cuota
                                                                    if(isset($diagnostico['pasos'][2]['resultado'])) {
                                                                        foreach ($diagnostico['pasos'][2]['resultado'] as $pago) {
                                                                            if (property_exists($pago, 'cuota_id') && $pago->cuota_id == $cuota->cuota_id && 
                                                                                property_exists($pago, 'monto_usd')) {
                                                                                $pagosRealizados += $pago->monto_usd;
                                                                            }
                                                                        }
                                                                    }
                                                                    
                                                                    // La deuda es la diferencia
                                                                    $deuda = $montoOriginal - $pagosRealizados;
                                                                }
                                                                break; // Una vez encontrada la cuota, salimos del bucle
                                                            }
                                                        }
                                                    }
                                                @endphp
                                                -${{ number_format($deuda, 2) }}
                                            </td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="{{ route('comprador.show', $deudor->id) }}" class="btn btn-sm bg-warning text-dark" data-toggle="tooltip" title="Ver detalles">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <button class="btn btn-sm btn-primary" data-toggle="tooltip" title="Enviar mensaje">
                                                        <i class="fas fa-envelope"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-success" data-toggle="tooltip" title="Registrar pago">
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
                    @endif
                @endif
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    $(document).ready(function() {
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

    <!-- JavaScript para la funcionalidad de judicializar -->
    <script>
    $(function () {
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
            
            // Ejemplo de implementación AJAX
            /*
            $.ajax({
                url: '/api/compradores/' + deudorId + '/judicializar',
                type: 'POST',
                data: {
                    '_token': $('meta[name="csrf-token"]').attr('content'),
                    'judicializado': nuevoEstado
                },
                success: function(response) {
                    console.log('Estado actualizado correctamente');
                },
                error: function(error) {
                    // Revertir el cambio visual en caso de error
                    if (nuevoEstado === 1) {
                        fila.removeClass('border border-danger');
                    } else {
                        fila.addClass('border border-danger');
                    }
                    $(this).data('estado', estadoActual); // Revertir el estado del botón
                    console.error('Error al actualizar estado:', error);
                }
            });
            */
        });
    });
    </script>
</body>
</html> 