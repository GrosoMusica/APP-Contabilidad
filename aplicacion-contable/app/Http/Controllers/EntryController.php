<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Comprador;
use App\Models\Lote;
use App\Models\Financiacion;
use App\Models\Acreedor;
use App\Models\Cuota;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EntryController extends Controller
{
    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            // Validar los datos del formulario
            $request->validate([
                'nombre' => 'required|string|max:255',
                'direccion' => 'required|string|max:255',
                'telefono' => 'required|string|max:255',
                'email' => 'required|email|max:255',
                'dni' => 'required|string|max:20',
                'loteo' => 'required|string|max:255',
                'manzana' => 'required|string|max:255',
                'lote' => 'required|string|max:255',
                'mts_cuadrados' => 'required|numeric',
                'monto_a_financiar' => 'required|numeric',
                'cantidad_de_cuotas' => 'required|integer',
                'fecha_de_vencimiento' => 'required|date',
                'acreedores' => 'array',
                'acreedores.*.id' => 'exists:acreedores,id',
                'acreedores.*.porcentaje' => 'numeric|min:0|max:100',
            ]);

            // Crear el nuevo comprador
            $comprador = Comprador::create($request->only(['nombre', 'direccion', 'telefono', 'email', 'dni']));

            // Crear el lote asociado al comprador
            $lote = Lote::create([
                'estado' => 'comprado',
                'comprador_id' => $comprador->id,
                'loteo' => $request->loteo,
                'manzana' => $request->manzana,
                'lote' => $request->lote,
                'mts_cuadrados' => $request->mts_cuadrados,
            ]);

            // Calcular el monto de las cuotas
            $montoDeLasCuotas = $request->monto_a_financiar / $request->cantidad_de_cuotas;

            // Crear la financiación asociada al comprador
            $financiacion = Financiacion::create([
                'comprador_id' => $comprador->id,
                'monto_a_financiar' => $request->monto_a_financiar,
                'cantidad_de_cuotas' => $request->cantidad_de_cuotas,
                'fecha_de_vencimiento' => $request->fecha_de_vencimiento,
                'monto_de_las_cuotas' => $montoDeLasCuotas,
            ]);

            // Crear las cuotas
            $fechaVencimiento = Carbon::parse($request->fecha_de_vencimiento);
            for ($i = 1; $i <= $request->cantidad_de_cuotas; $i++) {
                Cuota::create([
                    'financiacion_id' => $financiacion->id,
                    'monto' => $montoDeLasCuotas,
                    'fecha_de_vencimiento' => $fechaVencimiento->copy()->addMonths($i - 1),
                    'estado' => 'pendiente',
                    'numero_de_cuota' => $i,
                ]);
            }

            // Asociar la financiación al acreedor "admin"
            $adminAcreedor = Acreedor::firstOrCreate(['nombre' => 'admin'], ['saldo' => 0]);
            $financiacion->acreedores()->attach($adminAcreedor->id, ['porcentaje' => 100]);

            DB::commit();

            return redirect()->back()->with('success', 'Entradas creadas exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['error' => 'Hubo un problema al crear las entradas: ' . $e->getMessage()]);
        }
    }

    public function show($id)
    {
        $comprador = Comprador::with('lote', 'financiacion.cuotas', 'financiacion.acreedores')->findOrFail($id);
        $cuotas = $comprador->financiacion->cuotas;
        $abonadoHastaLaFecha = $cuotas->where('estado', 'pagada')->sum('monto');
        $saldoPendiente = $comprador->financiacion->monto_a_financiar - $abonadoHastaLaFecha;

        // Obtener acreedores asociados a la financiación
        $acreedores = $comprador->financiacion->acreedores;

        // Si no hay acreedores, asociar el acreedor "admin" a la financiación
        if ($acreedores->isEmpty()) {
            $adminAcreedorId = 1; // ID del acreedor "admin"
            $comprador->financiacion->acreedores()->attach($adminAcreedorId, ['porcentaje' => 100]);
            $acreedores = $comprador->financiacion->acreedores()->get(); // Actualizar la colección de acreedores
        }

        return view('comprador_detalle', compact('comprador', 'cuotas', 'abonadoHastaLaFecha', 'saldoPendiente', 'acreedores'));
    }

    public function index()
    {
        $compradores = Comprador::with(['financiacion.cuotas' => function ($query) {
            $query->whereMonth('fecha_de_vencimiento', now()->month)
                  ->whereYear('fecha_de_vencimiento', now()->year);
        }])->get();

        foreach ($compradores as $comprador) {
            $cuotaActual = $comprador->financiacion->cuotas->first();
            
            if ($cuotaActual) {
                // Determina el color del estado directamente
                $estadoColor = $cuotaActual->estado_color;
            } else {
                $estadoColor = 'text-muted'; // Sin cuotas
            }

            // Pasar el color del estado a la vista
            $comprador->estado_color = $estadoColor;
        }

        return view('compradores_index', compact('compradores'));
    }

    public function edit($id)
    {
        $comprador = Comprador::findOrFail($id);
        return view('comprador_edit', compact('comprador'));
    }

    public function destroy($id)
    {
        $comprador = Comprador::findOrFail($id);
        $comprador->delete();

        return redirect()->route('compradores.index')->with('success', 'Comprador eliminado exitosamente.');
    }
}