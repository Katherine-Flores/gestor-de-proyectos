<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'nombre',
        'email',
        'password',
        'role_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function projects()
    {
        return $this->belongsToMany(Project::class, 'project_user', 'user_id', 'project_id');
    }

    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    // Scopes para roles
    public function scopeLideres($query)
    {
        return $query->whereHas('role', function($q) {
            $q->where('nombre', 'Lider');
        });
    }

    public function scopeIntegrantes($query)
    {
        return $query->whereHas('role', function($q) {
            $q->where('nombre', 'Integrante');
        });
    }

    public function scopeClientes($query)
    {
        return $query->whereHas('role', function($q) {
            $q->where('nombre', 'Cliente');
        });
    }

    // Métodos de verificación de roles
    public function isLider()
    {
        return $this->role->nombre === 'Lider';
    }

    public function isIntegrante()
    {
        return $this->role->nombre === 'Integrante';
    }

    public function isCliente()
    {
        return $this->role->nombre === 'Cliente';
    }

}
