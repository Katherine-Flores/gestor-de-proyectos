<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'descripcion',
        'tipo',
        'categoria',
        'estado',
        'fecha_inicio',
        'fecha_fin_estimada',
        'fecha_fin_real',
        'porcentaje_avance',
    ];
}
