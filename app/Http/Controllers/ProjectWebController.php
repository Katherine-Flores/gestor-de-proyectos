<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project; // <-- Importante: usa tu modelo
use App\Models\User;    // <-- Para los 'select'
// use App\Models\Client; // <-- Para los 'select'

// ¡Nombre de clase cambiado para evitar conflictos!
class ProjectWebController extends Controller
{
    /**
     * Muestra la VISTA de listado
     */
    public function index()
    {
        // --- LÓGICA DE USUARIO COMENTADA TEMPORALMENTE ---
        // (Porque comentamos el middleware 'auth' en routes/web.php)
        // $user = auth()->user();
        // if ($user->isLider()) {
        //     $projects = Project::all();
        // } else {
        //     $projects = $user->projects;
        // }

        // --- LÍNEA TEMPORAL PARA QUE FUNCIONE SIN LOGIN ---
        $projects = Project::all();

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

        $data['estado'] = 'Planificado';
        $data['porcentaje_avance'] = 0;

        $project = Project::create($data);

        $asignados = [];
        if (!empty($data['clientes'])) $asignados = array_merge($asignados, $data['clientes']);
        if (!empty($data['integrantes'])) $asignados = array_merge($asignados, $data['integrantes']);

        $project->users()->sync(array_unique($asignados));

        return redirect()->route('projects.index')
            ->with('success', '¡Proyecto creado exitosamente!');
    }

    /**
     * Muestra la VISTA de detalle
     */
    public function show(Project $project)
    {
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
        return view('projects.edit', [
            'project' => $project
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
