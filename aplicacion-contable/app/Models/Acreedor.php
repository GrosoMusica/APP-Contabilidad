<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Acreedor extends Model
{
    use HasFactory;

    protected $table = 'acreedores';

    protected $fillable = ['nombre', 'monto_adeudado', 'porcentaje', 'saldo'];

    public function compradores()
    {
        return $this->hasManyThrough(
            Comprador::class,
            Financiacion::class,
            'id', // Clave local en financiacion_acreedor
            'id', // Clave local en compradores
            'id', // Clave remota en acreedores
            'comprador_id' // Clave remota en financiaciones
        );
    }

    public function financiaciones()
    {
        return $this->belongsToMany(Financiacion::class, 'financiacion_acreedor')
                    ->withPivot('porcentaje');
    }
}