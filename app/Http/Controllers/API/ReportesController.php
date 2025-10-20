<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReportesController extends Controller
{
    public function proyectosCreados(Request $request)
    {
        $user = Auth::user();

        // Filtros
        $fechaInicio = $request->query('fecha_inicio');
        $fechaFin = $request->query('fecha_fin');
        $tipo = $request->query('tipo');

        $query = Project::query();

        // Filtro por rol
        if ($user->isLider()) {
            $query->whereHas('users', function ($q) use ($user) {
                $q->where('users.id', $user->id);
            });
        } elseif ($user->isIntegrante() || $user->isCliente()) {
            $query->whereHas('users', fn($q) => $q->where('users.id', $user->id));
        } else {
            return response(['message' => 'No autorizado'], 403);
        }

        // Filtrar por fechas si se indican
        if ($fechaInicio && $fechaFin) {
            $query->whereBetween('created_at', [$fechaInicio, $fechaFin]);
        }

        // Agrupaciones
        if ($tipo === 'mensual') {
            $reportes = $query->selectRaw('YEAR(created_at) as anio, MONTH(created_at) as mes, COUNT(*) as total')
                ->groupBy('anio', 'mes')
                ->orderBy('anio', 'desc')
                ->orderBy('mes', 'desc')
                ->get();
        } elseif ($tipo === 'anual') {
            $reportes = $query->selectRaw('YEAR(created_at) as anio, COUNT(*) as total')
                ->groupBy('anio')
                ->orderBy('anio', 'desc')
                ->get();
        } else {
            $reportes = $query->get();
        }

        return response([
            'data' => $reportes,
            'message' => 'Reporte generado correctamente'
        ], 200);
    }

    public function proyectosEnEjecucion()
    {
        $user = Auth::user();

        $query = Project::where('estado', 'En ejecución');

        if ($user->isLider() || $user->isIntegrante() || $user->isCliente()) {
            $query->whereHas('users', fn($q) => $q->where('users.id', $user->id));
        } else {
            return response(['message' => 'No autorizado'], 403);
        }

        $proyectos = $query->select('id', 'nombre', 'porcentaje_avance', 'estado', 'updated_at')->get();

        return response([
            'data' => $proyectos,
            'message' => 'Proyectos en ejecución recuperados correctamente'
        ], 200);
    }

    public function proyectosFinalizados(Request $request)
    {
        $resultado = $request->query('resultado');
        $user = Auth::user();

        $query = Project::where('estado', 'Finalizado');

        if ($resultado) {
            $query->where('resultado_final', ucfirst($resultado));
        }

        if ($user->isLider() || $user->isIntegrante() || $user->isCliente()) {
            $query->whereHas('users', fn($q) => $q->where('users.id', $user->id));
        } else {
            return response(['message' => 'No autorizado'], 403);
        }

        $proyectos = $query->select('id', 'nombre', 'descripcion', 'porcentaje_avance', 'resultado_final', 'updated_at')->get();

        return response([
            'data' => $proyectos,
            'message' => 'Proyectos finalizados recuperados correctamente'
        ], 200);
    }

    public function proyectosPorLider()
    {
        $user = Auth::user();

        if (!$user->isLider()) {
            return response(['message' => 'No autorizado'], 403);
        }

        // 1. Obtener todos los proyectos
        // 2. Filtrar solo los usuarios que son líderes (role_id = 1)
        // 3. Obtener el promedio de avance y el conteo de proyectos para cada líder.

        $reportes = DB::table('project_user')
            ->join('projects', 'project_user.project_id', '=', 'projects.id')
            ->join('users', 'project_user.user_id', '=', 'users.id')
            ->join('roles', 'users.role_id', '=', 'roles.id')
            ->where('roles.nombre', 'Lider') // Asumiendo que el líder siempre tiene el rol 'Lider' en project_user
            ->selectRaw('users.id as lider_id, users.nombre as nombre_lider, AVG(projects.porcentaje_avance) as progreso_promedio, COUNT(projects.id) as total')
            ->groupBy('users.id', 'users.nombre')
            ->get();

        return response([
            'data' => $reportes,
            'message' => 'Reporte de proyectos por líder generado correctamente'
        ], 200);
    }

    public function proyectosPorCliente()
    {
        $user = Auth::user();

        if (!$user->isLider()) {
            return response(['message' => 'No autorizado'], 403);
        }

        $reportes = DB::table('project_user')
            ->join('projects', 'project_user.project_id', '=', 'projects.id')
            ->join('users', 'project_user.user_id', '=', 'users.id')
            ->join('roles', 'users.role_id', '=', 'roles.id')

            // Filtramos solo por los usuarios cuyo rol es 'Cliente'
            ->where('roles.nombre', 'Cliente')

            // Seleccionamos y agrupamos por el Cliente
            ->selectRaw('users.id as cliente_id, users.nombre as nombre_cliente, AVG(projects.porcentaje_avance) as progreso_promedio, COUNT(projects.id) as total')
            ->groupBy('users.id', 'users.nombre')
            ->get();

        return response([
            'data' => $reportes,
            'message' => 'Reporte de proyectos por cliente generado correctamente'
        ], 200);
    }
}
