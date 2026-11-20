<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

use App\Models\Docente;
use App\Models\User;
use App\Models\Materia;
use App\Models\AsignacionAcademica;
use App\Models\Configuracion;

class DocenteController
{
    // ==========================================
    // CONSULTAR DOCENTES
    // ==========================================

    public function index(Request $request)
    {
        $datos = $request->validate([
            'buscar' => ['nullable', 'string', 'max:100'],
        ]);

        $buscar = trim($datos['buscar'] ?? '');

        $docentes = Docente::query()
            ->when($buscar !== '', function ($query) use ($buscar) {
                $query->where(function ($q) use ($buscar) {
                    $q->where('numero_empleado', 'LIKE', "%{$buscar}%")
                        ->orWhere('nombres', 'LIKE', "%{$buscar}%")
                        ->orWhere('apellido_paterno', 'LIKE', "%{$buscar}%");
                });
            })
            ->orderBy('id_docente', 'desc')
            ->paginate(10)
            ->withQueryString();

        return view(
            'admin.docentes.index',
            compact('docentes', 'buscar')
        );
    }

    // ==========================================
    // MOSTRAR FORMULARIO DE REGISTRO
    // ==========================================

    public function create()
    {
        $usuariosOcupados = Docente::whereNotNull('id_usuario')
            ->pluck('id_usuario');

        $usuarios = User::where('id_rol', 2)
            ->whereNotIn('id_usuario', $usuariosOcupados)
            ->get();

        $materias = Materia::where('estado', 'ACTIVA')->get();

        return view(
            'admin.docentes.create',
            compact('usuarios', 'materias')
        );
    }

    // ==========================================
    // VALIDACIONES GENERALES
    // ==========================================

    private function reglas(?int $id = null): array
    {
        return [
            'id_usuario' => [
                'nullable',
                'integer',
                Rule::exists('usuarios', 'id_usuario')
                    ->where('id_rol', 2),
                Rule::unique('docentes', 'id_usuario')
                    ->ignore($id, 'id_docente'),
            ],

            'numero_empleado' => [
                'required',
                'string',
                'max:20',
                Rule::unique('docentes', 'numero_empleado')
                    ->ignore($id, 'id_docente'),
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
                Rule::unique('docentes', 'curp')
                    ->ignore($id, 'id_docente'),
            ],

            'cedula_profesional' => [
                'nullable',
                'string',
                'max:50',
            ],

            'estado' => $id === null
                ? ['sometimes', Rule::in(['ACTIVO'])]
                : [
                    'required',
                    Rule::in([
                        'ACTIVO',
                        'INACTIVO',
                        'BAJA',
                    ]),
                ],

            'materias' => [
                'nullable',
                'array',
            ],

            'materias.*' => [
                'nullable',
                'integer',
                Rule::exists('materias', 'id_materia')
                    ->where('estado', 'ACTIVA'),
            ],

            'grupos' => [
                'nullable',
                'array',
            ],

            'grupos.*' => [
                'nullable',
                'string',
                'max:10',
            ],
        ];
    }

    // ==========================================
    // MENSAJES DE VALIDACION
    // ==========================================

    private function mensajesValidacion(): array
    {
        return [
            'required' => 'El campo :attribute es obligatorio.',
            'string' => 'El campo :attribute debe contener texto.',
            'integer' => 'El campo :attribute debe ser un número entero.',
            'array' => 'El campo :attribute debe tener un formato válido.',
            'max' => 'El campo :attribute no debe superar :max caracteres.',
            'size' => 'El campo :attribute debe contener exactamente :size caracteres.',
            'regex' => 'El formato del campo :attribute no es válido.',
            'unique' => 'El valor de :attribute ya está registrado.',
            'exists' => 'El valor seleccionado en :attribute no existe o no está disponible.',
            'in' => 'El valor seleccionado en :attribute no es válido.',
        ];
    }

    private function atributosValidacion(): array
    {
        return [
            'id_usuario' => 'cuenta de usuario',
            'numero_empleado' => 'número de empleado',
            'nombres' => 'nombre',
            'apellido_paterno' => 'apellido paterno',
            'apellido_materno' => 'apellido materno',
            'curp' => 'CURP',
            'cedula_profesional' => 'cédula profesional',
            'estado' => 'estado administrativo',
            'materias' => 'materias',
            'materias.*' => 'materia',
            'grupos' => 'grupos',
            'grupos.*' => 'grupo',
        ];
    }

    // ==========================================
    // VALIDAR ASIGNACIONES ACADEMICAS
    // ==========================================

    private function validarAsignaciones(array $datos): void
    {
        $materias = $datos['materias'] ?? [];
        $grupos = $datos['grupos'] ?? [];

        $combinaciones = [];

        foreach ($materias as $indice => $idMateria) {
            if ($idMateria === null || $idMateria === '') {
                continue;
            }

            $grupo = strtoupper(
                trim((string) ($grupos[$indice] ?? ''))
            );

            if ($grupo === '') {
                throw ValidationException::withMessages([
                    "grupos.$indice" =>
                        'Debes indicar un grupo para cada materia seleccionada.',
                ]);
            }

            if (mb_strlen($grupo) > 10) {
                throw ValidationException::withMessages([
                    "grupos.$indice" =>
                        'El grupo no puede superar los 10 caracteres.',
                ]);
            }

            $clave = $idMateria . '|' . $grupo;

            if (isset($combinaciones[$clave])) {
                throw ValidationException::withMessages([
                    "materias.$indice" =>
                        'No puedes asignar dos veces la misma materia al mismo grupo.',
                ]);
            }

            $combinaciones[$clave] = true;
        }
    }

    // ==========================================
    // GUARDAR ASIGNACIONES ACADEMICAS
    // ==========================================

    private function guardarAsignaciones(
        int $idDocente,
        array $datos,
        string $periodo
    ): void {
        $materias = $datos['materias'] ?? [];
        $grupos = $datos['grupos'] ?? [];

        foreach ($materias as $indice => $idMateria) {
            if ($idMateria === null || $idMateria === '') {
                continue;
            }

            $grupo = strtoupper(
                trim((string) ($grupos[$indice] ?? ''))
            );

            AsignacionAcademica::create([
                'id_docente' => $idDocente,
                'id_materia' => $idMateria,
                'grupo' => $grupo,
                'periodo' => $periodo,
            ]);
        }
    }

    // ==========================================
    // REGISTRAR DOCENTE
    // ==========================================

    public function store(Request $request)
    {
        $request->merge([
            'curp' => strtoupper(
                trim((string) $request->input('curp', ''))
            ),
        ]);

        $datos = $request->validate(
            $this->reglas(),
            $this->mensajesValidacion(),
            $this->atributosValidacion()
        );

        $this->validarAsignaciones($datos);

        DB::transaction(function () use ($datos) {
            $docente = new Docente();

            $docente->id_usuario =
                $datos['id_usuario'] ?? null;

            $docente->numero_empleado =
                $datos['numero_empleado'];

            $docente->nombres =
                $datos['nombres'];

            $docente->apellido_paterno =
                $datos['apellido_paterno'];

            $docente->apellido_materno =
                $datos['apellido_materno'] ?? null;

            $docente->curp =
                $datos['curp'];

            $docente->cedula_profesional =
                $datos['cedula_profesional'] ?? null;

            $docente->estado = 'ACTIVO';

            $docente->save();

            $periodoActual = Configuracion::where(
                'clave',
                'periodo_actual'
            )->first()?->valor ?? 'No definido';

            $this->guardarAsignaciones(
                $docente->id_docente,
                $datos,
                $periodoActual
            );
        });

        return redirect()
            ->route('admin.docentes')
            ->with(
                'success',
                'Docente y sus asignaciones registradas correctamente.'
            );
    }

    // ==========================================
    // MOSTRAR FORMULARIO DE EDICION
    // ==========================================

    public function edit($id)
    {
        $docente = Docente::where(
            'id_docente',
            $id
        )->firstOrFail();

        $usuariosOcupados = Docente::whereNotNull('id_usuario')
            ->where('id_docente', '!=', $id)
            ->pluck('id_usuario');

        $usuarios = User::where('id_rol', 2)
            ->whereNotIn('id_usuario', $usuariosOcupados)
            ->get();

        $materias = Materia::where('estado', 'ACTIVA')->get();

        $periodoActual = Configuracion::where(
            'clave',
            'periodo_actual'
        )->first()?->valor ?? 'No definido';

        $asignacionesActuales = AsignacionAcademica::where(
            'id_docente',
            $docente->id_docente
        )
            ->where('periodo', $periodoActual)
            ->get();

        return view(
            'admin.docentes.editorD',
            compact(
                'docente',
                'usuarios',
                'materias',
                'asignacionesActuales'
            )
        );
    }

    // ==========================================
    // ACTUALIZAR DOCENTE
    // ==========================================

    public function update(Request $request, $id)
    {
        $request->merge([
            'curp' => strtoupper(
                trim((string) $request->input('curp', ''))
            ),
        ]);

        $docente = Docente::where(
            'id_docente',
            $id
        )->firstOrFail();

        $datos = $request->validate(
            $this->reglas((int) $docente->id_docente),
            $this->mensajesValidacion(),
            $this->atributosValidacion()
        );

        $this->validarAsignaciones($datos);

        DB::transaction(function () use ($docente, $datos) {
            $docente->id_usuario =
                $datos['id_usuario'] ?? null;

            $docente->numero_empleado =
                $datos['numero_empleado'];

            $docente->nombres =
                $datos['nombres'];

            $docente->apellido_paterno =
                $datos['apellido_paterno'];

            $docente->apellido_materno =
                $datos['apellido_materno'] ?? null;

            $docente->curp =
                $datos['curp'];

            $docente->cedula_profesional =
                $datos['cedula_profesional'] ?? null;

            $docente->estado =
                $datos['estado'];

            $docente->save();

            $periodoActual = Configuracion::where(
                'clave',
                'periodo_actual'
            )->first()?->valor ?? 'No definido';

            // Conservar asignaciones de otros periodos.
            AsignacionAcademica::where(
                'id_docente',
                $docente->id_docente
            )
                ->where('periodo', $periodoActual)
                ->delete();

            $this->guardarAsignaciones(
                $docente->id_docente,
                $datos,
                $periodoActual
            );
        });

        return redirect()
            ->route('admin.docentes')
            ->with(
                'success',
                'Expediente de docente y asignaciones actualizados correctamente.'
            );
    }
}
