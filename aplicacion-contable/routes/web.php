<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\EntryController;
use App\Http\Controllers\CompradorController;
use App\Http\Controllers\AcreedorController;
use App\Http\Controllers\LoteController;
use App\Http\Controllers\PagoController;

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
    ->name('pagos.comprobante')
    ->middleware('auth'); // Asegúrate de tener middleware de autenticación configurado
