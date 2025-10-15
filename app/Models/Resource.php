<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Resource extends Model
{
    protected $fillable = ['tipo','descripcion','cantidad','project_id'];

    public function project() {
        return $this->belongsTo(Project::class);
    }
}
