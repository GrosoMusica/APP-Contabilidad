<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle del Comprador</title>
    <!-- Font Awesome - PRIMERO para evitar conflictos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
                        @php
                            // Calcular el monto real abonado sumando todos los pagos
                            $pagosRealizados = \App\Models\Pago::whereHas('cuota', function($query) use ($comprador) {
                                $query->whereHas('financiacion', function($q) use ($comprador) {
                                    $q->where('comprador_id', $comprador->id);
                                });
                            })->sum('monto_usd');
                            
                            // Calcular el saldo real pendiente
                            $saldoPendienteReal = $comprador->financiacion->monto_a_financiar - $pagosRealizados;
                        @endphp
                        
                        <p><strong>Monto Total:</strong> U$D {{ number_format($comprador->financiacion->monto_a_financiar, 2) }}</p>
                        <p><strong>Abonado Hasta la Fecha:</strong> U$D {{ number_format($pagosRealizados, 2) }}</p>
                        <p><strong>Saldo Pendiente:</strong> U$D {{ number_format($saldoPendienteReal, 2) }}</p>
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
                        @php
                            // Fechas básicas para comparación
                            $hoy = \Carbon\Carbon::now();
                            $inicioMes = \Carbon\Carbon::now()->startOfMonth();
                            $finMes = \Carbon\Carbon::now()->endOfMonth();
                            
                            // Asegurarse de que todas las fechas son instancias de Carbon
                            foreach ($cuotas as $cuota) {
                                if (!$cuota->fecha_de_vencimiento instanceof \Carbon\Carbon) {
                                    $cuota->fecha_de_vencimiento = \Carbon\Carbon::parse($cuota->fecha_de_vencimiento);
                                }
                            }
                            
                            // Encontrar la cuota del mes actual (la más simple posible)
                            $cuotaMesActual = $cuotas->first(function($cuota) use ($hoy) {
                                return $cuota->fecha_de_vencimiento->month == $hoy->month &&
                                       $cuota->fecha_de_vencimiento->year == $hoy->year;
                            });
                            
                            // Si no hay cuota este mes, tomar la próxima
                            if (!$cuotaMesActual) {
                                $cuotaMesActual = $cuotas->where('fecha_de_vencimiento', '>', $hoy)
                                                     ->sortBy('fecha_de_vencimiento')
                                                     ->first();
                            }
                            
                            // Contar cuotas atrasadas (simple)
                            $cuotasAtrasadas = $cuotas->where('estado', '!=', 'pagada')
                                                      ->where('estado', '!=', 'sin_comprobante')
                                                      ->where('fecha_de_vencimiento', '<', $hoy)
                                                      ->count();
                            
                            // Determinar estado general de la cuenta
                            $estadoCuenta = $cuotasAtrasadas > 0 ? 'Cuotas atrasadas' : 'Al día';
                            $claseCuenta = $cuotasAtrasadas > 0 ? 'text-danger' : 'text-success';
                        @endphp
                        Cuota Mes Actual
                        <p><strong>Fecha de Vencimiento:</strong> {{ $cuotaMesActual ? $cuotaMesActual->fecha_de_vencimiento->format('d-m-Y') : 'N/A' }}</p>
                    </div>
                    <div class="card-body">
                        @if($cuotaMesActual)
                            <p><strong>Monto:</strong> U$D {{ number_format($cuotaMesActual->monto, 2) }}</p>
                            <p><strong>Estado:</strong> 
                                @if($cuotaMesActual->estado == 'pagada' || $cuotaMesActual->estado == 'sin_comprobante')
                                    <span class="text-success">Pagada</span>
                                @elseif($cuotaMesActual->fecha_de_vencimiento < $hoy)
                                    <span class="text-danger" style="{{ $cuotaMesActual->fecha_de_vencimiento->month < $hoy->month ? 'border:2px solid red; padding:2px 5px; display:inline-block;' : '' }}">
                                        {{ $cuotaMesActual->fecha_de_vencimiento->month < $hoy->month ? 'Adeuda' : 'Vencida' }}
                                    </span>
                                @else
                                    <span class="text-warning">Pendiente</span>
                                @endif
                            </p>
                        @else
                            <p>No hay cuotas programadas para el mes actual.</p>
                        @endif
                        
                        <!-- Indicador de estado de cuenta -->
                        <div class="alert {{ $cuotasAtrasadas > 0 ? 'alert-danger' : 'alert-success' }} mt-3">
                            <strong>Estado de cuenta:</strong> <span class="{{ $claseCuenta }}">{{ $estadoCuenta }}</span>
                            @if($cuotasAtrasadas > 0)
                                <br>Hay {{ $cuotasAtrasadas }} cuota(s) atrasada(s).
                            @endif
                        </div>
                    </div>

                    <!-- Desplegable de Cuotas -->
                    <x-cuotas-accordion :cuotas="$cuotas" :inicioMes="$inicioMes" :hoy="$hoy" :finMes="$finMes" />
                </div>
            </div>

            <!-- Acreedores -->
            <div class="col-md-12 mt-4">
                <x-acreedores :acreedores="$acreedores" :comprador="$comprador" />
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 