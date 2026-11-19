<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Alumno;
use App\Models\User;
use Illuminate\Validation\Rule;

class AlumnoController
{
    public function index(Request $request)
    {
        $buscar = $request->get('buscar');

        $alumnos = \App\Models\Alumno::query()
            ->when($buscar, function ($query, $buscar) {
                return $query->where('matricula', 'LIKE', "%{$buscar}%")
                             ->orWhere('nombres', 'LIKE', "%{$buscar}%")
                             ->orWhere('apellido_paterno', 'LIKE', "%{$buscar}%")
                             ->orWhere('curp', 'LIKE', "%{$buscar}%");
            })
            ->orderBy('matricula', 'desc')
            ->paginate(10); 

        return view('admin.alumnos.index', compact('alumnos', 'buscar'));
    }

    public function create()
    {
        $usuariosOcupados = \App\Models\Alumno::whereNotNull('id_usuario')->pluck('id_usuario')->toArray();

        $usuarios = \App\Models\User::where('id_rol', 3)
            ->whereNotIn('id_usuario', $usuariosOcupados)
            ->get();

        return view('admin.alumnos.create', compact('usuarios'));
    }

    public function store(Request $request)
    {
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
            'matricula' => 'required|string|max:30|unique:alumnos,matricula',
            'nombres'           => 'required|string|max:100',
            'apellido_paterno' => 'required|string|max:100',
            'apellido_materno' => 'nullable|string|max:100',
            'curp' => [
    'required',
    'string',
    'size:18',
    'regex:/^[A-Z]{4}[0-9]{6}[HM][A-Z]{5}[A-Z0-9][0-9]$/',
    'unique:alumnos,curp',
],
            'semestre'         => 'required|integer|in:1,2,3,4,5,6',
            'grupo'            => 'required|string|in:A,B'
        ]);

        Alumno::create($datos);

        return redirect()->route('admin.alumnos')->with('success', 'Expediente de alumno creado correctamente.');
    }

    public function edit($id)
    {
        $alumno = \App\Models\Alumno::where('matricula', $id)->firstOrFail(); 

        $usuariosOcupados = \App\Models\Alumno::whereNotNull('id_usuario')
            ->where('matricula', '!=', $id)
            ->pluck('id_usuario')->toArray();

        $usuarios = \App\Models\User::where('id_rol', 3)
            ->whereNotIn('id_usuario', $usuariosOcupados)
            ->get();

        return view('admin.alumnos.editorA', compact('alumno', 'usuarios'));
    }


public function update(Request $request, $id)
{
    // Comprobar que el alumno existe.
    $alumno = Alumno::where('matricula', $id)
        ->firstOrFail();

    // Normalizar la CURP antes de validarla.
    $request->merge([
        'curp' => strtoupper(
            trim((string) $request->input('curp', ''))
        ),
    ]);

    // Validar los datos recibidos.
    $datos = $request->validate(
        [
            'id_usuario' => [
                'required',
                'integer',
                Rule::exists('usuarios', 'id_usuario')
                    ->where('id_rol', 3),
                Rule::unique('alumnos', 'id_usuario')
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
        ],
        [
            'required' => 'El campo :attribute es obligatorio.',
            'integer' => 'El campo :attribute debe ser un número entero.',
            'string' => 'El campo :attribute debe contener texto.',
            'max' => 'El campo :attribute no debe superar :max caracteres.',
            'size' => 'El campo :attribute debe contener exactamente :size caracteres.',
            'regex' => 'El formato del campo :attribute no es válido.',
            'unique' => 'El valor de :attribute ya está registrado.',
            'exists' => 'La cuenta seleccionada no existe o no pertenece a un alumno.',
            'in' => 'El valor seleccionado en :attribute no es válido.',
        ],
        [
            'id_usuario' => 'cuenta de usuario',
            'nombres' => 'nombre',
            'apellido_paterno' => 'apellido paterno',
            'apellido_materno' => 'apellido materno',
            'curp' => 'CURP',
            'semestre' => 'semestre',
            'grupo' => 'grupo',
        ]
    );

    // Actualizar únicamente los campos validados.
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
