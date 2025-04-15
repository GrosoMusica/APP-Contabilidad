<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Balance de Pagos a Acreedores</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f5e6; /* Fondo beige suave */
        }
        .card {
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>

@include('partials.top_bar')

<div class="container-fluid py-4">
    <h1 class="mb-4">Balance de Pagos a Acreedores</h1>
    
    @if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger">
        {{ session('error') }}
    </div>
    @endif

    <!-- Detalles de pagos por acreedor -->
    <div class="row">
        @php
            // Separar Admin (id=1) para mostrarlo último
            $adminAcreedor = null;
            $otrosAcreedores = [];
            
            foreach($acreedores as $acreedor) {
                if($acreedor->id == 1) {
                    $adminAcreedor = $acreedor;
                } else {
                    $otrosAcreedores[] = $acreedor;
                }
            }
        @endphp
        
        <!-- Para los acreedores NORMALES (no admin) -->
        @foreach($otrosAcreedores as $acreedor)
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">{{ $acreedor->nombre }}</h5>
                </div>
                <div class="card-body">
                    @php
                    // Datos para el mes actual (en lugar del mes seleccionado)
                    $mesActual = date('Y-m');
                    
                    // Calcular montos
                    $saldoTotal = 0; // Iniciar en 0
                    
                    // Obtenemos todas las cuotas pagadas para las financiaciones donde participa este acreedor
                    // Con fecha anterior a la actual
                    $financiacionIds = DB::table('financiacion_acreedor')
                        ->where('acreedor_id', $acreedor->id)
                        ->pluck('financiacion_id');
                        
                    $cuotasPagadas = \App\Models\Cuota::whereIn('financiacion_id', $financiacionIds)
                        ->whereIn('estado', ['pagada', 'parcial'])
                        ->where('fecha_de_vencimiento', '<=', now())
                        ->get();
                        
                    // Para cada cuota, calcular la parte que le corresponde al acreedor según su porcentaje
                    foreach($cuotasPagadas as $cuota) {
                        // Obtenemos el porcentaje de este acreedor en esta financiación
                        $porcentaje = DB::table('financiacion_acreedor')
                            ->where('financiacion_id', $cuota->financiacion_id)
                            ->where('acreedor_id', $acreedor->id)
                            ->value('porcentaje') ?? 0;
                            
                        // Calculamos el monto que le corresponde (basado en su porcentaje)
                        $montoCorrespondiente = ($cuota->monto * $porcentaje) / 100;
                        $saldoTotal += $montoCorrespondiente;
                    }
                    
                    // Obtener todos los pagos recibidos por este acreedor (mes actual)
                    $pagosRecibidos = \App\Models\Pago::where('acreedor_id', $acreedor->id)
                        ->whereMonth('created_at', now()->month)
                        ->whereYear('created_at', now()->year)
                        ->get();
                        
                    // Obtener todas las liquidaciones para este acreedor (mes actual)
                    $liquidaciones = \App\Models\Liquidacion::where('acreedor_id', $acreedor->id)
                        ->whereMonth('fecha', now()->month)
                        ->whereYear('fecha', now()->year)
                        ->get();
                        
                    // Calcular el total recibido (sin aplicar porcentaje)
                    $totalMontoUsd = $pagosRecibidos->sum('monto_usd');
                    
                    // Calcular el saldo pendiente (lo que debería recibir menos lo que ya recibió)
                    $saldoPendiente = $saldoTotal - $totalMontoUsd;
                    @endphp
                    
                    <!-- Mostramos el listado de pagos y liquidaciones para acreedor normal (sin título) -->
                    @if(count($pagosRecibidos) > 0 || count($liquidaciones) > 0)
                    <div class="mb-4">
                        <div class="table-responsive">
                            <table class="table table-sm table-striped">
                                <thead>
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Monto USD</th>
                                        <th>Origen</th>
                                        <th>Porcentaje</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Pagos normales -->
                                    @foreach($pagosRecibidos as $pago)
                                    <tr>
                                        <td>{{ $pago->created_at->format('d/m/Y') }}</td>
                                        <td>${{ number_format($pago->monto_usd, 2) }}</td>
                                        <td>
                                            @if($pago->cuota && $pago->cuota->financiacion && $pago->cuota->financiacion->comprador)
                                                {{ $pago->cuota->financiacion->comprador->nombre }} {{ $pago->cuota->financiacion->comprador->apellido }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>
                                            @php
                                                $porcentaje = 0;
                                                if($pago->cuota && $pago->cuota->financiacion) {
                                                    $porcentaje = DB::table('financiacion_acreedor')
                                                        ->where('financiacion_id', $pago->cuota->financiacion_id)
                                                        ->where('acreedor_id', $acreedor->id)
                                                        ->value('porcentaje') ?? 0;
                                                }
                                            @endphp
                                            <span class="badge bg-info">{{ $porcentaje }}%</span>
                                        </td>
                                        <td>
                                            @if(!$pago->sin_comprobante && $pago->comprobante)
                                            <button class="btn btn-sm btn-outline-primary" onclick="verComprobante('{{ $pago->comprobante }}')">
                                                <i class="fas fa-receipt"></i> Ver
                                            </button>
                                            @else
                                            <span class="badge bg-secondary">Sin comprobante</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                    
                                    <!-- Liquidaciones (con estilo diferente) -->
                                    @foreach($liquidaciones as $liquidacion)
                                    <tr class="table-info">
                                        <td>{{ \Carbon\Carbon::parse($liquidacion->fecha)->format('d/m/Y') }}</td>
                                        <td>${{ number_format($liquidacion->monto, 2) }}</td>
                                        <td><strong class="text-success">ADMIN</strong></td>
                                        <td>-</td>
                                        <td>
                                            @if(!$liquidacion->sin_comprobante && $liquidacion->comprobante)
                                            <button class="btn btn-sm btn-outline-primary" onclick="verComprobante('{{ $liquidacion->comprobante }}')">
                                                <i class="fas fa-receipt"></i> Ver
                                            </button>
                                            @else
                                            <span class="badge bg-secondary">Sin comprobante</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @else
                    <div class="alert alert-info mb-4">
                        No hay movimientos registrados en este mes.
                    </div>
                    @endif
                    
                    <div class="mb-3">
                        <table class="table table-sm">
                            <tr class="text-secondary">
                                <td><i class="fas fa-money-bill-wave"></i> Saldo a recibir:</td>
                                <td class="text-end">${{ number_format($saldoTotal, 2) }}</td>
                            </tr>
                            
                            <tr>
                                <td><i class="fas fa-wallet text-success"></i> Saldo actual:</td>
                                <td class="text-end fw-bold text-success">${{ number_format($acreedor->saldo, 2) }}</td>
                            </tr>
                            
                            <tr>
                                <td><i class="fas fa-hourglass-half text-warning"></i> Saldo pendiente:</td>
                                <td class="text-end">${{ number_format($saldoPendiente, 2) }}</td>
                            </tr>
                            <tr>
                                <td><i class="fas fa-money-check-alt text-info"></i> Total recibido:</td>
                                <td class="text-end">${{ number_format($totalMontoUsd, 2) }}</td>
                            </tr>
                        </table>
                    </div>
                    
                    <div class="d-grid gap-2 mt-4">
                        <button type="button" 
                                class="btn btn-dark" 
                                data-bs-toggle="modal" 
                                data-bs-target="#liquidarModal{{ $acreedor->id }}"
                                {{ $saldoPendiente <= 0 ? 'disabled' : '' }}>
                            <i class="fas fa-hand-holding-usd"></i> Liquidar Pago
                        </button>
                    </div>
                </div>
            </div>
        </div>
        @endforeach

        <!-- Para el ADMIN (ID=1) - Sin listado de pagos y sin botón liquidar -->
        @if($adminAcreedor)
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">{{ $adminAcreedor->nombre }} <span class="badge bg-info">Admin</span></h5>
                </div>
                <div class="card-body">
                    @php
                    // Mismo código de cálculo que para los acreedores normales pero para el admin
                    // ... (utilizando $adminAcreedor en lugar de $acreedor)
                    @endphp
                    
                    <!-- NO mostramos listado de pagos para Admin -->
                    
                    <!-- Sí mostramos información de saldos -->
                    <div class="mb-3">
                        <table class="table table-sm">
                            <tr class="text-secondary">
                                <td><i class="fas fa-money-bill-wave"></i> Saldo a recibir:</td>
                                <td class="text-end">${{ number_format($saldoTotal ?? 0, 2) }}</td>
                            </tr>
                            
                            <tr>
                                <td><i class="fas fa-wallet text-success"></i> Saldo actual:</td>
                                <td class="text-end fw-bold text-success">${{ number_format($adminAcreedor->saldo, 2) }}</td>
                            </tr>
                            
                            <tr>
                                <td><i class="fas fa-hourglass-half text-warning"></i> Saldo pendiente:</td>
                                <td class="text-end">${{ number_format($saldoPendiente ?? 0, 2) }}</td>
                            </tr>
                            <tr>
                                <td><i class="fas fa-money-check-alt text-info"></i> Total recibido:</td>
                                <td class="text-end">${{ number_format($totalMontoUsd ?? 0, 2) }}</td>
                            </tr>
                        </table>
                    </div>
                    
                    <!-- NO mostramos botón Liquidar para Admin -->
                    
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Modal con bg-dark -->
@foreach($otrosAcreedores as $acreedor)
<div class="modal fade" id="liquidarModal{{ $acreedor->id }}" tabindex="-1" aria-labelledby="liquidarModalLabel{{ $acreedor->id }}" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title" id="liquidarModalLabel{{ $acreedor->id }}">Liquidar Pago a {{ $acreedor->nombre }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('api.acreedores.actualizar-saldo', $acreedor->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info">
                        <p class="mb-1"><strong>Saldo pendiente:</strong> ${{ number_format($saldoPendiente ?? 0, 2) }}</p>
                        <p class="mb-1"><strong>Saldo actual en cuenta:</strong> ${{ number_format($acreedor->saldo, 2) }}</p>
                    </div>
                    
                    <div class="mb-3">
                        <label for="montoLiquidacion{{ $acreedor->id }}" class="form-label">Monto a liquidar (USD)</label>
                        <input type="text" class="form-control" id="montoLiquidacion{{ $acreedor->id }}" 
                               name="monto" value="{{ min($saldoPendiente ?? 0, $acreedor->saldo) }}" required>
                        <small class="form-text text-muted">Ingrese el monto sin símbolos, ejemplo: 400.00</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="fechaLiquidacion{{ $acreedor->id }}" class="form-label">Fecha de liquidación</label>
                        <input type="date" class="form-control" id="fechaLiquidacion{{ $acreedor->id }}" 
                               name="fecha_liquidacion" value="{{ date('Y-m-d') }}" required>
                    </div>
                    
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="sinComprobante{{ $acreedor->id }}" name="sin_comprobante" value="1">
                        <label class="form-check-label" for="sinComprobante{{ $acreedor->id }}">
                            Marcar como pago sin comprobante
                        </label>
                    </div>
                    
                    <!-- Área de comprobante que se mostrará/ocultará -->
                    <div id="areaComprobante{{ $acreedor->id }}">
                        <div class="mb-3">
                            <label for="comprobante{{ $acreedor->id }}" class="form-label">
                                Comprobante de pago <span class="text-danger">*</span>
                            </label>
                            <input type="file" class="form-control" id="comprobante{{ $acreedor->id }}" 
                                   name="comprobante" accept=".jpg,.jpeg,.png,.pdf" required>
                            <div class="form-text">
                                Formatos permitidos: JPG, PNG, PDF. Máximo 2MB.
                            </div>
                        </div>
                    </div>
                    
                    <!-- Mensaje que se mostrará cuando esté marcado "sin comprobante" -->
                    <div id="mensajeSinComprobante{{ $acreedor->id }}" class="alert alert-warning" style="display: none;">
                        <i class="fas fa-exclamation-triangle"></i> Se registrará el pago sin comprobante.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Confirmar Liquidación</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach

<!-- JavaScript para manejar la interacción entre comprobante y sin comprobante -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Para cada acreedor, configurar la interacción
    @foreach($acreedores as $acreedor)
    (function() {
        const fileInput = document.getElementById('comprobante{{ $acreedor->id }}');
        const checkboxSinComprobante = document.getElementById('sinComprobante{{ $acreedor->id }}');
        const areaComprobante = document.getElementById('areaComprobante{{ $acreedor->id }}');
        const mensajeSinComprobante = document.getElementById('mensajeSinComprobante{{ $acreedor->id }}');
        
        // Función para actualizar la visibilidad según el estado del checkbox
        function actualizarVisibilidad() {
            if (checkboxSinComprobante && checkboxSinComprobante.checked) {
                // Si está marcado "sin comprobante"
                if (areaComprobante) areaComprobante.style.display = 'none';
                if (mensajeSinComprobante) mensajeSinComprobante.style.display = 'block';
                if (fileInput) {
                    fileInput.removeAttribute('required');
                    fileInput.value = '';
                }
            } else {
                // Si NO está marcado "sin comprobante"
                if (areaComprobante) areaComprobante.style.display = 'block';
                if (mensajeSinComprobante) mensajeSinComprobante.style.display = 'none';
                if (fileInput) {
                    fileInput.setAttribute('required', 'required');
                }
            }
        }
        
        // Aplicar al cargar la página
        if (checkboxSinComprobante) {
            actualizarVisibilidad();
            
            // Y cada vez que cambie el checkbox
            checkboxSinComprobante.addEventListener('change', actualizarVisibilidad);
        }
    })();
    @endforeach
});
</script>

<!-- Bootstrap JS Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    });
});

// Función para ver comprobantes
function verComprobante(path) {
    // Usamos el controlador dedicado para ver comprobantes
    const url = '{{ route("comprobantes.ver") }}?path=' + encodeURIComponent(path);
    
    // Abrir en una nueva ventana o modal
    window.open(url, '_blank');
}
</script>
</body>
</html> 