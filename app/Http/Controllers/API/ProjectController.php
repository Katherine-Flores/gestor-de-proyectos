<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProjectResource;
use App\Models\Project;
use App\Models\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

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
            'recursos' => 'nullable|array',
            'recursos.*.tipo' => 'required_with:recursos|string|in:tiempo,personas,equipos,servicios',
            'recursos.*.descripcion' => 'required_with:recursos|string|max:255',
            'recursos.*.cantidad' => 'required_with:recursos|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response(['errors' => $validator->errors(), 'message' => 'Validación fallida'],400);
        }

        DB::beginTransaction();
        try {
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

            // Crear recursos
            if (!empty($data['recursos'])) {
                foreach ($data['recursos'] as $recurso) {
                    $project->resources()->create($recurso);
                }
            }

            DB::commit();

            // Log
            $project->load(['users', 'resources']);

            $usuarios = $project->users->map(function ($u) {
                $rol = match ($u->role_id) {
                    1 => 'Líder',
                    2 => 'Integrante',
                    3 => 'Cliente',
                    default => 'Desconocido',
                };
                return "{$u->nombre} ({$rol})";
            })->join(', ');

            $recursos = $project->resources->map(function ($r) {
                return "{$r->tipo}: {$r->descripcion} (Cantidad: {$r->cantidad})";
            })->join(', ');

            $accion = "Creó el proyecto '{$project->nombre}' (ID {$project->id}). Descripción: {$project->descripcion}.";
            $accion .= " Tipo: {$project->tipo}, Categoría: {$project->categoria}, Estado inicial: {$project->estado}.";
            if ($usuarios) $accion .= "Usuarios asignados: {$usuarios}. ";
            if ($recursos) $accion .= "Recursos: {$recursos}. ";

            Log::create([
                'user_id' => $lider->id,
                'accion' => $accion,
                'created_at' => now(),
            ]);

            return response(['project' => new ProjectResource($project->load('users')), 'message' => 'Proyecto creado y usuarios asignados correctamente'], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response(['message' => 'Error al crear el proyecto', 'error' => $e->getMessage()], 500);
        }
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

        $data = $request->all();

        $validator = Validator::make($data, [
            'nombre' => 'sometimes|string|max:200',
            'descripcion' => 'nullable|string',
            'tipo' => 'sometimes|in:software,redes,hardware,otros',
            'categoria' => 'nullable|string|max:100',
            'fecha_inicio' => 'nullable|date',
            'fecha_fin_estimada' => 'nullable|date|after_or_equal:fecha_inicio',
            'clientes' => 'nullable|array',
            'clientes.*' => 'exists:users,id',
            'integrantes' => 'nullable|array',
            'integrantes.*' => 'exists:users,id',
            'recursos' => 'nullable|array',
            'recursos.*.tipo' => 'required_with:recursos|string|in:tiempo,personas,equipos,servicios',
            'recursos.*.descripcion' => 'required_with:recursos|string|max:255',
            'recursos.*.cantidad' => 'required_with:recursos|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response(['errors' => $validator->errors(), 'message' => 'Validación fallida'], 400);
        }

        DB::beginTransaction();
        try {
            // Guardar valores originales antes de actualizar
            $original = $project->getOriginal();

            unset($data['estado'], $data['porcentaje_avance'], $data['fecha_fin_real'], $data['resultado_final']);

            $project->update($data);

            $cambios = [];

            // Detectar campos cambiados automáticamente
            foreach ($project->getChanges() as $campo => $nuevoValor) {
                if ($campo === 'updated_at') continue; // ignoramos el timestamp
                $anterior = $original[$campo] ?? 'N/A';
                $cambios[] = ucfirst($campo) . " cambió de '{$anterior}' a '{$nuevoValor}'";
            }

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
                $cambios[] = "Usuarios asignados actualizados";
            }

            // Actualizar recursos
            if (!empty($data['recursos'])) {
                foreach ($data['recursos'] as $recursoData) {
                    if (isset($recursoData['id'])) {
                        // Si existe ID, actualiza el recurso existente
                        $recurso = $project->resources()->find($recursoData['id']);
                        if ($recurso) {
                            $recurso->update($recursoData);
                        }
                    } else {
                        // Si no tiene ID, es un recurso nuevo
                        $project->resources()->create($recursoData);
                    }
                }
                $cambios[] = "Recursos modificados o agregados";
            }

            DB::commit();

            // Log
            $accion = "Actualizó el proyecto '{$project->nombre}' (ID {$project->id}).";

            if (!empty($cambios)) {
                $accion .= "Cambios: " . implode('; ', $cambios) . ".";
            }

            Log::create([
                'user_id' => auth()->id(),
                'accion' => $accion,
                'created_at' => now(),
            ]);

            return response([
                'project' => new ProjectResource($project->load(['users', 'resources'])),
                'message' => 'Proyecto actualizado correctamente'
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response(['message' => 'Error al actualizar el proyecto', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project)
    {
        $user = auth()->user();

        if (!$user->isLider()) {
            return response(['message' => 'No autorizado. Solo líderes pueden eliminar proyectos.'], 403);
        }

        DB::beginTransaction();

        try {
            // Log
            $project->load(['users', 'resources']);

            $usuarios = $project->users->map(function ($u) {
                $rol = match ($u->role_id) {
                    1 => 'Líder',
                    2 => 'Integrante',
                    3 => 'Cliente',
                    default => 'Desconocido',
                };
                return "{$u->nombre} ({$rol})";
            })->join(', ');

            $recursos = $project->resources->pluck('descripcion')->join(', ') ?: 'Ninguno';

            $accion = "Eliminó el proyecto '{$project->nombre}' (ID {$project->id}). ";
            $accion .= "Descripción: {$project->descripcion}. ";
            $accion .= "Recursos: {$recursos}. ";
            $accion .= "Usuarios asignados: {$usuarios}.";

            $project->delete();

            Log::create([
                'user_id' => $user->id,
                'accion' => $accion,
                'created_at' => now(),
            ]);

            DB::commit();

            return response(['message' => 'Eliminado correctamente'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response(['message' => 'Error al eliminar el proyecto', 'error' => $e->getMessage()], 500);
        }
    }
}
