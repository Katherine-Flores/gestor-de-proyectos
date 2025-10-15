<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProjectResource;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = auth()->user();

        // Control de acceso por roles
        if ($user->isLider()) {
            $projects = Project::all();
        } elseif ($user->isIntegrante() || $user->isCliente()) {
            // Solo proyectos donde el usuario esté asignado
            $projects = $user->projects;
        } else {
            return response(['message' => 'No autorizado'], 403);
        }

        return response([
            'projects' => ProjectResource::collection($projects),
            'message' => 'Recuperado correctamente'
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Solo líderes pueden crear proyectos
        $lider = auth()->user();
        if (!auth()->user()->isLider()) {
            return response(['message' => 'No autorizado. Solo líderes pueden crear proyectos.'], 403);
        }

        $data = $request->all();

        $validator = Validator::make($data, [
            'nombre' => 'required|string|max:200',
            'descripcion' => 'nullable|string',
            'tipo' => 'required|in:software,redes,hardware,otros',
            'categoria' => 'nullable|string|max:100',
            'estado' => 'required|in:Planificado,En Ejecución,En Auditoría,Finalizado',
            'fecha_inicio' => 'nullable|date',
            'fecha_fin_estimada' => 'nullable|date|after_or_equal:fecha_inicio',
            'fecha_fin_real' => 'nullable|date|after_or_equal:fecha_inicio',
            'porcentaje_avance' => 'numeric|min:0|max:100',
            'clientes' => 'nullable|array',
            'clientes.*' => 'exists:users,id',
            'integrantes' => 'nullable|array',
            'integrantes.*' => 'exists:users,id',
        ]);

        if ($validator->fails()) {
            return response(['errors' => $validator->errors(), 'message' => 'Validación fallida'],400);
        }

        $project = Project::create($data);

        // Asociar usuarios al proyecto
        $asignados = [];

        // Asignar al lider
        $asignados[] = $lider->id;

        // Asignar a los clientes
        if (!empty($data['clientes'])) {
            $asignados = array_merge($asignados, $data['clientes']);
        }

        // Asignar a los integrantes
        if (!empty($data['integrantes'])) {
            $asignados = array_merge($asignados, $data['integrantes']);
        }

        // Quitar duplicados por si se repite alguno
        $asignados = array_unique($asignados);

        // Asignarlos a todos al proyecto
        $project->users()->attach($asignados);

        return response(['project' => new ProjectResource($project->load('users')), 'message' => 'Proyecto creado y usuarios asignados correctamente'], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project)
    {
        $user = auth()->user();

        if (!$user->isLider() && !$user->projects->contains($project->id)) {
            return response(['message' => 'No autorizado para ver este proyecto'], 403);
        }

        return response([
            'project' => new ProjectResource($project),
            'message' => 'Recuperado correctamente'
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Project $project)
    {
        if (!auth()->user()->isLider()) {
            return response(['message' => 'No autorizado. Solo líderes pueden modificar proyectos.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'nombre' => 'sometimes|string|max:200',
            'descripcion' => 'nullable|string',
            'tipo' => 'sometimes|in:software,redes,hardware,otros',
            'categoria' => 'nullable|string|max:100',
            'estado' => 'sometimes|in:Planificado,En Ejecución,En Auditoría,Finalizado',
            'fecha_inicio' => 'nullable|date',
            'fecha_fin_estimada' => 'nullable|date|after_or_equal:fecha_inicio',
            'fecha_fin_real' => 'nullable|date|after_or_equal:fecha_inicio',
            'porcentaje_avance' => 'numeric|min:0|max:100',
            'clientes' => 'nullable|array',
            'clientes.*' => 'exists:users,id',
            'integrantes' => 'nullable|array',
            'integrantes.*' => 'exists:users,id',
        ]);

        if ($validator->fails()) {
            return response(['errors' => $validator->errors(), 'message' => 'Validación fallida'], 400);
        }

        $project->update($request->all());

        if ($request->has('clientes') || $request->has('integrantes')) {
            $asignados = [];

            // Mantener al líder
            $asignados[] = auth()->user()->id;

            // Actualizar clientes
            if ($request->has('clientes')) {
                $asignados = array_merge($asignados, $request->clientes ?? []);
            }

            // Actualizar integrantes
            if ($request->has('integrantes')) {
                $asignados = array_merge($asignados, $request->integrantes ?? []);
            }

            // Actualizar relación (detach + attach)
            $project->users()->sync(array_unique($asignados));
        }


        return response([
            'project' => new ProjectResource($project),
            'message' => 'Actualizado correctamente'
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project)
    {
        if (!auth()->user()->isLider()) {
            return response(['message' => 'No autorizado. Solo líderes pueden eliminar proyectos.'], 403);
        }

        $project->delete();

        return response(['message' => 'Eliminado correctamente'], 200);
    }
}
