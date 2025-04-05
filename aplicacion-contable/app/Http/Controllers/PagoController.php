<?php

namespace App\Http\Controllers;

use App\Models\Comprador;
use App\Models\Cuota;
use App\Models\Pago;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use App\Models\Lote;

class PagoController extends Controller
{
    public function index(Request $request)
    {
        // Obtener todos los compradores para el selector, ordenados por nombre
        $compradores = Comprador::orderBy('nombre')->get();
        
        // Comprador seleccionado (si existe)
        $compradorSeleccionado = null;
        $cuotas = collect();
        
        if ($request->has('comprador_id') && $request->comprador_id) {
            $compradorSeleccionado = Comprador::with(['lote', 'financiacion', 'financiacion.cuotas'])
                ->findOrFail($request->comprador_id);
            $cuotas = $compradorSeleccionado->financiacion->cuotas;
        } elseif ($request->has('dni') && $request->dni) {
            // Búsqueda por DNI
            $compradorSeleccionado = Comprador::with(['lote', 'financiacion', 'financiacion.cuotas'])
                ->where('dni', 'like', '%' . $request->dni . '%')
                ->first();
            
            if ($compradorSeleccionado) {
                $cuotas = $compradorSeleccionado->financiacion->cuotas;
            }
        } elseif ($request->has('email') && $request->email) {
            // Búsqueda por Email
            $compradorSeleccionado = Comprador::with(['lote', 'financiacion', 'financiacion.cuotas'])
                ->where('email', 'like', '%' . $request->email . '%')
                ->first();
            
            if ($compradorSeleccionado) {
                $cuotas = $compradorSeleccionado->financiacion->cuotas;
            }
        } elseif ($request->has('lote') && $request->lote) {
            // Búsqueda por Lote
            $lote = Lote::where('lote', 'like', '%' . $request->lote . '%')->first();
            
            if ($lote) {
                $compradorSeleccionado = Comprador::with(['lote', 'financiacion', 'financiacion.cuotas'])
                    ->where('lote_id', $lote->id)
                    ->first();
                
                if ($compradorSeleccionado) {
                    $cuotas = $compradorSeleccionado->financiacion->cuotas;
                }
            }
        }
        
        return view('pagos.index', compact('compradores', 'compradorSeleccionado', 'cuotas'));
    }
    
    public function registrarPago(Request $request)
    {
        $request->validate([
            'cuota_id' => 'required|exists:cuotas,id',
            'fecha_de_pago' => 'required|date',
            'monto_pagado' => 'required|numeric|min:0',
            'archivo_comprobante' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'tipo_cambio' => 'required_if:pago_divisa,on|nullable|numeric|min:1',
            'monto_usd' => 'required_if:pago_divisa,on|nullable|numeric|min:0',
        ]);
        
        // Obtener la cuota
        $cuota = Cuota::findOrFail($request->cuota_id);
        
        // Obtener la financiación asociada a la cuota
        $financiacion = $cuota->financiacion;
        
        // Obtener los acreedores de esta financiación con sus porcentajes
        $acreedores = $financiacion->acreedores;
        
        // Iniciar una transacción de base de datos
        DB::beginTransaction();
        
        try {
            // Determinar si es un pago en divisa (pesos)
            $pagoDivisa = $request->has('pago_divisa');
            
            // Crear una nueva instancia del modelo Pago
            $pago = new Pago();
            
            // Calcular monto en USD correctamente
            if ($pagoDivisa) {
                // Si el pago es en pesos, usar tipo_cambio para convertir a dólares
                $montoUsd = $request->monto_pagado / $request->tipo_cambio;
                
                // Almacenar el monto original en pesos y el convertido en USD
                $pago->monto_pagado = $request->monto_pagado; // Valor original en pesos
                $pago->monto_usd = $montoUsd; // Valor convertido a dólares
            } else {
                // Si el pago es en dólares, usar el valor directamente
                $montoUsd = $request->monto_pagado;
                
                // Ambos campos tienen el mismo valor (en dólares)
                $pago->monto_pagado = $montoUsd;
                $pago->monto_usd = $montoUsd;
            }
            
            // Establecer los valores relacionados con la divisa
            $pago->pago_divisa = $pagoDivisa ? 1 : 0;
            $pago->tipo_cambio = $pagoDivisa ? $request->tipo_cambio : null;
            
            // Calcular monto total pagado hasta ahora y saldo pendiente
            $pagosPrevios = Pago::where('cuota_id', $cuota->id)->sum('monto_usd');
            $montoPendienteEnCuota = $cuota->monto - $pagosPrevios;
            
            // Calcular excedente si existe
            $excedente = max(0, $montoUsd - $montoPendienteEnCuota);
            $montoEfectivoCuota = min($montoUsd, $montoPendienteEnCuota);
            
            // Determinar estado de la cuota según el pago
            $estadoCuota = ($montoUsd >= $montoPendienteEnCuota) ? 'pagada' : 'parcial';
            
            // Procesar archivo de comprobante
            $comprobante = null;
            if ($request->hasFile('archivo_comprobante') && !$request->has('sin_comprobante')) {
                $archivo = $request->file('archivo_comprobante');
                $nombreArchivo = time() . '_cuota' . $cuota->numero_de_cuota . '_' . $archivo->getClientOriginalName();
                $rutaCarpeta = 'COMPROBANTES/' . $financiacion->id;
                Storage::makeDirectory($rutaCarpeta, 0775, true);
                $archivo->storeAs($rutaCarpeta, $nombreArchivo);
                $comprobante = $rutaCarpeta . '/' . $nombreArchivo;
            }
            
            // 1. Actualizar el estado de la cuota
            $cuota->estado = $estadoCuota;
            $cuota->updated_at = $request->fecha_de_pago;
            $cuota->save();
            
            // Obtener fecha seleccionada o usar today
            $fechaSeleccionada = now();
            if ($request->fecha_de_pago) {
                $fechaSeleccionada = Carbon::parse($request->fecha_de_pago);
            }
            
            // 2. Registrar el pago principal
            $pago->cuota_id = $cuota->id;
            $pago->acreedor_id = 1; // Admin por defecto
            $pago->comprobante = $comprobante;
            $pago->sin_comprobante = $request->has('sin_comprobante') ? 1 : 0;
            $pago->fecha_de_pago = $fechaSeleccionada;
            $pago->saldo_pendiente = max(0, $montoPendienteEnCuota - $montoEfectivoCuota);
            $pago->created_at = $fechaSeleccionada;
            $pago->updated_at = $fechaSeleccionada;
            $pago->save();
            
            // 3. Registrar las distribuciones a los acreedores
            foreach ($acreedores as $acreedor) {
                $porcentaje = $acreedor->pivot->porcentaje;
                $montoDistribuido = ($montoEfectivoCuota * $porcentaje) / 100;
                
                DB::table('distribucion_pagos')->insert([
                    'pago_id' => $pago->id,
                    'acreedor_id' => $acreedor->id,
                    'porcentaje' => $porcentaje,
                    'monto_distribuido' => $pagoDivisa ? $montoDistribuido * $request->tipo_cambio : $montoDistribuido,
                    'monto_usd' => $montoDistribuido,
                    'excedente' => 0,
                    'created_at' => $fechaSeleccionada,
                    'updated_at' => $fechaSeleccionada
                ]);
                
                // Actualizar el saldo del acreedor
                $acreedor->saldo += $montoDistribuido;
                $acreedor->save();
            }
            
            // 4. Si hay excedente, aplicarlo a la siguiente cuota
            if ($excedente > 0) {
                // Buscar la siguiente cuota pendiente
                $siguienteCuota = Cuota::where('financiacion_id', $financiacion->id)
                    ->where('numero_de_cuota', '>', $cuota->numero_de_cuota)
                    ->where('estado', 'pendiente')
                    ->orderBy('numero_de_cuota', 'asc')
                    ->first();
                
                if ($siguienteCuota) {
                    // Marcar la cuota como parcial o pagada según el excedente
                    $saldoPendienteSiguiente = $siguienteCuota->monto;
                    $estadoSiguiente = ($excedente >= $saldoPendienteSiguiente) ? 'pagada' : 'parcial';
                    
                    $siguienteCuota->estado = $estadoSiguiente;
                    $siguienteCuota->updated_at = $fechaSeleccionada;
                    $siguienteCuota->save();
                    
                    // Registrar un pago con el excedente para la siguiente cuota
                    $pagoExcedente = new Pago();
                    $pagoExcedente->cuota_id = $siguienteCuota->id;
                    $pagoExcedente->acreedor_id = 1; // Admin por defecto
                    $pagoExcedente->monto_usd = $excedente;
                    $pagoExcedente->monto_pagado = $pagoDivisa ? $excedente * $request->tipo_cambio : $excedente;
                    $pagoExcedente->comprobante = $comprobante;
                    $pagoExcedente->sin_comprobante = $request->has('sin_comprobante') ? 1 : 0;
                    $pagoExcedente->fecha_de_pago = $fechaSeleccionada;
                    $pagoExcedente->pago_divisa = $pagoDivisa ? 1 : 0;
                    $pagoExcedente->tipo_cambio = $pagoDivisa ? $request->tipo_cambio : null;
                    $pagoExcedente->saldo_pendiente = max(0, $saldoPendienteSiguiente - $excedente);
                    $pagoExcedente->created_at = $fechaSeleccionada;
                    $pagoExcedente->updated_at = $fechaSeleccionada;
                    $pagoExcedente->save();
                    
                    // Distribuir el excedente entre los acreedores
                    foreach ($acreedores as $acreedor) {
                        $porcentaje = $acreedor->pivot->porcentaje;
                        $montoDistribuidoExcedente = ($excedente * $porcentaje) / 100;
                        
                        DB::table('distribucion_pagos')->insert([
                            'pago_id' => $pagoExcedente->id,
                            'acreedor_id' => $acreedor->id,
                            'porcentaje' => $porcentaje,
                            'monto_distribuido' => $pagoDivisa ? $montoDistribuidoExcedente * $request->tipo_cambio : $montoDistribuidoExcedente,
                            'monto_usd' => $montoDistribuidoExcedente,
                            'excedente' => 0,
                            'created_at' => $fechaSeleccionada,
                            'updated_at' => $fechaSeleccionada
                        ]);
                        
                        // Actualizar el saldo del acreedor
                        $acreedor->saldo += $montoDistribuidoExcedente;
                        $acreedor->save();
                    }
                }
            }
            
            // Construir mensaje de éxito
            $mensaje = "Pago registrado correctamente.";
            
            if ($estadoCuota === 'pagada') {
                $mensaje .= " Cuota #" . $cuota->numero_de_cuota . " marcada como pagada.";
            } else {
                $mensaje .= " Cuota #" . $cuota->numero_de_cuota . " parcialmente pagada.";
            }
            
            if ($excedente > 0) {
                if (isset($siguienteCuota)) {
                    $mensaje .= " Excedente de U\$D " . number_format($excedente, 2) . " aplicado a cuota #" . $siguienteCuota->numero_de_cuota . ".";
                } else {
                    $mensaje .= " Excedente de U\$D " . number_format($excedente, 2) . " registrado.";
                }
            }
            
            // Confirmar la transacción
            DB::commit();
            
            // Redireccionar con el comprador seleccionado
            $compradorId = $financiacion->comprador_id ?? null;
            return redirect()->route('pagos.index', ['comprador_id' => $compradorId])->with('success', $mensaje);
            
        } catch (\Exception $e) {
            // Revertir la transacción en caso de error
            DB::rollBack();
            
            // Registrar el error para debugging
            \Log::error('Error al registrar pago: ' . $e->getMessage());
            
            return redirect()->back()->with('error', 'Error al registrar el pago: ' . $e->getMessage());
        }
    }
    
    /**
     * Mostrar comprobante (controlado y protegido)
     */
    public function mostrarComprobante($id)
    {
        // Buscar el pago
        $pago = Pago::findOrFail($id);
        
        // Verificar si hay comprobante
        if (!$pago->comprobante || $pago->sin_comprobante) {
            return abort(404, 'No hay comprobante disponible para este pago.');
        }
        
        // Verificar que el archivo existe
        if (!Storage::exists($pago->comprobante)) {
            return abort(404, 'El archivo de comprobante no fue encontrado.');
        }
        
        // Obtener el tipo de contenido (MIME type)
        $path = Storage::path($pago->comprobante);
        $mime = File::mimeType($path);
        
        // Servir el archivo como respuesta
        return response()->file($path, [
            'Content-Type' => $mime,
        ]);
    }

} 