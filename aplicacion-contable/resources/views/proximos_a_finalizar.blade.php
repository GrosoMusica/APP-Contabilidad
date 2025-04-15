<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Próximos a Finalizar</title>
    <!-- Fonts -->
    <link href="https://fonts.bunny.net/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <!-- Estilos de informes -->
    <link rel="stylesheet" href="{{ asset('css/informes.css') }}">
    <style>
        body {
            font-family: 'Nunito', sans-serif;
            background-color: rgba(33, 37, 41, 0.2);
        }
        .card {
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .badge-count {
            font-size: 0.9rem;
            margin-left: 5px;
        }
        .estado-pendiente { color: #dc3545; }
        .estado-parcial { color: #ffc107; }
        .estado-pagada { color: #28a745; }
        .estado-futura { color: #6c757d; }
        .action-buttons {
            display: flex;
            gap: 8px;
        }
        .cuotas-badge {
            font-size: 0.8rem;
            margin-top: 5px;
            display: inline-block;
        }
        .table td {
            vertical-align: middle;
        }
        .icon-column {
            color: #6c757d;
            margin-right: 8px;
            width: 20px;
            text-align: center;
            display: inline-block;
        }
        .finalizado-row {
            background-color: rgba(40, 167, 69, 0.1) !important;
        }
    </style>
</head>
<body>
    <!-- Navegación -->
    @include('partials.top_bar')

    <div class="container mt-4">
        <div class="card mb-4">
            <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Compradores Próximos a Finalizar</h5>
                <div class="d-flex align-items-center">
                    <input type="text" id="nombreBusqueda" class="form-control form-control-sm me-2" placeholder="Buscar por nombre...">
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped" id="tablaProximos">
                        <thead>
                            <tr>
                                <th><i class="fas fa-user icon-column"></i> Comprador</th>
                                <th><i class="far fa-envelope icon-column"></i> Email</th>
                                <th><i class="fas fa-phone icon-column"></i> Teléfono</th>
                                <th>Lote</th>
                                <th class="titulo-con-accion">
                                    Cuotas Faltantes
                                    <button class="btn-finalizados" id="mostrarFinalizados">
                                        <i class="fas fa-flag-checkered btn-finalizados-icon"></i>
                                    </button>
                                </th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($detallesCompradores as $detalle)
                                <tr class="comprador-row {{ $detalle['finalizado'] ? 'finalizado-row' : '' }}" data-finalizado="{{ $detalle['finalizado'] ? '1' : '0' }}">
                                    <td>
                                        {{ $detalle['comprador']->nombre }}
                                        <br>
                                        <span class="badge bg-primary cuotas-badge">
                                            {{ $detalle['estadisticas']['total'] }} cuotas totales
                                        </span>
                                    </td>
                                    <td>
                                        {{ $detalle['comprador']->email }}
                                    </td>
                                    <td>
                                        {{ $detalle['comprador']->telefono }}
                                    </td>
                                    <td>
                                        @if($detalle['comprador']->lote)
                                            Mza: {{ $detalle['comprador']->lote->manzana }} - Lote: {{ $detalle['comprador']->lote->lote }}
                                        @else
                                            No asignado
                                        @endif
                                    </td>
                                    <td>
                                        @if($detalle['estadisticas']['parciales'] > 0)
                                            <span class="badge bg-warning badge-count">
                                                <i class="fas fa-hand-holding-usd"></i> {{ $detalle['estadisticas']['parciales'] }}
                                            </span>
                                        @endif
                                        @if($detalle['estadisticas']['pendientes'] > 0)
                                            <span class="badge bg-danger badge-count">
                                                <i class="fas fa-exclamation-triangle"></i> {{ $detalle['estadisticas']['pendientes'] }}
                                            </span>
                                        @endif
                                        @if($detalle['estadisticas']['futuras'] > 0)
                                            <span class="badge bg-secondary badge-count">
                                                <i class="fas fa-clock"></i> {{ $detalle['estadisticas']['futuras'] }}
                                            </span>
                                        @endif
                                        @if($detalle['finalizado'])
                                            <span class="badge bg-success">Finalizado</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="{{ route('comprador.show', $detalle['comprador']->id) }}" class="btn btn-info btn-sm" title="Ver detalle">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="mailto:{{ $detalle['comprador']->email }}" class="btn btn-mail-light btn-sm" title="Enviar mensaje">
                                                <i class="far fa-envelope"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    
    <script>
        $(document).ready(function() {
            // Inicializar DataTables
            var tabla = $('#tablaProximos').DataTable({
                "paging": false,
                "info": false,
                "searching": true,
                "ordering": true,
                "dom": 't',
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.11.5/i18n/es-ES.json"
                },
                "columnDefs": [
                    { "searchable": true, "targets": [0, 1] },  // Solo buscar en nombre y email
                    { "searchable": false, "targets": [2, 3, 4, 5] },  // No buscar en el resto
                    { "orderable": true, "targets": [0, 1] },  // Solo ordenar nombre y email
                    { "orderable": false, "targets": [2, 3, 4, 5] }  // No ordenar el resto
                ]
            });
            
            // Buscar por nombre
            $('#nombreBusqueda').on('keyup', function() {
                tabla.search(this.value).draw();
            });
            
            // Mostrar solo finalizados
            $('#mostrarFinalizados').click(function() {
                toggleFinalizados();
            });
            
            var mostrandoSoloFinalizados = false;
            
            // Función para alternar entre todos y solo finalizados
            function toggleFinalizados() {
                mostrandoSoloFinalizados = !mostrandoSoloFinalizados;
                
                if (mostrandoSoloFinalizados) {
                    $('.comprador-row').hide();
                    $('.comprador-row[data-finalizado="1"]').show();
                } else {
                    $('.comprador-row').show();
                }
                
                tabla.draw();
            }
        });
    </script>
</body>
</html> 