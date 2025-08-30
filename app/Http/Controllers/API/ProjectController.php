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
        $projects = Project::all();
        return response(['projects' => ProjectResource::collection($projects), 'message' => 'Retrieved successfully'], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
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
            'porcentaje_avance' => 'numeric|min:0|max:100'
        ]);

        if ($validator->fails()) {
            return response(['errors' => $validator->errors(), 'message' => 'Validation Fail'],400);
        }

        $project = Project::create($data);

        return response(['project' => new ProjectResource($project), 'message' => 'Created successfully'], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project)
    {
        return response(['project' => new ProjectResource($project), 'message' => 'Retrieved successfully'], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Project $project)
    {
        $project->update($request->all());

        return response(['project' => new ProjectResource($project), 'message' => 'Updated successfully'], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project)
    {
        $project->delete();

        return response(['message' => 'Deleted successfully'], 200);
    }
}
