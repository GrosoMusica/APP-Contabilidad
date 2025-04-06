<!-- resources/views/create_entries.blade.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Entradas</title>
    <!-- Fonts -->
    <link href="https://fonts.bunny.net/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="{{ asset('css/create_entries.css') }}" rel="stylesheet">
</head>
<body>
    <!-- Navegación -->
    @include('partials.top_bar')

    <div class="container mt-5">
        <h1 class="text-center">Crear Entradas</h1>
        
        <!-- Mostrar éxito y errores -->
        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        
        <!-- Mostrar errores específicos del CSV -->
        @if (session('csv_errors'))
            <div class="alert alert-warning">
                <h5>Detalles de errores en la importación:</h5>
                <ul>
                    @foreach (session('csv_errors') as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        
        <!-- Formulario para crear entrada individual -->
        <form action="{{ route('entries.store') }}" method="POST">
            @csrf
            <div class="row">
                <!-- Columna Izquierda: Comprador -->
                <div class="col-md-6">
                    <div class="section-border">
                        <h4>Comprador</h4>
                        <div class="mb-3">
                            <label for="nombre" class="form-label">Nombre</label>
                            <input type="text" class="form-control" id="nombre" name="nombre" required>
                        </div>
                        <div class="mb-3">
                            <label for="direccion" class="form-label">Dirección</label>
                            <input type="text" class="form-control" id="direccion" name="direccion" required>
                        </div>
                        <div class="mb-3">
                            <label for="telefono" class="form-label">Teléfono</label>
                            <input type="text" class="form-control" id="telefono" name="telefono" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="dni" class="form-label">DNI</label>
                            <input type="text" class="form-control" id="dni" name="dni" required>
                        </div>
                    </div>
                </div>

                <!-- Columna Derecha: Lote y Financiación -->
                <div class="col-md-6">
                    <div class="section-border">
                        <!-- Sección Lote -->
                        <h4>Lote</h4>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="manzana" class="form-label">Manzana</label>
                                    <input type="text" class="form-control" id="manzana" name="manzana" required>
                                </div>
                                <div class="mb-3">
                                    <label for="lote" class="form-label">Lote</label>
                                    <input type="text" class="form-control" id="lote" name="lote" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="loteo" class="form-label">Loteo</label>
                                    <input type="text" class="form-control" id="loteo" name="loteo" required>
                                </div>
                                <div class="mb-3">
                                    <label for="mts_cuadrados" class="form-label">Metros Cuadrados</label>
                                    <input type="number" step="0.01" class="form-control" id="mts_cuadrados" name="mts_cuadrados" required>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="section-border">
                        <!-- Sección Financiación -->
                        <h4>Financiación</h4>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="monto_a_financiar" class="form-label">Monto a Financiar (U$D)</label>
                                    <input type="number" step="0.01" class="form-control" id="monto_a_financiar" name="monto_a_financiar" required>
                                </div>
                                <div class="mb-3">
                                    <label for="cantidad_de_cuotas" class="form-label">Cantidad de Cuotas</label>
                                    <input type="number" class="form-control" id="cantidad_de_cuotas" name="cantidad_de_cuotas" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="fecha_de_vencimiento" class="form-label">Fecha de Registro</label>
                                    <input type="date" class="form-control" id="fecha_de_vencimiento" name="fecha_de_vencimiento" required>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-success btn-submit">Guardar</button>
        </form>
        
        <!-- Separador -->
        <hr class="my-5">
        
        <!-- Desplegable para importación masiva (MOVIDO AL FINAL) -->
        <div class="accordion mb-4" id="importAccordion">
            <div class="accordion-item">
                <h2 class="accordion-header" id="headingImport">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" 
                            data-bs-target="#collapseImport" aria-expanded="false" aria-controls="collapseImport">
                        <i class="fas fa-file-csv me-2"></i> Importación Masiva desde CSV
                    </button>
                </h2>
                <div id="collapseImport" class="accordion-collapse collapse" aria-labelledby="headingImport" data-bs-parent="#importAccordion">
                    <div class="accordion-body">
                        <div class="csv-import-box">
                            <p class="text-muted mb-3">Cargue un archivo CSV con múltiples entradas para procesarlas en lote.</p>
                            <form action="{{ route('entries.import') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="mb-3">
                                    <label for="csv_file" class="form-label">Seleccione el archivo CSV</label>
                                    <input type="file" class="form-control" id="csv_file" name="csv_file" accept=".csv">
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <button type="submit" class="btn btn-primary w-100">
                                            <i class="fas fa-upload me-2"></i>Importar entradas
                                        </button>
                                    </div>
                                    <div class="col-md-6">
                                        <a href="{{ route('entries.template') }}" class="btn btn-outline-secondary w-100">
                                            <i class="fas fa-download me-1"></i>Descargar plantilla
                                        </a>
                                    </div>
                                </div>
                            </form>
                            <div class="csv-info mt-3">
                                <p class="fw-bold">El archivo CSV debe contener las siguientes columnas:</p>
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Comprador</th>
                                                <th>Lote</th>
                                                <th>Financiación</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>nombre, direccion, telefono, email, dni</td>
                                                <td>manzana, lote, loteo, mts_cuadrados</td>
                                                <td>monto_a_financiar, cantidad_de_cuotas, fecha_de_vencimiento</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>