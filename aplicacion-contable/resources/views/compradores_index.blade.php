<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listado de Compradores</title>
    <!-- Fonts -->
    <link href="https://fonts.bunny.net/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Nunito', sans-serif;
        }
        .estado-cuotas {
            padding: 5px;
            border-radius: 5px;
            color: white;
        }
        .estado-cuotas.verde {
            background-color: green;
        }
        .estado-cuotas.amarillo {
            background-color: yellow;
            color: black;
        }
        .estado-cuotas.rojo {
            background-color: red;
        }
        .judicializado {
            border: 2px solid red;
            border-radius: 12px;
        }
    </style>
</head>
<body>
    <!-- Navegación -->
    @include('partials.top_bar')
    
    <div class="container mt-4">
        <div class="row mb-4">
            <div class="col-md-12">
                <h1>Listado de Compradores</h1>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Compradores Registrados</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Email</th>
                                <th>Teléfono</th>
                                <th>Estado de Cuota Actual</th>
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
                                    <form action="{{ route('comprador.toggleJudicializado', $comprador->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <input type="checkbox" onchange="this.form.submit()" {{ $comprador->judicializado ? 'checked' : '' }}>
                                    </form>
                                    <a href="{{ route('comprador.show', $comprador->id) }}" class="btn btn-info btn-sm">Ver Detalle</a>
                                    <a href="{{ route('comprador.edit', $comprador->id) }}" class="btn btn-warning btn-sm">Editar</a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 