<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnóstico de Relaciones</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .table-container {
            max-height: 600px;
            overflow-y: auto;
        }
        .error-row {
            background-color: #ffcccc;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <h1 class="mb-4">Diagnóstico de Relaciones</h1>
        
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        
        @if(session('warning'))
            <div class="alert alert-warning">{{ session('warning') }}</div>
        @endif
        
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif
        
        @if($error)
            <div class="alert alert-danger">{{ $error }}</div>
        @endif
        
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                Resumen
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h5>Estado de las Tablas</h5>
                        <p>Tabla Compradores tiene columnas necesarias: 
                            @if($tablaCompradorTieneColumnas)
                                <span class="badge bg-success">SI</span>
                            @else
                                <span class="badge bg-danger">NO</span>
                            @endif
                        </p>
                    </div>
                    <div class="col-md-6">
                        <h5>Estadísticas</h5>
                        <ul>
                            <li>Total de compradores: {{ $totalCompradores }}</li>
                            <li>Compradores sin lote_comprado_id: {{ $compradoresSinLoteCompradoId }}</li>
                            <li>Compradores sin financiacion_id: {{ $compradoresSinFinanciacionId }}</li>
                        </ul>
                    </div>
                </div>
                
                @if($puedeCorregir && ($compradoresSinLoteCompradoId > 0 || $compradoresSinFinanciacionId > 0))
                    <div class="mt-3">
                        <form action="{{ route('corregir.relaciones') }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-success">
                                Corregir Relaciones Automáticamente
                            </button>
                        </form>
                    </div>
                @endif
            </div>
        </div>
        
        @if(!empty($compradoresInfo))
            <div class="card">
                <div class="card-header bg-primary text-white">
                    Detalle de Compradores
                </div>
                <div class="card-body">
                    <div class="table-container">
                        <table class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nombre</th>
                                    <th>lote_comprado_id</th>
                                    <th>ID Lote Rel.</th>
                                    <th>financiacion_id</th>
                                    <th>ID Financ. Rel.</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($compradoresInfo as $info)
                                    <tr class="{{ $info['necesita_correccion'] ? 'error-row' : '' }}">
                                        <td>{{ $info['id'] }}</td>
                                        <td>{{ $info['nombre'] }}</td>
                                        <td>
                                            {{ $info['lote_comprado_id'] ?: 'NULL' }}
                                        </td>
                                        <td>
                                            @if($info['lote_existe'])
                                                <span class="badge bg-success">{{ $info['lote_relacionado_id'] }}</span>
                                                @if(!$info['lote_comprado_id'])
                                                    <span class="badge bg-warning">Debería asignarse</span>
                                                @endif
                                            @else
                                                <span class="badge bg-secondary">No existe</span>
                                            @endif
                                        </td>
                                        <td>
                                            {{ $info['financiacion_id'] ?: 'NULL' }}
                                        </td>
                                        <td>
                                            @if($info['financiacion_existe'])
                                                <span class="badge bg-success">{{ $info['financiacion_id_correcto'] }}</span>
                                                @if(!$info['financiacion_id'])
                                                    <span class="badge bg-warning">Debería asignarse</span>
                                                @endif
                                            @else
                                                <span class="badge bg-secondary">No existe</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($info['necesita_correccion'])
                                                <span class="badge bg-danger">Requiere corrección</span>
                                            @else
                                                <span class="badge bg-success">OK</span>
                                            @endif
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-info ver-detalles" 
                                                    data-id="{{ $info['id'] }}">
                                                Ver Detalles
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif
        
        <!-- Modal de detalles -->
        <div class="modal fade" id="detallesModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Detalles de la Entrada</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div id="detalles-contenido">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Cargando...</span>
                            </div>
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
            // Manejar clics en botones de detalles
            document.querySelectorAll('.ver-detalles').forEach(button => {
                button.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    const modal = new bootstrap.Modal(document.getElementById('detallesModal'));
                    
                    // Mostrar modal
                    modal.show();
                    
                    // Cargar detalles
                    fetch(`/verificar-entrada/${id}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                let html = '<pre>' + JSON.stringify(data.data, null, 2) + '</pre>';
                                document.getElementById('detalles-contenido').innerHTML = html;
                            } else {
                                document.getElementById('detalles-contenido').innerHTML = 
                                    `<div class="alert alert-danger">${data.error}</div>`;
                            }
                        })
                        .catch(error => {
                            document.getElementById('detalles-contenido').innerHTML = 
                                `<div class="alert alert-danger">Error: ${error.message}</div>`;
                        });
                });
            });
        });
    </script>
</body>
</html> 