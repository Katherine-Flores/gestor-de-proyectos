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
        'resultado_final',
        'fecha_inicio',
        'fecha_fin_estimada',
        'fecha_fin_real',
        'porcentaje_avance',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'project_user', 'project_id', 'user_id');
    }

    public function resources()
    {
        return $this->hasMany(Resource::class, 'project_id');
    }

    public function updates()
    {
        return $this->hasMany(Update::class, 'project_id');
    }
}
