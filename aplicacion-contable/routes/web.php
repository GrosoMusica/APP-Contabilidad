<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\EntryController;
use App\Http\Controllers\CompradorController;
use App\Http\Controllers\AcreedorController;

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

// Ruta para la página de bienvenida
Route::get('/', function () {
    return view('welcome');
});

// Ruta para crear acreedores
Route::post('/acreedores', [AcreedorController::class, 'store'])->name('acreedores.store');
