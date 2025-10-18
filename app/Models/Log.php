<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Log extends Model
{
    public $timestamps = false;
    protected $fillable = ['user_id','accion', 'created_at'];
    public function user(){ return $this->belongsTo(User::class); }
}
