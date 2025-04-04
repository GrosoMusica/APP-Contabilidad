<?php

namespace App\Http\Controllers;

use App\Models\Acreedor;
use App\Models\Comprador;
use App\Models\Financiacion;
use Illuminate\Http\Request;

class AcreedorController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'porcentaje' => 'required|numeric|max:100',
            'financiacion_id' => 'required|exists:financiaciones,id',
        ]);

        $comprador = Comprador::find($request->comprador_id);
        $adminAcreedor = $comprador->acreedores()->where('nombre', 'admin')->first();

        if ($adminAcreedor) {
            $adminAcreedor->pivot->porcentaje -= $request->porcentaje;
            $adminAcreedor->pivot->save();
        }

        $acreedor = new Acreedor(['nombre' => $request->nombre]);
        $comprador->acreedores()->attach($acreedor, [
            'porcentaje' => $request->porcentaje,
            'financiacion_id' => $request->financiacion_id,
        ]);

        return redirect()->back()->with('success', 'Acreedor agregado exitosamente.');
    }
} 