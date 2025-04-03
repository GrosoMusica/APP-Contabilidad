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

        DB::beginTransaction();

        try {
            Log::info('Iniciando creación de comprador');
            $comprador = Comprador::create($request->only(['nombre', 'direccion', 'telefono', 'email', 'dni']));
            Log::info('Comprador creado con ID: ' . $comprador->id);

            // Crear el lote asociado al comprador
            $lote = Lote::create([
                'estado' => 'comprado',
                'comprador_id' => $comprador->id,
                'loteo' => $request->loteo,
                'manzana' => $request->manzana,
                'lote' => $request->lote,
                'mts_cuadrados' => $request->mts_cuadrados,
            ]);
            Log::info('Lote creado con ID: ' . $lote->id);

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
            Log::info('Financiación creada con ID: ' . $financiacion->id);

            // Crear las cuotas
            $fechaVencimiento = Carbon::parse($request->fecha_de_vencimiento);
            for ($i = 0; $i < $request->cantidad_de_cuotas; $i++) {
                $cuota = Cuota::create([
                    'financiacion_id' => $financiacion->id,
                    'monto' => $montoDeLasCuotas,
                    'fecha_de_vencimiento' => $fechaVencimiento->copy()->addMonths($i),
                    'estado' => 'pendiente', // Estado inicial
                ]);
                Log::info('Cuota creada con ID: ' . $cuota->id . ' para la financiación ID: ' . $financiacion->id);
            }

            // Actualizar el comprador con los IDs de lote y financiación
            $comprador->update([
                'lote_comprado_id' => $lote->id,
                'financiacion_id' => $financiacion->id,
            ]);

            // Crear el acreedor por defecto
            $adminAcreedor = Acreedor::firstOrCreate(['nombre' => 'admin'], ['saldo' => 0]);

            // Calcular el porcentaje restante para el acreedor "admin"
            $totalPorcentaje = 100;
            $porcentajeAsignado = 0;

            // Asociar acreedores adicionales
            if ($request->has('acreedores')) {
                foreach ($request->acreedores as $acreedorData) {
                    $financiacion->acreedores()->attach($acreedorData['id'], ['porcentaje' => $acreedorData['porcentaje']]);
                    $porcentajeAsignado += $acreedorData['porcentaje'];
                }
            }

            // Asignar el porcentaje restante al acreedor "admin"
            $porcentajeAdmin = $totalPorcentaje - $porcentajeAsignado;
            $financiacion->acreedores()->attach($adminAcreedor->id, ['porcentaje' => $porcentajeAdmin]);

            DB::commit();

            return redirect()->back()->with('success', 'Entradas creadas exitosamente.');
        } catch (\Exception $e) {
            Log::error('Error al crear las entradas: ' . $e->getMessage());
            DB::rollBack();
            return redirect()->back()->withErrors(['error' => 'Hubo un problema al crear las entradas: ' . $e->getMessage()]);
        }
    }

    public function show($id)
    {
        $comprador = Comprador::findOrFail($id);
        $cuotas = Cuota::where('financiacion_id', $comprador->financiacion_id)->get();

        return view('comprador_detalle', compact('comprador', 'cuotas'));
    }

    public function index()
    {
        $compradores = Comprador::all();
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