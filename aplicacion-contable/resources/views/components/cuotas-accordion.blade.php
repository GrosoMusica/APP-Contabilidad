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
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
</div> 