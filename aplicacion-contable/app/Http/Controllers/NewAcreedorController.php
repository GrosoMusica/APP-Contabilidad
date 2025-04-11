<?php

namespace App\Http\Controllers;

use App\Models\Acreedor;
use App\Models\Cuota;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;

class NewAcreedorController extends Controller
{
    /**
     * Mostrar listado de acreedores
     */
    public function index()
    {
        $acreedores = Acreedor::all();
        
        // Para cada acreedor, precargamos sus financiaciones y el estado de las cuotas del mes actual
        foreach ($acreedores as $acreedor) {
            // Obtener financiaciones del acreedor con los datos del comprador
            $financiaciones = DB::table('financiacion_acreedor as fa')
                ->join('financiaciones as f', 'fa.financiacion_id', '=', 'f.id')
                ->join('compradores as c', 'f.comprador_id', '=', 'c.id')
                ->where('fa.acreedor_id', $acreedor->id)
                ->select('fa.financiacion_id', 'fa.porcentaje', 'c.nombre as nombre_comprador')
                ->get();
            
            $acreedor->financiaciones = $financiaciones;
            
            // Mes actual
            $mesActual = now()->format('Y-m');
            
            // Para cada financiación, obtener la cuota del mes actual y sus pagos
            foreach ($financiaciones as $financiacion) {
                $cuota = Cuota::where('financiacion_id', $financiacion->financiacion_id)
                    ->where('fecha_de_vencimiento', 'like', $mesActual . '%')
                    ->first();
                    
                $financiacion->cuota = $cuota;
                
                if ($cuota) {
                    // Obtener pagos de la cuota
                    $pagos = DB::table('pagos')
                        ->where('cuota_id', $cuota->id)
                        ->get();
                    
                    // Calcular monto pagado
                    $montoPagado = $pagos->sum('monto_usd');
                    
                    // Determinar estado
                    if ($montoPagado >= $cuota->monto) {
                        $financiacion->estado = 'pagado';
                        $financiacion->monto_pagado = $montoPagado;
                    } elseif ($montoPagado > 0) {
                        $financiacion->estado = 'parcial';
                        $financiacion->monto_pagado = $montoPagado;
                    } else {
                        $financiacion->estado = 'pendiente';
                        $financiacion->monto_pagado = 0;
                    }
                    
                    // Calcular montos según porcentaje del acreedor
                    $financiacion->monto_total = $cuota->monto;
                    $financiacion->monto_porcentaje = ($cuota->monto * $financiacion->porcentaje) / 100;
                    $financiacion->monto_pagado_acreedor = ($financiacion->monto_pagado * $financiacion->porcentaje) / 100;
                    $financiacion->monto_pendiente_acreedor = $financiacion->monto_porcentaje - $financiacion->monto_pagado_acreedor;
                } else {
                    $financiacion->estado = 'sin_cuota';
                    $financiacion->monto_total = 0;
                    $financiacion->monto_porcentaje = 0;
                    $financiacion->monto_pagado = 0;
                    $financiacion->monto_pagado_acreedor = 0;
                    $financiacion->monto_pendiente_acreedor = 0;
                }
            }
        }
        
        return view('acreedores.index', compact('acreedores'));
    }

    /**
     * Guardar un nuevo acreedor
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'nombre' => 'required|string|max:255',
            ], [
                'nombre.required' => 'El nombre del acreedor es obligatorio.',
                'nombre.max' => 'El nombre no puede exceder los 255 caracteres.'
            ]);

            // Verificar si ya existe un acreedor con ese nombre
            $existingAcreedor = Acreedor::where('nombre', $request->nombre)->first();
            
            if ($existingAcreedor) {
                return redirect()->route('gestion.acreedores.index')
                                ->with('error', 'Ya existe un acreedor con este nombre.');
            }

            $acreedor = new Acreedor();
            $acreedor->nombre = $request->nombre;
            $acreedor->saldo = 0; // Por defecto siempre es 0
            $acreedor->save();

            return redirect()->route('gestion.acreedores.index')
                            ->with('success', 'Acreedor creado correctamente.');
        } catch (Exception $e) {
            return redirect()->route('gestion.acreedores.index')
                            ->with('error', 'Error al crear el acreedor: ' . $e->getMessage());
        }
    }

    /**
     * Mostrar información de un acreedor específico (para Ajax)
     */
    public function show(Acreedor $acreedor)
    {
        return response()->json($acreedor);
    }

    /**
     * Eliminar un acreedor
     */
    public function destroy(Acreedor $acreedor)
    {
        $acreedor->delete();
        return response()->json(['success' => true]);
    }

    /**
     * Obtener las financiaciones asociadas a un acreedor
     */
    public function getFinanciaciones(Acreedor $acreedor)
    {
        $financiaciones = DB::table('financiacion_acreedor as fa')
            ->join('financiaciones as f', 'fa.financiacion_id', '=', 'f.id')
            ->join('compradores as c', 'f.comprador_id', '=', 'c.id')
            ->where('fa.acreedor_id', $acreedor->id)
            ->select('fa.financiacion_id', 'fa.porcentaje', 'c.nombre as nombre_comprador')
            ->get();
            
        return response()->json($financiaciones);
    }

    /**
     * Obtiene la cuota del mes actual para una financiación específica
     */
    public function getCuotaMesActual($financiacionId)
    {
        $mesActual = now()->format('Y-m');
        
        $cuota = Cuota::where('financiacion_id', $financiacionId)
            ->where('fecha_de_vencimiento', 'like', $mesActual . '%')
            ->with('pagos')  // Incluir los pagos relacionados
            ->first();
            
        return response()->json($cuota);
    }
}