<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Role;

class RolesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('roles')->insert([
            ['nombre' => 'Lider'],
            ['nombre' => 'Integrante'],
            ['nombre' => 'Cliente'],
        ]);

        // Crear superusuario (solo si no existe)
        $adminEmail = 'admin@gestor.com';

        if (!User::where('email', $adminEmail)->exists()) {
            $lider = Role::where('nombre', 'Líder')->first();

            User::create([
                'nombre' => 'Administrador',
                'email' => $adminEmail,
                'password' => Hash::make('admin123'),
                'role_id' => $lider->id,
            ]);
        }
    }
}
