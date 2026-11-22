<?php

namespace App\Http\Controllers\Admin;

use App\Models\Alumno;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AlumnoController
{
    public function index(Request $request)
    {
        $buscar = trim((string) $request->get('buscar', ''));

        $alumnos = Alumno::with('usuario')
            ->when($buscar !== '', function ($query) use ($buscar) {
                $query->where(function ($q) use ($buscar) {
                    $q->where('matricula', 'LIKE', "%{$buscar}%")
                        ->orWhere('nombres', 'LIKE', "%{$buscar}%")
                        ->orWhere('apellido_paterno', 'LIKE', "%{$buscar}%")
                        ->orWhere('apellido_materno', 'LIKE', "%{$buscar}%")
                        ->orWhere('curp', 'LIKE', "%{$buscar}%");
                });
            })
            ->orderBy('matricula', 'desc')
            ->paginate(10)
            ->withQueryString();

        return view('admin.alumnos.index', compact('alumnos', 'buscar'));
    }

    public function create()
    {
        $usuariosOcupados = Alumno::whereNotNull('id_usuario')
            ->pluck('id_usuario')
            ->toArray();

        $usuarios = User::where('id_rol', 3)
            ->whereNotIn('id_usuario', $usuariosOcupados)
            ->get();

        return view('admin.alumnos.create', compact('usuarios'));
    }

    public function store(Request $request)
    {
        // Normalizar la CURP antes de validarla.
        $request->merge([
            'curp' => strtoupper(trim((string) $request->input('curp', ''))),
        ]);

        $datos = $request->validate([
            'id_usuario' => [
                'required',
                'integer',
                Rule::exists('usuarios', 'id_usuario')
                    ->where('id_rol', 3),
                Rule::unique('alumnos', 'id_usuario'),
            ],

            'matricula' => [
                'required',
                'string',
                'max:30',
                Rule::unique('alumnos', 'matricula'),
            ],

            'nombres' => [
                'required',
                'string',
                'max:100',
            ],

            'apellido_paterno' => [
                'required',
                'string',
                'max:100',
            ],

            'apellido_materno' => [
                'nullable',
                'string',
                'max:100',
            ],

            'curp' => [
                'required',
                'string',
                'size:18',
                'regex:/^[A-Z]{4}[0-9]{6}[HM][A-Z]{5}[A-Z0-9][0-9]$/',
                Rule::unique('alumnos', 'curp'),
            ],

            'semestre' => [
                'required',
                'integer',
                Rule::in([1, 2, 3, 4, 5, 6]),
            ],

            'grupo' => [
                'required',
                'string',
                Rule::in(['A', 'B']),
            ],
        ]);

        // El estado inicial es establecido por el servidor.
        Alumno::create([
            ...$datos,
            'estado' => 'ACTIVO',
        ]);

        return redirect()
            ->route('admin.alumnos')
            ->with(
                'success',
                'Expediente de alumno creado correctamente.'
            );
    }

    public function edit($id)
    {
        $alumno = Alumno::where('matricula', $id)
            ->firstOrFail();

        $usuariosOcupados = Alumno::whereNotNull('id_usuario')
            ->where('matricula', '!=', $id)
            ->pluck('id_usuario')
            ->toArray();

        $usuarios = User::where('id_rol', 3)
            ->whereNotIn('id_usuario', $usuariosOcupados)
            ->get();

        return view(
            'admin.alumnos.editorA',
            compact('alumno', 'usuarios')
        );
    }

    public function update(Request $request, $id)
    {
        // Buscar el alumno mediante su matrícula actual.
        $alumno = Alumno::where('matricula', $id)
            ->firstOrFail();

        /*
        |--------------------------------------------------------------------------
        | CONSERVAR MATRÍCULA CUANDO NO SE ENVÍA
        |--------------------------------------------------------------------------
        |
        | Si el formulario no incluye el campo matrícula,
        | se conserva la matrícula que ya tiene el alumno.
        |
        | Si se envía una nueva matrícula, se validará
        | para evitar duplicados.
        |
        | Si se envía explícitamente una matrícula vacía,
        | la validación la rechazará.
        |
        */

        if (!$request->exists('matricula')) {
            $request->merge([
                'matricula' => $alumno->matricula,
            ]);
        }

        // Normalizar la CURP antes de validarla.
        $request->merge([
            'curp' => strtoupper(
                trim((string) $request->input('curp', ''))
            ),
        ]);

        /*
        |--------------------------------------------------------------------------
        | VALIDACIÓN SEGURA DE DATOS
        |--------------------------------------------------------------------------
        */

        $datos = $request->validate([
            'id_usuario' => [
                'required',
                'integer',
                Rule::exists('usuarios', 'id_usuario')
                    ->where('id_rol', 3),
                Rule::unique('alumnos', 'id_usuario')
                    ->ignore($alumno->id_alumno, 'id_alumno'),
            ],

            'matricula' => [
                'required',
                'string',
                'max:30',
                Rule::unique('alumnos', 'matricula')
                    ->ignore($alumno->id_alumno, 'id_alumno'),
            ],

            'nombres' => [
                'required',
                'string',
                'max:100',
            ],

            'apellido_paterno' => [
                'required',
                'string',
                'max:100',
            ],

            'apellido_materno' => [
                'nullable',
                'string',
                'max:100',
            ],

            'curp' => [
                'required',
                'string',
                'size:18',
                'regex:/^[A-Z]{4}[0-9]{6}[HM][A-Z]{5}[A-Z0-9][0-9]$/',
                Rule::unique('alumnos', 'curp')
                    ->ignore($alumno->id_alumno, 'id_alumno'),
            ],

            'semestre' => [
                'required',
                'integer',
                Rule::in([1, 2, 3, 4, 5, 6]),
            ],

            'grupo' => [
                'required',
                'string',
                Rule::in(['A', 'B']),
            ],

            'estado' => [
                'sometimes',
                'required',
                Rule::in(['ACTIVO', 'BAJA', 'EGRESADO']),
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | ACTUALIZACIÓN DEL EXPEDIENTE
        |--------------------------------------------------------------------------
        |
        | Solo se modifican los campos que fueron validados.
        | Si la petición no envía el estado, se conserva el actual.
        |
        */

        $alumno->fill($datos);
        $alumno->save();

        return redirect()
            ->route('admin.alumnos')
            ->with(
                'success',
                'Expediente de alumno modificado correctamente.'
            );
    }
}
