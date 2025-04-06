<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Pagos</title>
    <!-- Fonts -->
    <link href="https://fonts.bunny.net/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome - PRIMERO para evitar conflictos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Estilos personalizados para pagos -->
    <link href="{{ asset('css/pagos.css') }}" rel="stylesheet">
</head>
<body>
    <!-- Navegación -->
    @include('partials.top_bar')

    <div class="container mt-4">

        <!-- Mensajes Flash -->
        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif

        @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif

        <div class="row mb-4">
            <div class="col-md-8">
                <h2 class="text-primary">Gestión de Pagos</h2>
            </div>
            <div class="col-md-4 text-end">
                <a href="{{ route('compradores.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Volver a Compradores
                </a>
            </div>
        </div>

        <!-- Filtros de Búsqueda -->
        <div class="card filter-card mb-4">
            <div class="card-body">
                <form id="filtroForm" action="{{ route('pagos.index') }}" method="GET">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="comprador" class="form-label">Seleccionar Comprador</label>
                                <select id="comprador" name="comprador_id" class="form-select">
                                    <option value="">-- Seleccione un comprador --</option>
                                    @foreach($compradores as $comprador)
                                        <option value="{{ $comprador->id }}" {{ request('comprador_id') == $comprador->id ? 'selected' : '' }}>
                                            {{ $comprador->nombre }} {{ $comprador->apellido }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="dni" class="form-label">DNI</label>
                                <input type="text" id="dni" name="dni" class="form-control" value="{{ request('dni') }}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" id="email" name="email" class="form-control" value="{{ request('email') }}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="lote" class="form-label">Lote</label>
                                <input type="text" id="lote" name="lote" class="form-control" value="{{ request('lote') }}">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12 text-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search me-1"></i> Buscar
                            </button>
                            <a href="{{ route('pagos.index') }}" class="btn btn-secondary ms-2">
                                <i class="fas fa-redo me-1"></i> Limpiar
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Información del Comprador (si se seleccionó uno) -->
        @if(isset($compradorSeleccionado) && $compradorSeleccionado)
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            Datos del Comprador
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Nombre:</strong> {{ $compradorSeleccionado->nombre }} {{ $compradorSeleccionado->apellido }}</p>
                                    <p><strong>Dirección:</strong> {{ $compradorSeleccionado->direccion }}</p>
                                    <p><strong>DNI:</strong> {{ $compradorSeleccionado->dni }}</p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Teléfono:</strong> {{ $compradorSeleccionado->telefono }}</p>
                                    <p><strong>Email:</strong> {{ $compradorSeleccionado->email }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            Información del Lote
                        </div>
                        <div class="card-body">
                            <p><strong>Loteo:</strong> {{ $compradorSeleccionado->lote->loteo }}</p>
                            <p><strong>Manzana:</strong> {{ $compradorSeleccionado->lote->manzana }}</p>
                            <p><strong>Lote:</strong> {{ $compradorSeleccionado->lote->lote }}</p>
                            <p><strong>Monto a Financiar:</strong> U$D {{ number_format($compradorSeleccionado->financiacion->monto_a_financiar, 2, ',', '.') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Grid de Cuotas -->
            <div class="card">
                <div class="card-header">
                    Cuotas del Comprador
                </div>
                <div class="card-body">
                    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4 mt-3">
                        @php
                            $hoy = \Carbon\Carbon::now();
                            $inicioMes = \Carbon\Carbon::now()->startOfMonth();
                            $finMes = \Carbon\Carbon::now()->endOfMonth();
                        @endphp
                        
                        @foreach($cuotas as $index => $cuota)
                            @php
                                // Determinar el estilo basado en el estado y la fecha
                                $cardClass = '';
                                $estadoBadge = '';
                                $estadoText = '';
                                
                                if ($cuota->estado == 'pagada') {
                                    // Pagado - VERDE
                                    $cardClass = 'cuota-pagada estado-pagada';
                                    $estadoBadge = 'bg-success';
                                    $estadoText = 'Pagada';
                                } elseif ($cuota->estado == 'parcial') {
                                    // Pago parcial - NARANJA
                                    $cardClass = 'cuota-parcial estado-parcial';
                                    $estadoBadge = 'bg-warning';
                                    $estadoText = 'Pago Parcial';
                                } elseif ($cuota->fecha_de_vencimiento < $inicioMes) {
                                    // Adeuda - ROJO con borde (mes anterior)
                                    $cardClass = 'cuota-adeuda';
                                    $estadoBadge = 'bg-danger';
                                    $estadoText = 'Adeuda';
                                } elseif ($cuota->fecha_de_vencimiento <= $hoy) {
                                    // Vencido - ROJO (mismo mes, pasó la fecha)
                                    $cardClass = 'cuota-vencida';
                                    $estadoBadge = 'bg-danger';
                                    $estadoText = 'Vencida';
                                } elseif ($cuota->fecha_de_vencimiento <= $finMes) {
                                    // Pendiente - AMARILLO (mismo mes, antes de la fecha)
                                    $cardClass = 'cuota-pendiente';
                                    $estadoBadge = 'bg-warning text-dark';
                                    $estadoText = 'Pendiente';
                                } else {
                                    // Pendiente futuro
                                    $cardClass = 'cuota-futura';
                                    $estadoBadge = 'bg-secondary';
                                    $estadoText = 'Futura';
                                }
                                
                                // Obtener pagos y calcular saldo pendiente usando monto_usd
                                $pagos = \App\Models\Pago::where('cuota_id', $cuota->id)->orderBy('created_at', 'desc')->get();
                                $totalPagado = $pagos->sum('monto_usd');
                                $saldoPendiente = $cuota->monto - $totalPagado;
                            @endphp
                            
                            <div class="col">
                                <div class="card cuota-card {{ $cardClass }}">
                                    <div class="cuota-header bg-light d-flex justify-content-between align-items-center">
                                        <span>Cuota #{{ $cuota->numero_de_cuota }}</span>
                                        <span class="badge {{ $estadoBadge }} badge-cuota">
                                            {{ $estadoText }}
                                        </span>
                                    </div>
                                    <div class="card-body">
                                        <p class="card-text">U$D {{ number_format($cuota->monto, 2, ',', '.') }}</p>
                                        <p class="card-text"><strong>Vencimiento:</strong><br> {{ $cuota->fecha_de_vencimiento->format('d-m-Y') }}</p>
                                        
                                        @if($cuota->estado === 'pagada' || $cuota->estado === 'parcial')
                                            <div class="mb-1">
                                                <span class="{{ $cuota->estado === 'pagada' ? 'text-success' : 'text-warning' }}">
                                                    <i class="{{ $cuota->estado === 'pagada' ? 'fas fa-check-circle' : 'fas fa-clock' }}"></i> 
                                                    {{ $cuota->estado === 'pagada' ? 'Pagada' : 'Pago Parcial' }}
                                                </span>
                                                <small class="text-muted d-block">
                                                    Fecha último pago: {{ $cuota->updated_at->format('d/m/Y') }}
                                                </small>
                                                
                                                @if($pagos->count() > 0)
                                                    <small class="text-muted d-block">
                                                        @php
                                                            $totalPagadoUSD = $pagos->sum('monto_usd');
                                                            $totalPagadoPesos = $pagos->where('pago_divisa', 1)->sum('monto_pagado');
                                                            $totalPagadoDirectoUSD = $pagos->where('pago_divisa', 0)->sum('monto_usd');
                                                            
                                                            // Si hubo pagos en pesos, mostrar el total en ambas monedas
                                                            $mostrarDobleMoneda = $totalPagadoPesos > 0 && $totalPagadoDirectoUSD > 0;
                                                        @endphp
                                                        
                                                        Total pagado: 
                                                        @if($mostrarDobleMoneda)
                                                            ARS ${{ number_format($totalPagadoPesos, 2) }} + USD {{ number_format($totalPagadoDirectoUSD, 2) }}
                                                            <br>(Total USD: {{ number_format($totalPagadoUSD, 2) }})
                                                        @elseif($totalPagadoPesos > 0)
                                                            ARS ${{ number_format($totalPagadoPesos, 2) }}
                                                            <br>(Equivale a USD {{ number_format($totalPagadoUSD, 2) }})
                                                        @else
                                                            USD {{ number_format($totalPagadoUSD, 2) }}
                                                        @endif
                                                    </small>
                                                    
                                                    @if($cuota->estado === 'parcial')
                                                        <small class="text-danger d-block">
                                                            <strong>Saldo pendiente: USD {{ number_format($saldoPendiente, 2) }}</strong>
                                                        </small>
                                                    @elseif($saldoPendiente < 0)
                                                        <!-- Solo mostrar el excedente si no hay saldo pendiente -->
                                                        <small class="text-primary d-block">
                                                            <strong>Saldo excedente: USD {{ number_format(abs($saldoPendiente), 2) }}</strong>
                                                        </small>
                                                    @endif
                                                    
                                                    <!-- PAGOS -->
                                                    @foreach($pagos as $pago)
                                                        <div class="pago-item border-top mt-2 pt-1">
                                                            <small class="{{ isset($pago->es_pago_excedente) && $pago->es_pago_excedente ? 'text-primary' : 'text-muted' }} d-block">
                                                                <i class="{{ isset($pago->es_pago_excedente) && $pago->es_pago_excedente ? 'fas fa-star' : 'fas fa-receipt' }}"></i>
                                                                @if($pago->pago_divisa)
                                                                    ${{ number_format($pago->monto_pagado, 2) }} ARS
                                                                @else
                                                                    U$D {{ number_format($pago->monto_usd, 2) }}
                                                                @endif
                                                                <span class="d-block">{{ $pago->fecha_de_pago->format('d/m/Y') }}</span>
                                                                
                                                                @if(isset($pago->es_pago_excedente) && $pago->es_pago_excedente)
                                                                    <span class="text-primary d-block"><strong>* Pago con saldo excedente</strong></span>
                                                                @endif
                                                            </small>
                                                            
                                                            @if($pago->comprobante)
                                                                <a href="{{ route('pagos.comprobante', $pago->id) }}" 
                                                                  class="btn btn-sm btn-outline-info mt-1" 
                                                                  target="_blank">
                                                                    <i class="fas fa-eye me-1"></i> Ver comprobante
                                                                </a>
                                                            @endif
                                                        </div>
                                                    @endforeach
                                                @endif
                                            </div>
                                            
                                            @if($cuota->estado === 'parcial')
                                                <button class="btn btn-sm btn-warning mt-2 w-100 registrar-pago" 
                                                        data-cuota-id="{{ $cuota->id }}"
                                                        data-cuota-monto="{{ $saldoPendiente }}"
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#registrarPagoModal">
                                                    <i class="fas fa-money-bill-wave me-1"></i> Completar Pago
                                                </button>
                                            @endif
                                        @else
                                            <button class="btn btn-sm btn-dark mt-2 w-100 registrar-pago" 
                                                    data-cuota-id="{{ $cuota->id }}"
                                                    data-cuota-monto="{{ $cuota->monto }}"
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#registrarPagoModal">
                                                <i class="fas fa-money-bill-wave me-1"></i> Registrar Pago
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @else
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i> Seleccione un comprador para ver sus cuotas.
            </div>
        @endif
    </div>

    <!-- Incluir el modal de registro de pago como componente separado -->
    @include('pagos.components.registrar-pago-modal')

    <!-- Modal de Historial de Pagos -->
    <div class="modal fade" id="historialPagosModal" tabindex="-1" aria-labelledby="historialPagosModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="historialPagosModalLabel">Historial de Pagos</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="historialPagosBody">
                    <div class="text-center">
                        <div class="spinner-border" role="status">
                            <span class="visually-hidden">Cargando...</span>
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
            // Asegurar que todos los elementos de saldo a favor y excedentes se muestren en azul
            document.querySelectorAll('.saldo-favor, .aplicado-anterior, .fa-asterisk').forEach(el => {
                el.style.color = '#0d6efd';
                el.style.fontWeight = 'bold';
            });
            
            // Reemplazar todos los U\$D por U$D (quitar el slash)
            document.querySelectorAll('*').forEach(el => {
                if (el.childNodes && el.childNodes.length > 0) {
                    for (let i = 0; i < el.childNodes.length; i++) {
                        let node = el.childNodes[i];
                        if (node.nodeType === 3) { // Text node
                            let text = node.nodeValue;
                            if (text && text.includes('U\\$D')) {
                                node.nodeValue = text.replace(/U\\\$D/g, 'U$D');
                            }
                        }
                    }
                }
            });
            
            // Resaltar cuotas con saldo aplicado
            document.querySelectorAll('.cuota-con-saldo-aplicado').forEach(el => {
                el.style.borderColor = '#0d6efd';
            });
            
            // Referencia al formulario
            const formPago = document.getElementById('formPago');
            const errorComprobante = document.getElementById('errorComprobante');
            
            // Validación al enviar el formulario
            formPago.addEventListener('submit', function(e) {
                const sinComprobante = document.getElementById('sinComprobante').checked;
                const archivoComprobante = document.getElementById('archivoComprobante').files.length > 0;
                
                if (!sinComprobante && !archivoComprobante) {
                    e.preventDefault();
                    errorComprobante.classList.remove('d-none');
                    return false;
                } else {
                    errorComprobante.classList.add('d-none');
                }
            });
            
            // Autosubmit al cambiar el selector de comprador (si existe)
            if (document.getElementById('comprador')) {
                document.getElementById('comprador').addEventListener('change', function() {
                    document.getElementById('filtroForm').submit();
                });
            }
            
            // Configurar modal para registrar pago
            const registrarPagoBtns = document.querySelectorAll('.registrar-pago');
            registrarPagoBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    const cuotaId = this.getAttribute('data-cuota-id');
                    const cuotaMonto = this.getAttribute('data-cuota-monto');
                    
                    // Reset de formulario
                    formPago.reset();
                    errorComprobante.classList.add('d-none');
                    
                    // Establecer valores iniciales
                    document.getElementById('cuotaIdInput').value = cuotaId;
                    document.getElementById('montoPagado').value = cuotaMonto;
                    document.getElementById('montoUsd').value = cuotaMonto;
                    document.getElementById('montoAlerta').textContent = 'U$D ' + cuotaMonto;
                    
                    // Mostrar comprobante y ocultar alerta
                    document.getElementById('archivoComprobanteContainer').style.display = 'block';
                    document.getElementById('alertaSinComprobante').classList.add('d-none');
                    
                    // Resetear fecha al día actual
                    document.getElementById('fechaPago').value = "{{ now()->format('Y-m-d') }}";
                });
            });
            
            // Manejar checkbox "Sin comprobante"
            const sinComprobanteCheckbox = document.getElementById('sinComprobante');
            const archivoComprobanteContainer = document.getElementById('archivoComprobanteContainer');
            const alertaSinComprobante = document.getElementById('alertaSinComprobante');
            const archivoComprobante = document.getElementById('archivoComprobante');
            
            sinComprobanteCheckbox.addEventListener('change', function() {
                if (this.checked) {
                    archivoComprobanteContainer.style.display = 'none';
                    alertaSinComprobante.classList.remove('d-none');
                    archivoComprobante.value = '';
                    errorComprobante.classList.add('d-none');
                } else {
                    archivoComprobanteContainer.style.display = 'block';
                    alertaSinComprobante.classList.add('d-none');
                }
            });
            
            // Manejar conversión de moneda
            const montoPagadoInput = document.getElementById('montoPagado');
            const pagoDivisaCheckbox = document.getElementById('pagoDivisa');
            const montoUsdInput = document.getElementById('montoUsd');
            const tipoCambio = 1250; // 1250 pesos ARS = 1 USD
            
            pagoDivisaCheckbox.addEventListener('change', function() {
                calcularMontoUSD();
            });
            
            montoPagadoInput.addEventListener('input', function() {
                calcularMontoUSD();
                actualizarMontoAlerta();
            });
            
            function calcularMontoUSD() {
                if (pagoDivisaCheckbox.checked) {
                    // Si pago es en pesos, calcular equivalente en USD
                    const montoPesos = parseFloat(montoPagadoInput.value) || 0;
                    const montoUSD = montoPesos / tipoCambio;
                    montoUsdInput.value = montoUSD.toFixed(2);
                } else {
                    // Si pago es en USD, mostrar el mismo valor
                    montoUsdInput.value = montoPagadoInput.value;
                }
                
                actualizarMontoAlerta();
            }
            
            function actualizarMontoAlerta() {
                const montoUSD = montoUsdInput.value;
                document.getElementById('montoAlerta').textContent = 'U$D ' + montoUSD;
            }
        });
    </script>
</body>
</html> 