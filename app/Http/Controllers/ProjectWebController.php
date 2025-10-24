<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project; // <-- Asegúrate de que tu modelo esté en app/Models/Project.php
use App\Models\User;    // <-- Asegúrate de que tu modelo esté en app/Models/User.php

// Este es el controlador para TUS VISTAS WEB
class ProjectWebController extends Controller
{
    /**
     * Muestra la VISTA de listado
     */
    public function index()
    {
        // --- LÓGICA DE USUARIO COMENTADA TEMPORALMENTE ---
        // $user = auth()->user();
        // ... (lógica de roles) ...

        // --- LÍNEA TEMPORAL PARA QUE FUNCIONE SIN LOGIN ---
        // Usamos '\App\Models\Project' para estar seguros de la ruta
        $projects = \App\Models\Project::all();

        // Retornamos la VISTA
        return view('projects.index', [
            'projects' => $projects
        ]);
    }

    /**
     * Muestra la VISTA de formulario de creación
     */
    public function create()
    {
        // $all_users = User::all();
        // $all_clients = User::where('role_id', 3)->get();

        return view('projects.create' /*, compact('all_users', 'all_clients')*/);
    }

    /**
     * Guarda el nuevo proyecto desde el formulario WEB
     */
    public function store(Request $request)
    {
        // Validación basada en tu controlador de API
        $data = $request->validate([
            'nombre' => 'required|string|max:200',
            'descripcion' => 'nullable|string',
            'tipo' => 'required|in:software,redes,hardware,otros',
            'categoria' => 'nullable|string|max:100',
            'fecha_inicio' => 'nullable|date',
            'fecha_fin_estimada' => 'nullable|date|after_or_equal:fecha_inicio',
            'clientes' => 'nullable|array',
            'integrantes' => 'nullable|array',
        ]);

        // Asignar valores por defecto (basado en tu API)
        $data['estado'] = 'Planificado';
        $data['porcentaje_avance'] = 0;

        $project = \App\Models\Project::create($data);

        // Asignar usuarios (clientes e integrantes)
        $asignados = [];
        if (!empty($data['clientes'])) $asignados = array_merge($asignados, $data['clientes']);
        if (!empty($data['integrantes'])) $asignados = array_merge($asignados, $data['integrantes']);

        $project->users()->sync(array_unique($asignados));

        // Redirigir de vuelta al índice con un mensaje de éxito
        return redirect()->route('projects.index')
            ->with('success', '¡Proyecto creado exitosamente!');
    }

    /**
     * Muestra la VISTA de detalle
     */
    public function show(Project $project)
    {
        // Cargar las relaciones para verlas
        $project->load('users', 'resources');

        return view('projects.show', [
            'project' => $project
        ]);
    }

    /**
     * Muestra la VISTA de edición
     */
    public function edit(Project $project)
    {
        // $all_users = User::all();
        // $all_clients = User::where('role_id', 3)->get();

        return view('projects.edit', [
            'project' => $project
            // 'all_users' => $all_users,
            // 'all_clients' => $all_clients
        ]);
    }

    /**
     * Actualiza el proyecto desde el formulario WEB
     */
    public function update(Request $request, Project $project)
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:200',
            'descripcion' => 'nullable|string',
            'tipo' => 'required|in:software,redes,hardware,otros',
            'categoria' => 'nullable|string|max:100',
            'fecha_inicio' => 'nullable|date',
            'fecha_fin_estimada' => 'nullable|date|after_or_equal:fecha_inicio',
            'clientes' => 'nullable|array',
            'integrantes' => 'nullable|array',
        ]);

        $project->update($data);

        $asignados = [];
        if ($request->has('clientes')) $asignados = array_merge($asignados, $request->clientes ?? []);
        if ($request->has('integrantes')) $asignados = array_merge($asignados, $request->integrantes ?? []);

        // Sincronizar los usuarios
        $project->users()->sync(array_unique($asignados));

        return redirect()->route('projects.index')
            ->with('success', '¡Proyecto actualizado!');
    }

    /**
     * Elimina el proyecto
     */
    public function destroy(Project $project)
    {
        $project->delete();

        return redirect()->route('projects.index')
            ->with('success', 'Proyecto eliminado.');
    }
}
