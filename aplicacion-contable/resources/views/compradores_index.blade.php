<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Compradores</title>
    <!-- Fonts -->
    <link href="https://fonts.bunny.net/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
    <!-- CSS personalizado para compradores -->
    <link rel="stylesheet" href="{{ asset('css/compradores.css') }}">
    <style>
        /* Quitar borde celeste al enfocar el input */
        #dtSearchBox:focus {
            box-shadow: none;
            border-color: #ced4da; /* Color de borde normal de Bootstrap */
        }
    </style>
</head>
<body>
    <!-- Navegación -->
    @include('partials.top_bar')
    
    <div class="container mt-4">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Compradores Registrados</h5>
            </div>
                <div class="card-body">
                <!-- Buscador mejorado de DataTables (simplificado) -->
            <div class="row justify-content-start">
                    <div class="col-md-5">
                        <div class="input-group">
                            <span class="input-group-text bg-primary text-white">
                                <i class="fas fa-search"></i>
                            </span>
                            <input type="text" id="dtSearchBox" class="form-control" 
                                   placeholder="Búsqueda por Nombre o Email">
                        </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-striped" id="tablaCompradores">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Email</th>
                                <th>Teléfono</th>
                                <th>Lote</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($compradores as $comprador)
                            <tr class="{{ $comprador->judicializado ? 'judicializado' : '' }}">
                                <td>{{ $comprador->nombre }}</td>
                                <td>{{ $comprador->email }}</td>
                                <td>{{ $comprador->telefono }}</td>
                                <td>
                                    @if($comprador->lote)
                                        Mza: {{ $comprador->lote->manzana }} - Lote: {{ $comprador->lote->lote }}
                                    @else
                                        No asignado
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('comprador.show', $comprador->id) }}" class="btn btn-info btn-sm btn-accion" title="Ver detalle">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('comprador.edit', $comprador->id) }}" class="btn btn-warning btn-sm btn-accion" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="{{ route('pagos.index', ['comprador_id' => $comprador->id]) }}" class="btn btn-success btn-sm btn-accion" title="Registrar pago">
                                        <i class="fas fa-money-bill-wave"></i>
                                    </a>
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
            // Inicializar DataTables con ordenamiento solo en la columna nombre (índice 0)
            var tabla = $('#tablaCompradores').DataTable({
                "paging": false,
                "info": false,
                "searching": true,
                "ordering": true,
                "columnDefs": [
                    { "orderable": true, "targets": 0 },  // Solo el nombre es ordenable
                    { "orderable": false, "targets": "_all" } // El resto no son ordenables
                ],
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.11.5/i18n/es-ES.json",
                    "search": "Buscar:",
                    "searchPlaceholder": "Filtrar resultados..."
                },
                "dom": 't' // Solo mostrar la tabla, sin el buscador nativo (usaremos el nuestro)
            });
            
            // Conectar nuestro buscador personalizado con la funcionalidad de búsqueda de DataTables
            $('#dtSearchBox').on('keyup', function() {
                tabla.search($(this).val()).draw();
            });
            
            // Quitar placeholder al obtener el foco
            $('#dtSearchBox').on('focus', function() {
                $(this).attr('placeholder', '');
            });
            
            // Restaurar placeholder al perder el foco si el campo está vacío
            $('#dtSearchBox').on('blur', function() {
                if ($(this).val() === '') {
                    $(this).attr('placeholder', 'Búsqueda por nombre, email, mza o lote');
                }
            });
            
            // Enfoque automático al campo de búsqueda cuando se carga la página
            $('#dtSearchBox').focus();
        });
    </script>
</body>
</html> 