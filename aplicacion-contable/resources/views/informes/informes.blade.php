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
        /* Reset completo para la barra de navegación */
        .navbar-nav .nav-item .nav-link {
            color: rgba(255, 255, 255, 0.55) !important;
            font-weight: normal !important;
            background-color: transparent !important; 
            padding: 0.5rem 1rem !important;
        }
        .navbar-nav .nav-item .nav-link.active {
            color: rgba(255, 255, 255, 1) !important;
            background-color: transparent !important;
            font-weight: normal !important;
        }
        .navbar {
            padding-top: 0.5rem !important;
            padding-bottom: 0.5rem !important;
        }
        #navbarSupportedContent .nav-link:hover {
            background-color: transparent !important;
            border-color: transparent !important;
        }
        #navbarSupportedContent .nav-link {
            border: none !important;
        }
        /* Para que las tarjetas tengan la misma altura */
        .equal-height-card {
            height: 100%;
            display: flex;
            flex-direction: column;
        }
        .equal-height-card .card-body {
            flex: 1;
        }
        /* Estilo para elementos judicializados */
        .judicializado {
            border: 2px solid red !important;
            background-color: #fff0f0 !important;
        }
    </style>
</head>
<body class="antialiased">
    <!-- Navegación -->
    @include('partials.top_bar')

    <div class="container mt-4">
        <div class="row mb-4">
            <div class="col-md-12">
                <h2 class="text-secondary">Panel de Informes</h2>
            </div>
        </div>

        <div class="card">
            <div class="card-header bg-secondary text-white">
                <h5 class="mb-0 text-center text-uppercase"><i class="fas fa-chart-bar"></i> Informes del Sistema</h5>
            </div>
            <div class="card-body">
                <!-- Pestañas de navegación -->
                <ul class="nav nav-tabs mb-3" id="informesTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="resumen-tab" data-bs-toggle="tab" data-bs-target="#resumen" type="button" role="tab" aria-controls="resumen" aria-selected="true">
                            <i class="fas fa-chart-pie"></i> Resumen Mensual
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="deudores-tab" data-bs-toggle="tab" data-bs-target="#deudores" type="button" role="tab" aria-controls="deudores" aria-selected="false">
                            <i class="fas fa-exclamation-triangle"></i> Deudores
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="deudores-atrasados-tab" data-bs-toggle="tab" data-bs-target="#deudores-atrasados" type="button" role="tab" aria-controls="deudores-atrasados" aria-selected="false">
                            <i class="fas fa-clock"></i> Deudores Atrasados
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="proximos-finalizar-tab" data-bs-toggle="tab" data-bs-target="#proximos-finalizar" type="button" role="tab" aria-controls="proximos-finalizar" aria-selected="false">
                            <i class="fas fa-flag-checkered"></i> Próximos a Finalizar
                        </button>
                    </li>
                </ul>
                
                <!-- Contenido de las pestañas -->
                <div class="tab-content" id="informesTabsContent">
                    <!-- Pestaña de Resumen Mensual -->
                    <div class="tab-pane fade show active" id="resumen" role="tabpanel" aria-labelledby="resumen-tab">
                        @if(isset($diagnostico))
                            <div class="alert alert-info text-center">
                                <h4 class="mb-0"><i class="fas fa-calendar-alt"></i> Periodo: {{ Carbon\Carbon::createFromDate($diagnostico['ano_consultado'], $diagnostico['mes_consultado'], 1)->locale('es')->monthName }} {{ $diagnostico['ano_consultado'] }}</h4>
                            </div>
                            
                            @if(isset($diagnostico['totales']))
                                <div class="row">
                                    <div class="col-md-3 mb-4">
                                        <div class="card bg-primary text-white equal-height-card">
                                            <div class="card-body">
                                                <h5 class="card-title">Total de Cuotas</h5>
                                                <h2 class="display-6">${{ number_format($diagnostico['totales']['total_cuotas'], 2) }}</h2>
                                                <p>{{ $diagnostico['totales']['cantidad_cuotas'] }} cuotas</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3 mb-4">
                                        <div class="card bg-success text-white equal-height-card">
                                            <div class="card-body">
                                                <h5 class="card-title">Total Cobrado</h5>
                                                <h2 class="display-6">${{ number_format($diagnostico['totales']['total_pagado'], 2) }}</h2>
                                                <p>{{ $diagnostico['totales']['cantidad_pagos'] }} pagos</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3 mb-4">
                                        <div class="card bg-danger text-white equal-height-card">
                                            <div class="card-body">
                                                <h5 class="card-title">Saldo Pendiente</h5>
                                                <h2 class="display-6">${{ number_format($diagnostico['totales']['saldo_pendiente'], 2) }}</h2>
                                                <p>&nbsp;</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3 mb-4">
                                        <div class="card bg-info text-white equal-height-card">
                                            <div class="card-body">
                                                <h5 class="card-title">Porcentaje Cobrado</h5>
                                                <h2 class="display-6">{{ $diagnostico['totales']['total_cuotas'] > 0 ? number_format(($diagnostico['totales']['total_pagado'] / $diagnostico['totales']['total_cuotas']) * 100, 1) : 0 }}%</h2>
                                                <p>&nbsp;</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Gráfico de Resumen -->
                                <div class="row mt-4">
                                    <div class="col-lg-8">
                                        <div class="card mb-4">
                                            <div class="card-header">
                                                <h5><i class="fas fa-chart-bar"></i> Resumen Mensual</h5>
                                            </div>
                                            <div class="card-body">
                                                <div id="graficoResumen" style="height: 300px;">
                                                    <div class="alert alert-secondary">
                                                        <p class="text-center">El gráfico se cargará próximamente...</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="card mb-4">
                                            <div class="card-header">
                                                <h5><i class="fas fa-clipboard-list"></i> Detalle por Estado</h5>
                                            </div>
                                            <div class="card-body">
                                                <div class="table-responsive">
                                                    <table class="table table-striped">
                                                        <thead>
                                                            <tr>
                                                                <th>Estado</th>
                                                                <th>Cantidad</th>
                                                                <th>Monto</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <tr>
                                                                <td><span class="badge bg-success">Pagada</span></td>
                                                                <td>{{ $diagnostico['totales']['cantidad_pagos'] }}</td>
                                                                <td>${{ number_format($diagnostico['totales']['total_pagado'], 2) }}</td>
                                                            </tr>
                                                            <tr>
                                                                <td><span class="badge bg-danger">Pendiente</span></td>
                                                                <td>{{ $diagnostico['totales']['cantidad_cuotas'] - $diagnostico['totales']['cantidad_pagos'] }}</td>
                                                                <td>${{ number_format($diagnostico['totales']['saldo_pendiente'], 2) }}</td>
                                                            </tr>
                                                            <tr class="table-active">
                                                                <td><strong>Total</strong></td>
                                                                <td><strong>{{ $diagnostico['totales']['cantidad_cuotas'] }}</strong></td>
                                                                <td><strong>${{ number_format($diagnostico['totales']['total_cuotas'], 2) }}</strong></td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="alert alert-warning">
                                    <h4><i class="fas fa-exclamation-circle"></i> Sin datos</h4>
                                    <p>No hay información de cuotas disponible para este periodo.</p>
                                </div>
                            @endif
                        @else
                            <div class="alert alert-danger">
                                <h4><i class="fas fa-exclamation-triangle"></i> Error</h4>
                                <p>No se pudo obtener información para generar el informe.</p>
                            </div>
                        @endif
                    </div>
                    
                    <!-- Pestaña de Deudores - Corregido el campo teléfono que no está en los resultados -->
                    <div class="tab-pane fade" id="deudores" role="tabpanel" aria-labelledby="deudores-tab">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h4><i class="fas fa-user-times"></i> Listado de Deudores</h4>
                            <div>
                                <button id="exportarDeudores" class="btn btn-danger btn-sm">
                                    <i class="fas fa-file-pdf"></i> Exportar PDF
                                </button>
                                <button id="imprimirDeudores" class="btn btn-secondary btn-sm">
                                    <i class="fas fa-print"></i> Imprimir
                                </button>
                            </div>
                        </div>
                        
                        <!-- Usar el paso 1 específicamente que contiene el JOIN con compradores -->
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Comprador</th>
                                        <th>Email</th>
                                        <th>Teléfono</th>
                                        <th>Cuota</th>
                                        <th>Monto</th>
                                        <th>Estado</th>
                                        <th>Vencimiento</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $deudoresEncontrados = false; @endphp
                                    
                                    @foreach($diagnostico['pasos'][1]['resultado'] as $deudor)
                                        @if(strtolower($deudor->estado) == 'pendiente' || strtolower($deudor->estado) == 'parcial')
                                            @php $deudoresEncontrados = true; @endphp
                                            <tr>
                                                <td>{{ $deudor->nombre }}</td>
                                                <td>{{ $deudor->email }}</td>
                                                <td>No disponible</td> <!-- Campo fijo ya que no está en los resultados -->
                                                <td>{{ $deudor->numero_de_cuota ?? $deudor->cuota_id }}</td>
                                                <td>${{ number_format($deudor->monto, 2) }}</td>
                                                <td>
                                                    @if(strtolower($deudor->estado) == 'pendiente')
                                                        <span class="badge bg-danger">Pendiente</span>
                                                    @elseif(strtolower($deudor->estado) == 'parcial')
                                                        <span class="badge bg-warning text-dark">Parcial</span>
                                                    @endif
                                                </td>
                                                <td>{{ \Carbon\Carbon::parse($deudor->fecha_de_vencimiento)->format('d/m/Y') }}</td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        <button type="button" class="btn btn-outline-primary">
                                                            <i class="fas fa-envelope"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-outline-success">
                                                            <i class="fas fa-dollar-sign"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endif
                                    @endforeach
                                    
                                    @if(!$deudoresEncontrados)
                                        <tr>
                                            <td colspan="8" class="text-center">No se encontraron deudores con cuotas pendientes o parciales</td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <!-- Pestaña de Deudores Atrasados -->
                    <div class="tab-pane fade" id="deudores-atrasados" role="tabpanel" aria-labelledby="deudores-atrasados-tab">
                        <div class="row">
                            <!-- Deudores con 2 meses de atraso -->
                            <div class="col-md-6">
                                <div class="card mb-4">
                                    <div class="card-header bg-warning text-dark">
                                        <h5 class="mb-0"><i class="fas fa-exclamation-circle"></i> Con 2 meses de atraso</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-sm table-striped">
                                                <thead>
                                                    <tr>
                                                        <th>Comprador</th>
                                                        <th>Teléfono</th>
                                                        <th>Deuda</th>
                                                        <th>Acciones</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @if(isset($deudoresAtrasados2Meses) && count($deudoresAtrasados2Meses) > 0)
                                                        @foreach($deudoresAtrasados2Meses as $deudor)
                                                            <tr>
                                                                <td>{{ $deudor->nombre }}</td>
                                                                <td>{{ $deudor->telefono ?? 'No disponible' }}</td>
                                                                <td>${{ number_format($deudor->deuda_total, 2) }}</td>
                                                                <td>
                                                                    <div class="btn-group btn-group-sm">
                                                                        <button type="button" class="btn btn-outline-primary">
                                                                            <i class="fas fa-envelope"></i>
                                                                        </button>
                                                                        <button type="button" class="btn btn-outline-success">
                                                                            <i class="fas fa-dollar-sign"></i>
                                                                        </button>
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    @else
                                                        <tr>
                                                            <td colspan="4" class="text-center">No hay deudores con 2 meses de atraso</td>
                                                        </tr>
                                                    @endif
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Deudores con 3 o más meses de atraso -->
                            <div class="col-md-6">
                                <div class="card mb-4">
                                    <div class="card-header bg-danger text-white">
                                        <h5 class="mb-0"><i class="fas fa-exclamation-triangle"></i> Con 3+ meses de atraso</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-sm table-striped">
                                                <thead>
                                                    <tr>
                                                        <th>Comprador</th>
                                                        <th>Teléfono</th>
                                                        <th>Deuda</th>
                                                        <th>Judicializado</th>
                                                        <th>Acciones</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @if(isset($deudoresAtrasados3Meses) && count($deudoresAtrasados3Meses) > 0)
                                                        @foreach($deudoresAtrasados3Meses as $deudor)
                                                            <tr class="{{ $deudor->judicializado ? 'judicializado' : '' }}">
                                                                <td>{{ $deudor->nombre }}</td>
                                                                <td>{{ $deudor->telefono ?? 'No disponible' }}</td>
                                                                <td>${{ number_format($deudor->deuda_total, 2) }}</td>
                                                                <td>
                                                                    <div class="form-check form-switch">
                                                                        <input class="form-check-input toggle-judicializado" 
                                                                               type="checkbox" 
                                                                               data-id="{{ $deudor->comprador_id }}" 
                                                                               {{ $deudor->judicializado ? 'checked' : '' }}>
                                                                    </div>
                                                                </td>
                                                                <td>
                                                                    <div class="btn-group btn-group-sm">
                                                                        <button type="button" class="btn btn-outline-primary">
                                                                            <i class="fas fa-envelope"></i>
                                                                        </button>
                                                                        <button type="button" class="btn btn-outline-success">
                                                                            <i class="fas fa-dollar-sign"></i>
                                                                        </button>
                                                                        <button type="button" class="btn btn-outline-danger">
                                                                            <i class="fas fa-gavel"></i>
                                                                        </button>
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    @else
                                                        <tr>
                                                            <td colspan="5" class="text-center">No hay deudores con 3 o más meses de atraso</td>
                                                        </tr>
                                                    @endif
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Pestaña de Próximos a Finalizar -->
                    <div class="tab-pane fade" id="proximos-finalizar" role="tabpanel" aria-labelledby="proximos-finalizar-tab">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h4><i class="fas fa-flag-checkered"></i> Próximos a Finalizar Plan</h4>
                            <div>
                                <button id="exportarProximos" class="btn btn-success btn-sm">
                                    <i class="fas fa-file-excel"></i> Exportar
                                </button>
                            </div>
                        </div>
                        
                        <div class="alert alert-info">
                            <p><i class="fas fa-info-circle"></i> Este informe muestra compradores a quienes les restan 3 o menos cuotas para finalizar su plan.</p>
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Comprador</th>
                                        <th>Teléfono</th>
                                        <th>Email</th>
                                        <th>Plan</th>
                                        <th>Cuotas Restantes</th>
                                        <th>Monto Pendiente</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if(isset($proximosAFinalizar) && count($proximosAFinalizar) > 0)
                                        @foreach($proximosAFinalizar as $cliente)
                                            <tr>
                                                <td>{{ $cliente->nombre }}</td>
                                                <td>{{ $cliente->telefono ?? 'No disponible' }}</td>
                                                <td>{{ $cliente->email }}</td>
                                                <td>{{ $cliente->plan_nombre }}</td>
                                                <td><span class="badge bg-success">{{ $cliente->cuotas_restantes }}</span></td>
                                                <td>${{ number_format($cliente->monto_pendiente, 2) }}</td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        <button type="button" class="btn btn-outline-primary">
                                                            <i class="fas fa-envelope"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-outline-info">
                                                            <i class="fas fa-certificate"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="7" class="text-center">No hay compradores próximos a finalizar su plan</td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
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
            // Mantener la pestaña activa al recargar la página
            const hash = window.location.hash;
            if (hash) {
                const tab = document.querySelector(`#informesTabs a[href="${hash}"]`);
                if (tab) {
                    const bsTab = new bootstrap.Tab(tab);
                    bsTab.show();
                }
            }
            
            // Actualizar URL al cambiar de pestaña
            document.querySelectorAll('#informesTabs button').forEach(tab => {
                tab.addEventListener('shown.bs.tab', function (e) {
                    history.pushState(null, null, '#' + e.target.getAttribute('data-bs-target').substring(1));
                });
            });
            
            // Exportar a PDF
            document.getElementById('exportarDeudores')?.addEventListener('click', function() {
                alert('La funcionalidad de exportación a PDF estará disponible próximamente');
            });
            
            // Funcionalidad de impresión
            document.getElementById('imprimirDeudores')?.addEventListener('click', function() {
                window.print();
            });
            
            // Toggle judicializado
            document.querySelectorAll('.toggle-judicializado').forEach(toggle => {
                toggle.addEventListener('change', function() {
                    const id = this.getAttribute('data-id');
                    const isJudicializado = this.checked;
                    
                    // Cambiar estilo de la fila
                    const row = this.closest('tr');
                    if (isJudicializado) {
                        row.classList.add('judicializado');
                    } else {
                        row.classList.remove('judicializado');
                    }
                    
                    // Aquí iría la llamada AJAX para actualizar el estado en la base de datos
                    console.log(`Cambiando estado judicializado a ${isJudicializado} para ID ${id}`);
                    // fetch('/api/compradores/'+id+'/judicializar', {
                    //     method: 'POST',
                    //     headers: {'Content-Type': 'application/json'},
                    //     body: JSON.stringify({judicializado: isJudicializado})
                    // });
                });
            });
        });
    </script>
</body>
</html> 