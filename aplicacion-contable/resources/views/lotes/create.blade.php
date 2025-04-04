@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col-md-8">
            <h2 class="text-success">Crear Nuevo Lote</h2>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('lotes.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i> Volver al Listado
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0">Información del Lote</h5>
        </div>
        <div class="card-body" style="background-color: #f0fff0;">
            <form action="{{ route('lotes.store') }}" method="POST">
                @csrf
                
                <div class="row mb-3">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="nombre" class="form-label">Número de Lote <span class="text-danger">*</span></label>
                            <input type="text" name="nombre" id="nombre" class="form-control @error('nombre') is-invalid @enderror" value="{{ old('nombre') }}" required>
                            @error('nombre')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="manzana" class="form-label">Manzana <span class="text-danger">*</span></label>
                            <input type="text" name="manzana" id="manzana" class="form-control @error('manzana') is-invalid @enderror" value="{{ old('manzana') }}" required>
                            @error('manzana')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="loteo" class="form-label">Loteo <span class="text-danger">*</span></label>
                            <input type="text" name="loteo" id="loteo" class="form-control @error('loteo') is-invalid @enderror" value="{{ old('loteo') }}" required>
                            @error('loteo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                
                <div class="row mt-4">
                    <div class="col-12 text-end">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save me-1"></i> Guardar Lote
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection 