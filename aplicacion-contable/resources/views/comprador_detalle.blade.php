<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle del Comprador</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f5f5f5; /* Fondo gris claro */
        }
        .card {
            margin-bottom: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .card-header {
            background-color: #007bff;
            color: white;
            font-weight: bold;
        }
        .card-body {
            background-color: #ffffff;
        }
        .accordion-button {
            color: #007bff;
            background-color: transparent;
            border: none;
            font-weight: bold;
            text-align: left;
            width: 100%;
            padding: 0;
        }
        .accordion-button:focus {
            box-shadow: none;
        }
    </style>
</head>
<body>
    @include('partials.top_bar')
    <div class="container mt-5">
        <div class="row">
            <!-- Datos del Comprador -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        Datos del Comprador
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Nombre:</strong> {{ $comprador->nombre }}</p>
                                <p><strong>Dirección:</strong> {{ $comprador->direccion }}</p>
                                <p><strong>DNI:</strong> {{ $comprador->dni }}</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Teléfono:</strong> {{ $comprador->telefono }}</p>
                                <p><strong>Email:</strong> {{ $comprador->email }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Balance -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        Balance
                    </div>
                    <div class="card-body">
                        <p><strong>Monto:</strong> U$D {{ number_format($comprador->financiacion->monto_a_financiar, 2) }}</p>
                        <p><strong>Abonado Hasta la Fecha:</strong> U$D {{ number_format($abonadoHastaLaFecha, 2) }}</p>
                        <p><strong>Saldo Pendiente:</strong> U$D {{ number_format($saldoPendiente, 2) }}</p>
                    </div>
                </div>
            </div>

            <!-- Información del Lote -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        Información del Lote
                    </div>
                    <div class="card-body">
                        <p><strong>Loteo:</strong> {{ $comprador->lote->loteo }}</p>
                        <p><strong>Manzana:</strong> {{ $comprador->lote->manzana }}</p>
                        <p><strong>Lote:</strong> {{ $comprador->lote->lote }}</p>
                    </div>
                </div>
            </div>

            <!-- Cuota Actual y Desplegable -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        Cuota Actual #{{ $cuotas->first()->numero_de_cuota }}
                        <p><strong>Fecha de Vencimiento:</strong> {{ $cuotas->first()->fecha_de_vencimiento->format('d-m-Y') }}</p>
                    </div>
                    <div class="card-body">
                        <p><strong>Monto:</strong> U$D {{ number_format($cuotas->first()->monto, 2) }}</p>
                        <p><strong>Estado:</strong> <span class="{{ $cuotas->first()->estado_color }}">{{ ucfirst($cuotas->first()->estado) }}</span></p>
                    </div>

                    <!-- Desplegable de Cuotas -->
                    <div class="accordion" id="accordionCuotas">
                        <div class="card">
                            <div class="card-header" id="headingCuotas">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseCuotas" aria-expanded="true" aria-controls="collapseCuotas">
                                    Ver Todas las Cuotas
                                </button>
                            </div>

                            <div id="collapseCuotas" class="accordion-collapse collapse" aria-labelledby="headingCuotas" data-bs-parent="#accordionCuotas">
                                <div class="card-body">
                                    <ul class="list-group">
                                        @foreach($cuotas as $cuota)
                                            <li class="list-group-item">
                                                <strong>Cuota #{{ $cuota->numero_de_cuota }}</strong>
                                                <p>Monto: U$D {{ number_format($cuota->monto, 2) }}</p>
                                                <p>Fecha de Vencimiento: {{ $cuota->fecha_de_vencimiento->format('d-m-Y') }}</p>
                                                <p>Estado: <span class="{{ $cuota->estado == 'pagada' ? 'text-success' : ($cuota->estado == 'pendiente' ? 'text-warning' : 'text-danger') }}">{{ ucfirst($cuota->estado) }}</span></p>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Acreedores -->
            <div class="col-md-12 mt-4">
                @include('components.acreedores', ['acreedores' => $acreedores])
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 