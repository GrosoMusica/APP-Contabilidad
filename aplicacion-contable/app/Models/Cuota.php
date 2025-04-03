<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cuota extends Model
{
    use HasFactory;

    protected $table = 'cuotas';

    protected $fillable = [
        'financiacion_id',
        'monto',
        'fecha_de_vencimiento',
        'estado',
    ];

    /**
     * Relación con Financiacion
     */
    public function financiacion()
    {
        return $this->belongsTo(Financiacion::class, 'financiacion_id');
    }
} 