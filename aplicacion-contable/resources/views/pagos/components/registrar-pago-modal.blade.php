<!-- Modal para Registrar Pago -->
<div class="modal fade" id="registrarPagoModal" tabindex="-1" aria-labelledby="registrarPagoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title" id="registrarPagoModalLabel">Registrar Pago de Cuota</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('cuotas.pagar') }}" method="POST" enctype="multipart/form-data" id="formPago">
                @csrf
                <input type="hidden" name="cuota_id" id="cuotaIdInput" value="">
                <input type="hidden" name="acreedor_id" value="1">
                <div class="modal-body">
                    <div class="row">
                        <!-- Columna izquierda -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="fechaPago" class="form-label">Fecha de Pago</label>
                                <input type="date" class="form-control" id="fechaPago" name="fecha_de_pago" value="{{ now()->format('Y-m-d') }}">
                            </div>
                            
                            <div class="mb-3">
                                <label for="montoPagado" class="form-label">Monto Pagado</label>
                                <div class="input-group">
                                    <input type="number" step="0.01" class="form-control" id="montoPagado" name="monto_pagado" required>
                                    <div class="input-group-text">
                                        <div class="form-check form-switch mb-0">
                                            <input class="form-check-input" type="checkbox" id="pagoDivisa" name="pago_divisa">
                                            <label class="form-check-label" for="pagoDivisa">Pago en pesos</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="montoUsd" class="form-label">Monto USD</label>
                                <div class="input-group">
                                    <span class="input-group-text">U$D</span>
                                    <input type="number" step="0.01" class="form-control" id="montoUsd" name="monto_usd" readonly>
                                </div>
                                <input type="hidden" id="tipoCambio" name="tipo_cambio" value="1250">
                                <small class="text-muted">Tipo de cambio: $1,250 ARS = 1 USD</small>
                            </div>
                        </div>
                        
                        <!-- Columna derecha -->
                        <div class="col-md-6">
                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="sinComprobante" name="sin_comprobante">
                                <label class="form-check-label" for="sinComprobante">Sin comprobante</label>
                            </div>
                            
                            <div class="alert alert-warning mt-2 d-none" id="alertaSinComprobante">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                Usted acepta incluir el siguiente pago de <span id="montoAlerta"></span> sin adjuntar comprobante.
                            </div>
                            
                            <div class="mb-3" id="archivoComprobanteContainer">
                                <label for="archivoComprobante" class="form-label">Subir Comprobante <span class="text-danger">*</span></label>
                                <input type="file" class="form-control" id="archivoComprobante" name="archivo_comprobante">
                                <small class="text-muted">Formatos aceptados: JPG, PNG, PDF. Máximo 2MB.</small>
                            </div>
                            
                            <div id="errorComprobante" class="alert alert-danger mt-2 d-none">
                                Debe subir un comprobante o marcar la casilla "Sin comprobante".
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success" id="btnRegistrarPago">
                        <i class="fas fa-money-bill-wave me-1"></i> Registrar Pago
                    </button>
                </div>
            </form>
        </div>
    </div>
</div> 