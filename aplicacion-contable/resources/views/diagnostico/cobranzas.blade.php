<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnóstico de Cobranzas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h1>Diagnóstico de Cobranzas</h1>
        <div class="alert alert-info">
            <p><strong>Fecha y hora:</strong> {{ $diagnostico['fecha_actual'] }}</p>
            <p><strong>Periodo consultado:</strong> {{ $diagnostico['mes_consultado'] }}/{{ $diagnostico['ano_consultado'] }}</p>
        </div>
        
        @if(isset($diagnostico['totales']))
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h2>Totales calculados</h2>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="alert alert-primary">
                                <h3>Total de cuotas</h3>
                                <h4>${{ number_format($diagnostico['totales']['total_cuotas'], 2) }}</h4>
                                <p>{{ $diagnostico['totales']['cantidad_cuotas'] }} cuotas</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="alert alert-success">
                                <h3>Total pagado</h3>
                                <h4>${{ number_format($diagnostico['totales']['total_pagado'], 2) }}</h4>
                                <p>{{ $diagnostico['totales']['cantidad_pagos'] }} pagos</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="alert alert-danger">
                                <h3>Saldo pendiente</h3>
                                <h4>${{ number_format($diagnostico['totales']['saldo_pendiente'], 2) }}</h4>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="alert alert-info">
                                <h3>% Cobrado</h3>
                                <h4>{{ $diagnostico['totales']['total_cuotas'] > 0 ? number_format(($diagnostico['totales']['total_pagado'] / $diagnostico['totales']['total_cuotas']) * 100, 2) : 0 }}%</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
        
        <h2>Pasos de diagnóstico</h2>
        
        @foreach($diagnostico['pasos'] as $paso)
            <div class="card mb-4">
                <div class="card-header">
                    <h3>{{ $paso['paso'] }}</h3>
                </div>
                <div class="card-body">
                    <h4>Consulta SQL:</h4>
                    <pre class="bg-light p-3">{{ $paso['consulta'] }}</pre>
                    
                    <h4>Resultados:</h4>
                    @if(isset($paso['resultado']['error']))
                        <div class="alert alert-danger">
                            <strong>Error:</strong> {{ $paso['resultado']['error'] }}
                        </div>
                    @else
                        <p><strong>Cantidad de resultados:</strong> {{ count($paso['resultado']) }}</p>
                        
                        @if(count($paso['resultado']) > 0)
                            <button class="btn btn-primary mb-3" type="button" data-bs-toggle="collapse" 
                                data-bs-target="#resultados{{ $loop->index }}">
                                Ver/Ocultar resultados
                            </button>
                            
                            <div class="collapse" id="resultados{{ $loop->index }}">
                                <div class="table-responsive">
                                    <table class="table table-sm table-striped table-bordered">
                                        <thead>
                                            <tr>
                                                @foreach((array)$paso['resultado'][0] as $columna => $valor)
                                                    <th>{{ $columna }}</th>
                                                @endforeach
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($paso['resultado'] as $resultado)
                                                <tr>
                                                    @foreach((array)$resultado as $valor)
                                                        <td>{{ $valor }}</td>
                                                    @endforeach
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @else
                            <div class="alert alert-warning">
                                No se encontraron resultados.
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        @endforeach
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 