<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProjectResource;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Project;
use App\Models\Log;
use App\Models\Update;

class UpdateController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // Control de acceso por roles
        if ($user->isLider()) {
            $updates = Update::with(['project', 'user'])->latest()->get();
        } elseif ($user->isIntegrante() || $user->isCliente()) {
            // Solo puede ver actualizaciones de los proyectos en los que participa
            $projectIds = $user->projects->pluck('id');
            $updates = Update::whereIn('project_id', $projectIds)->with(['project', 'user'])->latest()->get();
        } else {
            return response(['message' => 'No autorizado'], 403);
        }

        return response([
            'updates' => $updates,
            'message' => 'Actualizaciones recuperadas correctamente'
        ], 200);
    }

    public function store(Request $request)
    {
        // Solo el líder puede registrar avances
        if (!auth()->user()->isLider()) {
            return response(['message' => 'No autorizado. Solo líderes pueden registrar avances.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'project_id' => 'required|exists:projects,id',
            'contenido' => 'required|string|max:500',
            'porcentaje_avance' => 'required|numeric|min:0|max:100',
            'estado_actualizado' => 'nullable|string|max:100',
            'resultado_final' => 'nullable|required_if:estado_actualizado,Finalizado|in:Completo,Incompleto,Cancelado',
        ]);

        if ($validator->fails()) {
            return response(['errors' => $validator->errors()], 400);
        }

        $project = Project::findOrFail($request->project_id);
        $estado_original = $project->estado;

        $dataToUpdate = [
            'porcentaje_avance' => $request->porcentaje_avance,
            'estado' => $request->estado_actualizado ?? $project->estado,
        ];

        // LÓGICA DE FINALIZACIÓN
        if ($request->estado_actualizado === 'Finalizado') {
            $dataToUpdate['resultado_final'] = $request->resultado_final;
            $dataToUpdate['fecha_fin_real'] = now();
            if ($request->resultado_final === 'Completo') {
                $dataToUpdate['porcentaje_avance'] = 100;
            }
        } else {
            $dataToUpdate['resultado_final'] = null;
        }

        $avanceParaUpdate = $request->porcentaje_avance;
        if (
            $request->estado_actualizado === 'Finalizado' &&
            $request->resultado_final === 'Completo'
        ) {
            $avanceParaUpdate = 100; // Si es Completo, forzamos el 100% en el registro histórico
        }

        DB::beginTransaction();
        try {
            // Crear el registro de avance
            $update = $project->updates()->create([
                'contenido' => $request->contenido,
                'porcentaje_avance' => $avanceParaUpdate,
                'estado_actualizado' => $request->estado_actualizado ?? $project->estado,
                'user_id' => auth()->id(),
                'project_id' => $request->project_id,
                'created_at' => now(),
            ]);

            // Actualizar el porcentaje del proyecto
            $project->update($dataToUpdate);

            DB::commit();

            // Log de acción
            $accion = "Registró un avance ({$project->porcentaje_avance}%) en el proyecto '{$project->nombre}' (ID {$project->id}).";
            if ($project->estado !== $estado_original) {
                $accion .= " Estado actualizado a: {$project->estado}.";
                if ($request->estado_actualizado === 'Finalizado') {
                    $accion .= " Resultado final: {$project->resultado_final}.";
                }
            }

            Log::create([
                'user_id' => auth()->id(),
                'accion' => $accion,
                'created_at' => now(),
            ]);

            return response([
                'message' => 'Avance registrado correctamente',
                'project' => new ProjectResource($project),
                'update' => $update
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response([
                'message' => 'Error al registrar el avance',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Project $project, Update $update)
    {
        if (!auth()->user()->isLider()) {
            return response(['message' => 'No autorizado. Solo líderes pueden eliminar avances.'], 403);
        }

        $update->delete();

        Log::create([
            'user_id' => auth()->id(),
            'accion' => "Eliminó un registro de avance del proyecto '{$project->nombre}' (ID {$project->id}).",
            'created_at' => now(),
        ]);

        return response(['message' => 'Registro de avance eliminado correctamente.'], 200);
    }

}
