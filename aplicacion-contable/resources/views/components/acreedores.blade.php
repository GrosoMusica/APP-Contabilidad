<div class="card" id="seccion-acreedores">
    <div class="card-header bg-success text-white">
        Acreedores
    </div>
    <div class="card-body">
        @if (session('success'))
            <div class="alert alert-success mb-3">
                {{ session('success') }}
            </div>
        @endif
        
        @if (session('error'))
            <div class="alert alert-danger mb-3">
                {{ session('error') }}
            </div>
        @endif
        
        @php
            // Buscar al administrador (ID 1)
            $admin = $acreedores->firstWhere('id', 1);
            $adminPorcentaje = 100;
            
            // Si el admin ya está asociado a esta financiación, obtener su porcentaje actual
            if ($admin) {
                foreach($admin->financiaciones as $fin) {
                    if($fin->id == $comprador->financiacion->id) {
                        $adminPorcentaje = $fin->pivot->porcentaje;
                        break;
                    }
                }
            }
        @endphp
        
        <!-- Tabla de acreedores -->
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Fecha Creación</th>
                        <th>Acreedor</th>
                        <th>Porcentaje</th>
                        <th>Debe</th>
                        <th>Haber</th>
                        <th>Saldo</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Admin siempre primero -->
                    <tr class="table-light">
                        <td>{{ $comprador->financiacion->created_at->format('d/m/Y') }}</td>
                        <td><strong>{{ strtoupper('ADMIN') }}</strong></td>
                        <td><strong>{{ $adminPorcentaje }}%</strong></td>
                        <td><!-- Pendiente --></td>
                        <td><!-- Pendiente --></td>
                        <td><!-- Pendiente --></td>
                    </tr>
                    
                    <!-- Otros acreedores -->
                    @foreach($acreedores->where('id', '!=', 1) as $acreedor)
                        @php
                            $porcentaje = 0;
                            foreach($acreedor->financiaciones as $fin) {
                                if($fin->id == $comprador->financiacion->id) {
                                    $porcentaje = $fin->pivot->porcentaje;
                                    break;
                                }
                            }
                        @endphp
                        <tr>
                            <td>{{ $acreedor->created_at->format('d/m/Y') }}</td>
                            <td>{{ strtoupper($acreedor->nombre) }}</td>
                            <td>{{ $porcentaje }}%</td>
                            <td><!-- Pendiente --></td>
                            <td><!-- Pendiente --></td>
                            <td><!-- Pendiente --></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
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
                <input type="hidden" name="redirect_to" value="#seccion-acreedores">
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
                        <input type="number" class="form-control" id="porcentaje" name="porcentaje" 
                               max="{{ $adminPorcentaje }}" min="1" required>
                        <small class="text-muted">Máximo disponible: {{ $adminPorcentaje }}%</small>
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

<!-- Script para hacer scroll a la sección de acreedores -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Verificar si hay un hash en la URL
        if (window.location.hash === '#seccion-acreedores') {
            // Hacer scroll a la sección de acreedores
            document.getElementById('seccion-acreedores').scrollIntoView();
        }
    });
</script> 