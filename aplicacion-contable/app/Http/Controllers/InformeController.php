<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cuota;
use App\Models\Pago;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InformeController extends Controller
{
    public function index(Request $request)
    {
        Log::info("Método index de InformeController iniciado");
        
        try {
            // Obtener mes y año de los parámetros de la solicitud o usar valores actuales
            $mesConsultado = $request->input('mes', Carbon::now()->month);
            $anoConsultado = $request->input('ano', Carbon::now()->year);
            
            // Crear diagnóstico básico que siempre debe existir
            $diagnostico = [
                'fecha_actual' => Carbon::now()->format('Y-m-d H:i:s'),
                'mes_consultado' => $mesConsultado,
                'ano_consultado' => $anoConsultado,
                'pasos' => []
            ];
            
            Log::info("Diagnóstico básico creado", $diagnostico);
            
            // Fecha para cálculos basada en el mes y año consultados
            $fechaConsulta = Carbon::createFromDate($anoConsultado, $mesConsultado, 1);
            $primerDiaMes = $fechaConsulta->copy()->startOfMonth();
            $ultimoDiaMes = $fechaConsulta->copy()->endOfMonth();
            
            // Array para almacenar los resultados del diagnóstico
            $diagnostico['pasos'] = []; // Inicializar el array pasos para evitar errores
            
            // Paso 1: Obtener todas las cuotas del mes consultado
            $diagnostico['pasos'][0] = [
                'paso' => '1. Cuotas en el mes consultado',
                'sql' => "SELECT c.*, c.monto as monto FROM cuotas c WHERE fecha_de_vencimiento BETWEEN '$primerDiaMes' AND '$ultimoDiaMes'",
                'resultado' => DB::select("SELECT c.*, c.monto as monto FROM cuotas c WHERE fecha_de_vencimiento BETWEEN '$primerDiaMes' AND '$ultimoDiaMes'")
            ];
            
            // Paso 2: JOIN con financiaciones y compradores para obtener información completa
            $diagnostico['pasos'][1] = [
                'paso' => '2. JOIN con financiaciones y compradores',
                'sql' => "SELECT c.id as cuota_id, c.monto, c.estado, c.financiacion_id, c.fecha_de_vencimiento, f.comprador_id, comp.nombre, comp.email 
                         FROM cuotas c 
                         JOIN financiaciones f ON c.financiacion_id = f.id 
                         JOIN compradores comp ON f.comprador_id = comp.id 
                         WHERE c.fecha_de_vencimiento BETWEEN '$primerDiaMes' AND '$ultimoDiaMes'",
                'resultado' => DB::select("SELECT c.id as cuota_id, c.monto, c.estado, c.financiacion_id, c.fecha_de_vencimiento, f.comprador_id, comp.nombre, comp.email 
                                        FROM cuotas c 
                                        JOIN financiaciones f ON c.financiacion_id = f.id 
                                        JOIN compradores comp ON f.comprador_id = comp.id 
                                        WHERE c.fecha_de_vencimiento BETWEEN '$primerDiaMes' AND '$ultimoDiaMes'")
            ];
            
            // Paso 3: Obtener pagos para las cuotas del mes
            $diagnostico['pasos'][2] = [
                'paso' => '3. Pagos para las cuotas del mes',
                'sql' => "SELECT p.* FROM pagos p 
                         JOIN cuotas c ON p.cuota_id = c.id 
                         WHERE c.fecha_de_vencimiento BETWEEN '$primerDiaMes' AND '$ultimoDiaMes'",
                'resultado' => DB::select("SELECT p.* FROM pagos p 
                                         JOIN cuotas c ON p.cuota_id = c.id 
                                         WHERE c.fecha_de_vencimiento BETWEEN '$primerDiaMes' AND '$ultimoDiaMes'")
            ];
            
            // Calcular totales para el dashboard
            // Total cuotas del mes
            $totalCuotasMes = count($diagnostico['pasos'][0]['resultado']);
            
            // Total monto de cuotas
            $totalMonto = 0;
            foreach ($diagnostico['pasos'][0]['resultado'] as $cuota) {
                $totalMonto += $cuota->monto;
            }
            
            // Total cuotas pagadas
            $cuotasPagadas = 0;
            foreach ($diagnostico['pasos'][0]['resultado'] as $cuota) {
                if ($cuota->estado == 'pagada') {
                    $cuotasPagadas++;
                }
            }
            
            // Calcular pagos totales sumando monto_usd de la tabla pagos
            $pagosTotales = 0;
            foreach ($diagnostico['pasos'][2]['resultado'] as $pago) {
                if (property_exists($pago, 'monto_usd')) {
                    $pagosTotales += $pago->monto_usd;
                }
            }
            
            // Calcular monto pendiente de pago
            $montoPendiente = $totalMonto - $pagosTotales;
            
            $diagnostico['totales'] = [
                'cuotas_mes' => $totalCuotasMes,
                'monto_total' => $totalMonto,
                'cuotas_pagadas' => $cuotasPagadas,
                'pagos_recibidos' => $pagosTotales,
                'monto_pendiente' => $montoPendiente
            ];
            
            // Obtener solo una lista simple de deudores con el campo judicializado
            $diagnostico['deudores'] = DB::select("SELECT DISTINCT comp.id, comp.nombre, comp.email, comp.telefono, COALESCE(comp.judicializado, 0) as judicializado
                                             FROM cuotas c 
                                             JOIN financiaciones f ON c.financiacion_id = f.id 
                                             JOIN compradores comp ON f.comprador_id = comp.id 
                                             WHERE c.estado IN ('pendiente', 'parcial') 
                                             AND c.fecha_de_vencimiento BETWEEN '$primerDiaMes' AND '$ultimoDiaMes'");
            
            // IMPORTANTE: Usa esta forma directa para pasar la variable
            Log::info("Antes de renderizar vista, pasando diagnóstico");
            return view('informes.informes', compact('diagnostico'));
            
        } catch (\Exception $e) {
            Log::error("Error en InformeController: " . $e->getMessage());
            Log::error($e->getTraceAsString());
            
            return view('informes.informes', [
                'error' => 'Error al generar el informe: ' . $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
    
    private function ejecutarConsulta($sql, $params = [])
    {
        try {
            $resultados = DB::select($sql, $params);
            return $resultados;
        } catch (\Exception $e) {
            return [
                'error' => $e->getMessage()
            ];
        }
    }
} 