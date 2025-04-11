<?php

namespace App\Http\Controllers;

use App\Models\Acreedor;
use App\Models\Comprador;
use App\Models\Financiacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AcreedorController extends Controller
{
    /**
     * Mostrar vista para crear un nuevo acreedor
     */
    public function create()
    {
        return view('acreedores.create');
    }

    /**
     * Crear un nuevo acreedor sin asociarlo a ninguna financiación
     */
    public function storeSimple(Request $request)
    {
        // Validar datos básicos del acreedor
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
        ]);

        try {
            // Crear el acreedor
            $acreedor = Acreedor::create([
                'nombre' => $request->nombre,
                'descripcion' => $request->descripcion,
            ]);
            
            return redirect()->route('gestion.acreedores.index')
                ->with('success', "Se ha creado el acreedor {$acreedor->nombre} correctamente.");
                
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al crear el acreedor: ' . $e->getMessage());
        }
    }

    /**
     * Almacenar un nuevo acreedor o asociar uno existente a una financiación
     */
    public function store(Request $request)
    {
        // Validar los datos del formulario
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'porcentaje' => 'required|numeric|min:0.01|max:100',
            'financiacion_id' => 'required|exists:financiaciones,id',
            'redirect_to' => 'nullable|string',
        ]);

        // Obtener la financiación
        $financiacion = Financiacion::findOrFail($request->financiacion_id);
        
        try {
            DB::beginTransaction();
            
            // Buscar o crear el acreedor
            $acreedor = Acreedor::firstOrCreate(['nombre' => $request->nombre]);
            
            // Verificar si ya existe la relación
            $relacion = DB::table('financiacion_acreedor')
                ->where('financiacion_id', $financiacion->id)
                ->where('acreedor_id', $acreedor->id)
                ->first();
                
            if ($relacion) {
                DB::rollBack();
                return redirect()->back()
                    ->with('error', 'Este acreedor ya está asociado a esta financiación.')
                    ->with('redirect_to', $request->redirect_to);
            }
            
            // Verificar si hay suficiente porcentaje disponible
            $porcentajeAsignado = DB::table('financiacion_acreedor')
                ->where('financiacion_id', $financiacion->id)
                ->where('acreedor_id', '<>', 1) // Excluir al admin
                ->sum('porcentaje');
                
            $porcentajeDisponible = 100 - $porcentajeAsignado;
            
            if ($request->porcentaje > $porcentajeDisponible) {
                DB::rollBack();
                return redirect()->back()
                    ->with('error', "Solo hay {$porcentajeDisponible}% disponible para asignar.")
                    ->with('redirect_to', $request->redirect_to);
            }
            
            // Asociar el acreedor a la financiación con el porcentaje (sin timestamps)
            DB::table('financiacion_acreedor')->insert([
                'financiacion_id' => $financiacion->id,
                'acreedor_id' => $acreedor->id,
                'porcentaje' => $request->porcentaje,
            ]);
            
            // Actualizar el porcentaje del admin
            $adminRelacion = DB::table('financiacion_acreedor')
                ->where('financiacion_id', $financiacion->id)
                ->where('acreedor_id', 1) // Admin
                ->first();
                
            if ($adminRelacion) {
                // El nuevo porcentaje del admin es lo que queda de 100%
                $nuevoPorcentajeAdmin = 100 - ($porcentajeAsignado + $request->porcentaje);
                
                // Actualizar o eliminar la relación del admin según corresponda
                if ($nuevoPorcentajeAdmin > 0) {
                    DB::table('financiacion_acreedor')
                        ->where('financiacion_id', $financiacion->id)
                        ->where('acreedor_id', 1)
                        ->update([
                            'porcentaje' => $nuevoPorcentajeAdmin,
                        ]);
                } else {
                    // Si el admin ya no tiene porcentaje, eliminar su relación
                    DB::table('financiacion_acreedor')
                        ->where('financiacion_id', $financiacion->id)
                        ->where('acreedor_id', 1)
                        ->delete();
                }
            } else if ($porcentajeAsignado + $request->porcentaje < 100) {
                // Si no existe relación con el admin y aún queda porcentaje, crearlo (sin timestamps)
                $porcentajeAdmin = 100 - ($porcentajeAsignado + $request->porcentaje);
                DB::table('financiacion_acreedor')->insert([
                    'financiacion_id' => $financiacion->id,
                    'acreedor_id' => 1, // Admin
                    'porcentaje' => $porcentajeAdmin,
                ]);
            }
            
            DB::commit();
            
            return redirect()->back()
                ->with('success', "Se ha asignado {$request->porcentaje}% a {$acreedor->nombre} correctamente.")
                ->with('redirect_to', $request->redirect_to);
                
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Error al procesar la solicitud: ' . $e->getMessage())
                ->with('redirect_to', $request->redirect_to);
        }
    }
} 