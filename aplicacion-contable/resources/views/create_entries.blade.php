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
    <style>
        body {
            background-color: #f5f5dc; /* Color crema claro */
            font-family: 'Nunito', sans-serif;
        }
        h4 {
            text-transform: uppercase;
            font-size: 1.25rem; /* Tamaño de fuente más pequeño */
            font-weight: bold; /* Texto en negrita */
            text-align: center; /* Centrar texto */
        }
        .section-border {
            border: 1px solid #ccc;
            padding: 15px;
            margin-bottom: 20px;
            background-color: #fff; /* Fondo blanco para las secciones */
        }
        .form-control {
            background-color: #fff; /* Fondo blanco para los inputs */
        }
        .btn-submit {
            display: block;
            width: 100%;
            max-width: 200px;
            margin: 20px auto; /* Centrar el botón */
            background-color: #28a745; /* Color verde */
            border-color: #28a745;
        }
        .alert-success {
            background-color: #218838; /* Verde más intenso */
            color: white;
        }
    </style>
</head>
<body>
    <!-- Navegación -->
    @include('partials.top_bar')

    <div class="container mt-5">
        <h1 class="text-center">Crear Entradas</h1>
        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <!-- Mostrar errores -->
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        
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
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>