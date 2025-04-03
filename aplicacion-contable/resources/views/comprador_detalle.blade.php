<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle del Comprador</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .timeline {
            list-style: none;
            padding: 0;
        }
        .timeline-item {
            margin-bottom: 20px;
            position: relative;
        }
        .timeline-item:before {
            content: '';
            position: absolute;
            top: 0;
            left: 20px;
            width: 10px;
            height: 10px;
            background-color: #ccc;
            border-radius: 50%;
        }
        .timeline-item.green:before {
            background-color: green;
        }
        .timeline-item.red:before {
            background-color: red;
        }
        .timeline-item.gray:before {
            background-color: gray;
        }
    </style>
</head>
<body>
    @include('partials.top_bar')
    <div class="container mt-5">
        <h1>Detalle del Comprador</h1>
        <div class="mb-4">
            <h4>Datos del Comprador</h4>
            <p>Nombre: {{ $comprador->nombre }}</p>
            <p>Dirección: {{ $comprador->direccion }}</p>
            <p>Teléfono: {{ $comprador->telefono }}</p>
            <p>Email: {{ $comprador->email }}</p>
            <p>DNI: {{ $comprador->dni }}</p>
        </div>

        <h4>Cuotas</h4>
        <ul class="timeline">
            @foreach($cuotas as $cuota)
                <li class="timeline-item {{ $cuota->estado == 'pagada' ? 'green' : ($cuota->fecha_de_vencimiento < now() ? 'red' : 'gray') }}">
                    <strong>Cuota #{{ $loop->iteration }}</strong>
                    <p>Monto: {{ $cuota->monto }}</p>
                    <p>Fecha de Vencimiento: {{ $cuota->fecha_de_vencimiento->format('d-m-Y') }}</p>
                    <p>Estado: {{ ucfirst($cuota->estado) }}</p>
                </li>
            @endforeach
        </ul>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 