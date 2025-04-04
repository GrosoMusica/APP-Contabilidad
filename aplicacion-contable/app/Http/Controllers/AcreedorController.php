<?php

namespace App\Http\Controllers;

use App\Models\Acreedor;
use App\Models\Comprador;
use App\Models\Financiacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AcreedorController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'porcentaje' => 'required|numeric|min:1|max:100',
            'financiacion_id' => 'required|exists:financiaciones,id',
        ]);

        DB::beginTransaction();
        try {
            // Comprobar si ya existe un acreedor con ese nombre
            $acreedor = Acreedor::where('nombre', $request->nombre)->first();
            
            if (!$acreedor) {
                // Si no existe, crearlo manualmente con todos los campos necesarios
                $acreedor = new Acreedor();
                $acreedor->nombre = $request->nombre;
                $acreedor->saldo = 0; // Asignar saldo inicial explícitamente
                $acreedor->save();
            }

            // Verificar si ya existe una relación entre este acreedor y esta financiación
            $existingRelation = DB::table('financiacion_acreedor')
                ->where('financiacion_id', $request->financiacion_id)
                ->where('acreedor_id', $acreedor->id)
                ->exists();

            if ($existingRelation) {
                throw new \Exception('Este acreedor ya está asignado a esta financiación.');
            }

            // Obtener el porcentaje actual del admin
            $adminFinanciacion = DB::table('financiacion_acreedor')
                ->where('financiacion_id', $request->financiacion_id)
                ->where('acreedor_id', 1)
                ->first();

            if (!$adminFinanciacion) {
                // Si no existe, crear la relación admin-financiación con 100%
                DB::table('financiacion_acreedor')->insert([
                    'financiacion_id' => $request->financiacion_id,
                    'acreedor_id' => 1,
                    'porcentaje' => 100
                ]);
                $adminPorcentaje = 100;
            } else {
                $adminPorcentaje = $adminFinanciacion->porcentaje;
            }

            // Verificar que hay suficiente porcentaje disponible
            if ($request->porcentaje > $adminPorcentaje) {
                throw new \Exception("El porcentaje solicitado ({$request->porcentaje}%) excede el disponible ($adminPorcentaje%).");
            }

            // Asociar acreedor a la financiación con el porcentaje
            DB::table('financiacion_acreedor')->insert([
                'financiacion_id' => $request->financiacion_id,
                'acreedor_id' => $acreedor->id,
                'porcentaje' => $request->porcentaje
            ]);

            // Actualizar porcentaje del admin
            $nuevoAdminPorcentaje = $adminPorcentaje - $request->porcentaje;
            
            DB::table('financiacion_acreedor')
                ->where('financiacion_id', $request->financiacion_id)
                ->where('acreedor_id', 1)
                ->update([
                    'porcentaje' => $nuevoAdminPorcentaje
                ]);

            DB::commit();

            // Redirigir con mensaje de éxito y anclaje para scroll
            $redirectTo = $request->input('redirect_to', '');
            return redirect()->back()->with('success', 'Acreedor agregado correctamente.')->withFragment($redirectTo);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error al agregar acreedor: ' . $e->getMessage())->withFragment($request->input('redirect_to', ''));
        }
    }
} 