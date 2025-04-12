<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Reporte de Acreedores' }}</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            margin: 0;
            padding: 20px;
            font-size: 12px;
        }
        
        .header {
            position: fixed;
            top: 0;
            width: 100%;
            text-align: center;
            padding: 10px 0;
            border-bottom: 1px solid #ddd;
        }
        
        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
            padding: 10px 0;
            border-top: 1px solid #ddd;
            font-size: 10px;
            color: #666;
        }
        
        .content {
            margin-top: 70px;
            margin-bottom: 50px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th, td {
            text-align: left;
            padding: 6px;
            border: 1px solid #ddd;
        }
        
        th {
            background-color: #f0f0f0;
            font-weight: bold;
        }
        
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        
        .text-center {
            text-align: center;
        }
        
        .text-right {
            text-align: right;
        }
        
        .text-green {
            color: #28a745;
        }
        
        .text-yellow {
            color: #ffc107;
        }
        
        .text-red {
            color: #dc3545;
        }
        
        .text-gray {
            color: #6c757d;
        }
        
        .small {
            font-size: 10px;
        }
        
        .page-break {
            page-break-after: always;
        }
        
        .title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 15px;
            color: #333;
        }
        
        .subtitle {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 10px;
            color: #555;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1 style="margin: 0; font-size: 16px;">Aplicación Contable</h1>
        <div>{{ $subtitle ?? 'Reporte de Acreedores' }}</div>
    </div>
    
    <div class="content">
        @yield('content')
    </div>
    
    <div class="footer">
        <div>Generado el {{ $fechaGeneracion }} | Página <span class="pagenum"></span></div>
        <div>Aplicación Contable - Todos los derechos reservados</div>
    </div>
</body>
</html> 