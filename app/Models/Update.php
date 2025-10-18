<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Update extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'contenido',
        'porcentaje_avance',
        'estado_actualizado',
        'user_id',
        'project_id',
        'created_at',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
