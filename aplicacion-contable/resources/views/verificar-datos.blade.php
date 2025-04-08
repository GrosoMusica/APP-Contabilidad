<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificación de Datos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h1>Verificación de Datos del Controlador</h1>
        
        @if(isset($datos['error']))
            <div class="alert alert-danger">
                <h4>Error encontrado:</h4>
                <p>{{ $datos['error'] }}</p>
                <pre class="mt-3 bg-light p-3">{{ $datos['trace'] }}</pre>
            </div>
        @else
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h2>Datos obtenidos del controlador</h2>
                </div>
                <div class="card-body">
                    <p><strong>Tipo de datos devueltos:</strong> {{ $datos['resultado']['tipo'] }}</p>
                    
                    @if(count($datos['resultado']['claves_disponibles']) > 0)
                        <h4 class="mt-4">Claves disponibles:</h4>
                        <ul class="list-group mb-4">
                            @foreach($datos['resultado']['claves_disponibles'] as $clave)
                                <li class="list-group-item">{{ $clave }}</li>
                            @endforeach
                        </ul>
                        
                        <h4>Datos completos:</h4>
                        <div class="accordion" id="datosAccordion">
                            @foreach($datos['resultado']['claves_disponibles'] as $clave)
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="heading{{ $loop->index }}">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" 
                                            data-bs-target="#collapse{{ $loop->index }}" aria-expanded="false">
                                            {{ $clave }}
                                            
                                            @if(is_array($datos['resultado']['datos_completos'][$clave]) || is_object($datos['resultado']['datos_completos'][$clave]))
                                                <span class="badge bg-info ms-2">
                                                    @if(is_countable($datos['resultado']['datos_completos'][$clave]))
                                                        {{ count($datos['resultado']['datos_completos'][$clave]) }} elementos
                                                    @else
                                                        Objeto
                                                    @endif
                                                </span>
                                            @endif
                                        </button>
                                    </h2>
                                    <div id="collapse{{ $loop->index }}" class="accordion-collapse collapse" 
                                        aria-labelledby="heading{{ $loop->index }}" data-bs-parent="#datosAccordion">
                                        <div class="accordion-body">
                                            <pre class="bg-light p-3">{{ print_r($datos['resultado']['datos_completos'][$clave], true) }}</pre>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="alert alert-warning">
                            No hay claves disponibles en el resultado.
                        </div>
                    @endif
                </div>
            </div>
        @endif
        
        <a href="{{ route('informes.index') }}" class="btn btn-primary">Volver a Informes</a>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 