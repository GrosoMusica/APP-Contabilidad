<?php

namespace App\Http\Controllers;

use App\Models\Comprador;
use App\Models\Acreedor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CompradorController extends Controller
{
    // Método para mostrar la lista de compradores
    public function index()
    {
        // Cargar compradores con sus financiaciones y cuotas
        $compradores = Comprador::with('financiacion.cuotas')->get();
        return view('compradores_index', compact('compradores'));
    }

    public function edit($id)
    {
        $comprador = Comprador::findOrFail($id);
        return view('edit_comprador', compact('comprador'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'direccion' => 'required|string|max:255',
            'telefono' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'dni' => 'required|string|max:20',
        ]);

        $comprador = Comprador::findOrFail($id);
        $comprador->update($request->only(['nombre', 'direccion', 'telefono', 'email', 'dni', 'judicializado']));

        return redirect()->route('compradores.index')->with('success', 'Comprador actualizado exitosamente.');
    }

    public function toggleJudicializado($id)
    {
        $comprador = Comprador::findOrFail($id);
        $comprador->judicializado = !$comprador->judicializado;
        $comprador->save();

        return redirect()->route('compradores.index')->with('success', 'Estado de judicialización actualizado.');
    }

    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            // Validar los datos del comprador
            $request->validate([
                'nombre' => 'required|string|max:255',
                'direccion' => 'required|string|max:255',
                'telefono' => 'required|string|max:20',
                'email' => 'required|email|max:255',
                'dni' => 'required|string|max:20',
                // Otros campos necesarios...
            ]);

            // Crear el nuevo comprador
            $comprador = Comprador::create($request->all());

            // Crear la financiación asociada al comprador
            $financiacion = $comprador->financiacion()->create([
                'monto_a_financiar' => $request->input('monto_a_financiar'),
                // Otros campos de financiación...
            ]);

            // Asignar el acreedor "admin" al nuevo comprador
            $adminAcreedorId = 1; // ID del acreedor "admin"
            $comprador->acreedores()->attach($adminAcreedorId, ['porcentaje' => 100]);

            DB::commit();

            return redirect()->route('compradores.index')->with('success', 'Comprador creado exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors('Error al crear el comprador: ' . $e->getMessage());
        }
    }

    // Otros métodos CRUD pueden ir aquí
} 