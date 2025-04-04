<div class="card">
    <div class="card-header bg-success text-white">
        Acreedores
    </div>
    <div class="card-body">
        <ul class="list-group">
            @foreach($acreedores as $acreedor)
                <li class="list-group-item">
                    <strong>Nombre:</strong> {{ $acreedor->nombre }}
                    <p><strong>Monto Adeudado:</strong> U$D {{ number_format($acreedor->monto_adeudado ?? 0, 2) }}</p>
                    @php
                        $porcentaje = 100; // Valor por defecto
                        foreach($acreedor->financiaciones as $fin) {
                            if($fin->id == $comprador->financiacion->id) {
                                $porcentaje = $fin->pivot->porcentaje;
                                break;
                            }
                        }
                    @endphp
                    <p><strong>Porcentaje:</strong> {{ $porcentaje }}%</p>
                    <p><strong>Financiación:</strong> {{ $acreedor->financiaciones->first()->descripcion ?? 'N/A' }}</p>
                </li>
            @endforeach
        </ul>
        <button class="btn btn-primary mt-3" data-bs-toggle="modal" data-bs-target="#addAcreedorModal">Agregar Acreedor</button>
    </div>
</div>

<!-- Modal para agregar acreedor -->
<div class="modal fade" id="addAcreedorModal" tabindex="-1" aria-labelledby="addAcreedorModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('acreedores.store') }}" method="POST">
                @csrf
                <input type="hidden" name="financiacion_id" value="{{ $comprador->financiacion->id }}">
                <div class="modal-header">
                    <h5 class="modal-title" id="addAcreedorModalLabel">Agregar Acreedor</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="nombre" class="form-label">Nombre del Acreedor</label>
                        <input type="text" class="form-control" id="nombre" name="nombre" required>
                    </div>
                    <div class="mb-3">
                        <label for="porcentaje" class="form-label">Porcentaje</label>
                        <input type="number" class="form-control" id="porcentaje" name="porcentaje" max="100" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div> 