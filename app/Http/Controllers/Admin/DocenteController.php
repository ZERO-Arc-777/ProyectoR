<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Docente;
use App\Models\User;
use App\Models\MateriaA;
use App\Models\AsignacionAcademica;
use App\Models\Configuracion;

class DocenteController
{
    public function index(Request $request)
    {
        $buscar = $request->get('buscar');

        $docentes = Docente::query()
            ->when($buscar, function ($query, $buscar) {
                return $query->where('numero_empleado', 'LIKE', "%{$buscar}%")
                             ->orWhere('nombres', 'LIKE', "%{$buscar}%")
                             ->orWhere('apellido_paterno', 'LIKE', "%{$buscar}%");
            })
            ->orderBy('id_docente', 'desc')
            ->paginate(10); 
            
        return view('admin.docentes.index', compact('docentes', 'buscar'));
    }

    public function create()
    {
        $usuariosOcupados = Docente::whereNotNull('id_usuario')->pluck('id_usuario')->toArray();
        
        $usuarios = User::where('id_rol', 2)
            ->whereNotIn('id_usuario', $usuariosOcupados)
            ->get();
        
        $materias = MateriaA::where('estado', 'ACTIVA')->get();
        
        return view('admin.docentes.create', compact('usuarios', 'materias'));
    }

    public function store(Request $request)
    {
        $docente = new Docente();
        $docente->id_usuario = $request->id_usuario;
        $docente->numero_empleado = $request->numero_empleado;
        $docente->nombres = $request->nombres;
        $docente->apellido_paterno = $request->apellido_paterno;
        $docente->apellido_materno = $request->apellido_materno;
        $docente->curp = $request->curp;
        $docente->cedula_profesional = $request->cedula_profesional;
        $docente->estado = 'ACTIVO';
        $docente->save();

        $periodoActual = Configuracion::where('clave', 'periodo_actual')->first()->valor ?? 'No definido';

        if ($request->has('materias') && is_array($request->materias)) {
            foreach ($request->materias as $index => $id_materia) {
                if (!empty($id_materia)) {
                    AsignacionAcademica::create([
                        'id_docente' => $docente->id_docente,
                        'id_materia' => $id_materia,
                        'grupo'      => $request->grupos[$index] ?? 'A',
                        'periodo'    => $periodoActual
                    ]);
                }
            }
        }

        return redirect()->route('admin.docentes')->with('success', 'Docente y sus asignaciones registradas correctamente.');
    }

    public function edit($id)
    {
        $docente = Docente::where('id_docente', $id)->firstOrFail(); 
        
        $usuariosOcupados = Docente::whereNotNull('id_usuario')
            ->where('id_docente', '!=', $id)
            ->pluck('id_usuario')->toArray();

        $usuarios = User::where('id_rol', 2)
            ->whereNotIn('id_usuario', $usuariosOcupados)
            ->get();

        // 4. Cargamos las materias activas y las asignaciones que ya tiene este docente en el periodo actual
        $materias = MateriaA::where('estado', 'ACTIVA')->get();
        $periodoActual = Configuracion::where('clave', 'periodo_actual')->first()->valor ?? 'No definido';
        
        $asignacionesActuales = AsignacionAcademica::where('id_docente', $docente->id_docente)
            ->where('periodo', $periodoActual)
            ->get();

        return view('admin.docentes.editorD', compact('docente', 'usuarios', 'materias', 'asignacionesActuales'));
    }

    public function update(Request $request, $id)
    {
        $docente = Docente::where('id_docente', $id)->firstOrFail();

        $docente->id_usuario = $request->id_usuario;
        $docente->numero_empleado = $request->numero_empleado;
        $docente->nombres = $request->nombres;
        $docente->apellido_paterno = $request->apellido_paterno;
        $docente->apellido_materno = $request->apellido_materno;
        $docente->curp = $request->curp;
        $docente->cedula_profesional = $request->cedula_profesional;
        $docente->estado = $request->estado;
        
        $docente->save();

        $periodoActual = Configuracion::where('clave', 'periodo_actual')->first()->valor ?? 'No definido';

        AsignacionAcademica::where('id_docente', $docente->id_docente)
            ->where('periodo', $periodoActual)
            ->delete();

        if ($request->has('materias') && is_array($request->materias)) {
            foreach ($request->materias as $index => $id_materia) {
                if (!empty($id_materia)) {
                    AsignacionAcademica::create([
                        'id_docente' => $docente->id_docente,
                        'id_materia' => $id_materia,
                        'grupo'      => $request->grupos[$index] ?? 'A',
                        'periodo'    => $periodoActual
                    ]);
                }
            }
        }

        return redirect()->route('admin.docentes')->with('success', 'Expediente de docente y asignaciones actualizados correctamente.');
    }
}
