<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Log extends Model
{
    public $timestamps = true;
    protected $fillable = ['user_id','accion'];
    public function user(){ return $this->belongsTo(User::class); }
}
