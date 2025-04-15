<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

use App\Http\Controllers\EntryController;
use App\Http\Controllers\CompradorController;
use App\Http\Controllers\AcreedorController;
use App\Http\Controllers\LoteController;
use App\Http\Controllers\PagoController;
use App\Http\Controllers\CsvImportController;
use App\Http\Controllers\DiagnosticoController;
use App\Http\Controllers\NewAcreedorController;
use App\Http\Controllers\PagosAcreedorController;
use App\Http\Controllers\ComprobanteController;
use App\Http\Controllers\FinanciacionController;

// Ruta para crear entradas
Route::get('/create-entries', function () {
    return view('create_entries');
})->name('entries.create');

Route::post('/create-entries', [EntryController::class, 'store'])->name('entries.store');

// Rutas para compradores
Route::get('/compradores', [CompradorController::class, 'index'])->name('compradores.index');
Route::get('/comprador/{id}', [EntryController::class, 'show'])->name('comprador.show');
Route::get('/comprador/{id}/edit', [CompradorController::class, 'edit'])->name('comprador.edit');
Route::put('/comprador/{id}', [CompradorController::class, 'update'])->name('comprador.update');
Route::patch('/comprador/{id}/toggle-judicializado', [CompradorController::class, 'toggleJudicializado'])->name('comprador.toggleJudicializado');

// Rutas para lotes
Route::get('/lotes', [LoteController::class, 'index'])->name('lotes.index');
Route::get('/lotes/{id}', [LoteController::class, 'show'])->name('lotes.show');

// Ruta para la página de bienvenida
Route::get('/', function () {
    return view('welcome');
});

// Ruta para crear acreedores
Route::post('/acreedores', [AcreedorController::class, 'store'])->name('acreedores.store');

// Rutas para Pagos
Route::get('/pagos', [PagoController::class, 'index'])->name('pagos.index');
Route::post('/cuotas/pagar', [PagoController::class, 'registrarPago'])->name('cuotas.pagar');
Route::post('/pagos/registrar', [App\Http\Controllers\PagoController::class, 'registrarPago'])->name('pagos.registrar');

// Ruta protegida para ver comprobantes
Route::get('/pagos/comprobante/{id}', [App\Http\Controllers\PagoController::class, 'mostrarComprobante'])
    ->name('pagos.comprobante');

// Rutas para importación CSV (usando el nuevo controlador)
Route::post('/csv/import', [CsvImportController::class, 'import'])->name('entries.import');
Route::get('/csv/template', [CsvImportController::class, 'downloadTemplate'])->name('entries.template');

// Rutas para diagnóstico y corrección de relaciones
Route::get('/diagnosticar-relaciones', [EntryController::class, 'diagnosticarRelaciones'])->name('diagnosticar.relaciones');
Route::post('/corregir-relaciones', [EntryController::class, 'corregirRelaciones'])->name('corregir.relaciones');
Route::get('/verificar-entrada/{id}', [EntryController::class, 'verificarEntrada'])->name('verificar.entrada');

Route::get('/informes', [App\Http\Controllers\InformeController::class, 'index'])->name('informes.index');

// Ruta para diagnóstico
Route::get('/diagnostico/cobranzas', [DiagnosticoController::class, 'cobranzas'])->name('diagnostico.cobranzas');

// Rutas para la nueva gestión de acreedores
Route::get('/gestion-acreedores', [NewAcreedorController::class, 'index'])->name('gestion.acreedores.index');
Route::post('/gestion-acreedores', [NewAcreedorController::class, 'store'])->name('gestion.acreedores.store');
Route::get('/gestion-acreedores/{acreedor}', [NewAcreedorController::class, 'show'])->name('gestion.acreedores.show');
Route::delete('/gestion-acreedores/{acreedor}', [NewAcreedorController::class, 'destroy'])->name('gestion.acreedores.destroy');

// Ruta para obtener financiaciones de un acreedor
Route::get('/gestion-acreedores/{acreedor}/financiaciones', [NewAcreedorController::class, 'getFinanciaciones'])
    ->name('gestion.acreedores.financiaciones');

Route::get('acreedores/export-pdf/{tipo?}', 'AcreedorController@exportPDF')->name('acreedores.export-pdf');

// Ruta para exportar distribución de ingresos (con parámetro opcional de mes)
Route::get('/acreedores/{acreedor}/distribucion-ingresos/{mes?}', [AcreedorController::class, 'exportDistribucion'])
    ->name('acreedores.export-distribucion');

// Rutas para pagos a acreedores
Route::get('/gestion/acreedores/pagos', [PagosAcreedorController::class, 'index'])->name('gestion.acreedores.pagos');

// Rutas para los comprobantes
Route::get('comprobantes/ver', [ComprobanteController::class, 'ver'])->name('comprobantes.ver');
Route::get('comprobantes/descargar', [ComprobanteController::class, 'descargar'])->name('comprobantes.descargar');

// Endpoint para obtener acreedores asociados a una financiación
Route::get('/api/financiaciones/{financiacion}/acreedores', function($financiacionId) {
    // Obtener los acreedores relacionados con esta financiación
    // Buscar en la tabla con el nombre correcto (singular)
    $acreedorIds = DB::table('financiacion_acreedor')
        ->where('financiacion_id', $financiacionId)
        ->pluck('acreedor_id');
        
    $acreedores = App\Models\Acreedor::whereIn('id', $acreedorIds)->get();
    
    // Asegurarnos que el Admin (id=1) siempre esté incluido
    $adminIncluido = $acreedores->contains('id', 1);
    if (!$adminIncluido) {
        $admin = App\Models\Acreedor::find(1);
        if ($admin) {
            $acreedores->prepend($admin);
        }
    }
    
    return $acreedores;
});

// Actualizar la ruta para INCREMENTAR el saldo al hacer liquidación
Route::post('/api/acreedores/{id}/actualizar-saldo', function($id, Request $request) {
    $acreedor = App\Models\Acreedor::findOrFail($id);
    
    // Validar que el monto sea numérico
    $request->validate([
        'monto' => 'required|numeric',
        'comprobante' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        'sin_comprobante' => 'nullable'
    ]);
    
    $monto = $request->monto;
    
    // Incrementar el saldo (no decrementar)
    $acreedor->saldo += $monto;
    $acreedor->save();
    
    // Guardar comprobante si existe
    $rutaComprobante = null;
    $sinComprobante = $request->has('sin_comprobante');
    
    if ($request->hasFile('comprobante') && !$sinComprobante) {
        $file = $request->file('comprobante');
        $nombreArchivo = "Liquidacion-" . date('Y-m-d-His') . "." . $file->extension();
        $rutaComprobante = $file->storeAs(
            "LIQUIDACIONES/{$acreedor->id}-{$acreedor->nombre}",
            $nombreArchivo,
            'public'
        );
    }
    
    // Crear registro de liquidación
    App\Models\Liquidacion::create([
        'acreedor_id' => $acreedor->id,
        'monto' => $monto,
        'fecha' => $request->fecha_liquidacion ?? now()->format('Y-m-d'),
        'comprobante' => $rutaComprobante,
        'sin_comprobante' => $sinComprobante,
        'usuario_id' => auth()->id() ?? 1
    ]);
    
    return redirect()->back()->with('success', "Liquidación de $" . number_format($monto, 2) . " realizada con éxito a {$acreedor->nombre}");
})->name('api.acreedores.actualizar-saldo');

// Ruta para la página de morosos
Route::get('/morosos', [FinanciacionController::class, 'morosos'])->name('morosos');

// Ruta para la página de próximos a finalizar
Route::get('/proximos-a-finalizar', [App\Http\Controllers\FinanciacionController::class, 'proximosAFinalizar'])->name('proximos.finalizar');
