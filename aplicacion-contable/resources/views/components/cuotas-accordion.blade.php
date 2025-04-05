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
                            <p>Estado: 
                                @php
                                    $estadoClass = '';
                                    $estiloAdicional = '';
                                    
                                    // Determinar el estilo basado en el estado y la fecha
                                    if ($cuota->estado == 'pagada' || $cuota->estado == 'sin_comprobante') {
                                        // Pagado - VERDE
                                        $estadoClass = 'text-success';
                                        $estadoMostrado = 'Pagada';
                                    } elseif ($cuota->fecha_de_vencimiento < $inicioMes) {
                                        // Adeuda - ROJO con borde (mes anterior)
                                        $estadoClass = 'text-danger';
                                        $estiloAdicional = 'border:2px solid red; padding:2px 5px; display:inline-block;';
                                        $estadoMostrado = 'Adeuda';
                                    } elseif ($cuota->fecha_de_vencimiento <= $hoy) {
                                        // Vencido - ROJO (mismo mes, pasó la fecha)
                                        $estadoClass = 'text-danger';
                                        $estadoMostrado = 'Vencida';
                                    } elseif ($cuota->fecha_de_vencimiento <= $finMes) {
                                        // Pendiente - AMARILLO (mismo mes, antes de la fecha)
                                        $estadoClass = 'text-warning';
                                        $estadoMostrado = 'Pendiente';
                                    } else {
                                        // Pendiente futuro - Sin color especial
                                        $estadoClass = 'text-muted';
                                        $estadoMostrado = 'Pendiente';
                                    }
                                @endphp
                                <span class="{{ $estadoClass }}" style="{{ $estiloAdicional }}">{{ $estadoMostrado }}</span>
                            </p>
                            
                            @if($cuota->pagos->count() > 0)
                                <p>Fecha último pago: {{ $cuota->pagos->last()->fecha_de_pago->format('d/m/Y') }}</p>
                                
                                @php
                                    // SIEMPRE calculamos el total en USD para operaciones internas
                                    $totalPagadoUSD = $cuota->pagos->sum('monto_usd');
                                    
                                    // Determinamos si el último pago fue en pesos o dólares
                                    $ultimoPago = $cuota->pagos->last();
                                    $esPagoDivisa = $ultimoPago->pago_divisa == 1;
                                    
                                    // Preparamos la visualización según la moneda del último pago
                                    if ($esPagoDivisa) {
                                        // Si el último pago fue en pesos, mostrar todo en pesos
                                        $monedaTexto = "ARS";
                                        
                                        // Sumamos todos los pagos originales en pesos (o convertimos los que eran en USD)
                                        $pagosEnPesos = $cuota->pagos->filter(function($pago) {
                                            return $pago->pago_divisa == 1;
                                        });
                                        
                                        $pagosEnUSD = $cuota->pagos->filter(function($pago) {
                                            return $pago->pago_divisa == 0;
                                        });
                                        
                                        $totalEnPesos = $pagosEnPesos->sum('monto_pagado');
                                        
                                        // Convertimos los pagos en USD a pesos usando el tipo de cambio del último pago
                                        $tipoCambioUltimo = $ultimoPago->tipo_cambio;
                                        foreach ($pagosEnUSD as $pagoUSD) {
                                            $totalEnPesos += $pagoUSD->monto_usd * $tipoCambioUltimo;
                                        }
                                        
                                        $valorMostrado = $totalEnPesos;
                                    } else {
                                        // Si el último pago fue en USD, mostrar todo en USD
                                        $monedaTexto = "USD";
                                        $valorMostrado = $totalPagadoUSD;
                                    }
                                    
                                    // Saldo a favor SIEMPRE calculado en USD para consistencia
                                    $saldoFavor = max(0, $totalPagadoUSD - $cuota->monto);
                                @endphp
                                
                                <p>Total pagado: {{ $monedaTexto }} {{ number_format($valorMostrado, 2) }}</p>
                                
                                @if($saldoFavor > 0)
                                    <p>* Saldo a Favor USD {{ number_format($saldoFavor, 2) }}</p>
                                @endif
                            @endif
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
</div> 