<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listado de Compradores</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
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
    @include('partials.top_bar')
    <div class="container mt-5">
        <h1>Listado de Compradores</h1>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Email</th>
                    <th>Teléfono</th>
                    <th>Estado de las Cuotas</th>
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
                        <form action="{{ route('comprador.toggleJudicializado', $comprador->id) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <input type="checkbox" onchange="this.form.submit()" {{ $comprador->judicializado ? 'checked' : '' }}>
                        </form>
                    </td>
                    <td>
                        <a href="{{ route('comprador.show', $comprador->id) }}" class="btn btn-info btn-sm">Ver Detalle</a>
                        <a href="{{ route('comprador.edit', $comprador->id) }}" class="btn btn-warning btn-sm">Editar</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 