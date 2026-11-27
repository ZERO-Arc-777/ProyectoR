<?php

namespace App\Http\Controllers\Admin;

use App\Models\AsignacionAcademica;
use App\Models\Calificacion;
use App\Models\Configuracion;
use App\Models\Docente;
use App\Models\Materia;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class DocenteController
{
    private const GRUPOS_PERMITIDOS = ['A', 'B'];

    public function index(Request $request)
    {
        $datos = $request->validate([
            'buscar' => ['nullable', 'string', 'max:100'],
            'estado' => ['nullable', 'string', Rule::in(['ACTIVO', 'INACTIVO', 'BAJA'])],
        ]);

        $buscar = trim($datos['buscar'] ?? '');
        $estado = $datos['estado'] ?? null;

        $docentes = Docente::query()
            ->when($buscar !== '', function ($query) use ($buscar) {
                $query->where(function ($q) use ($buscar) {
                    $q->where('numero_empleado', 'LIKE', "%{$buscar}%")
                        ->orWhere('nombres', 'LIKE', "%{$buscar}%")
                        ->orWhere('apellido_paterno', 'LIKE', "%{$buscar}%")
                        ->orWhere('curp', 'LIKE', "%{$buscar}%");
                });
            })
            ->when($estado !== null, function ($query) use ($estado) {
                $query->where('estado', $estado);
            })
            ->orderBy('id_docente', 'desc')
            ->paginate(10)
            ->withQueryString();

        return view('admin.docentes.index', compact('docentes', 'buscar', 'estado'));
    }

    public function create()
    {
        $usuariosOcupados = Docente::whereNotNull('id_usuario')->pluck('id_usuario');

        $usuarios = User::where('id_rol', 2)
            ->whereNotIn('id_usuario', $usuariosOcupados)
            ->get();

        $materias = Materia::where('estado', 'ACTIVA')->orderBy('semestre')->orderBy('nombre')->get();

        return view('admin.docentes.create', compact('usuarios', 'materias'));
    }

    private function normalizarEntrada(Request $request): void
    {
        $datosNormalizados = [
            'curp' => strtoupper(trim((string) $request->input('curp', ''))),
        ];

        if (is_array($request->input('grupos'))) {
            $datosNormalizados['grupos'] = array_map(
                function ($grupo) {
                    if ($grupo === null) {
                        return null;
                    }

                    $limpio = strtoupper(trim((string) $grupo));

                    return $limpio === '' ? null : $limpio;
                },
                $request->input('grupos')
            );
        }

        $request->merge($datosNormalizados);
    }

    private function reglas(?int $id = null): array
    {
        return [
            'id_usuario' => [
                'nullable',
                'integer',
                Rule::exists('usuarios', 'id_usuario')->where('id_rol', 2),
                Rule::unique('docentes', 'id_usuario')->ignore($id, 'id_docente'),
            ],
            'numero_empleado' => [
                'required',
                'string',
                'max:20',
                Rule::unique('docentes', 'numero_empleado')->ignore($id, 'id_docente'),
            ],
            'nombres'            => ['required', 'string', 'max:100'],
            'apellido_paterno'   => ['required', 'string', 'max:100'],
            'apellido_materno'   => ['nullable', 'string', 'max:100'],
            'curp' => [
                'required',
                'string',
                'size:18',
                'regex:/^[A-Z]{4}[0-9]{6}[HM][A-Z]{5}[A-Z0-9][0-9]$/',
                Rule::unique('docentes', 'curp')->ignore($id, 'id_docente'),
            ],
            'cedula_profesional' => ['nullable', 'string', 'max:50'],
            'estado' => $id === null
                ? ['sometimes', Rule::in(['ACTIVO'])]
                : ['required', Rule::in(['ACTIVO', 'INACTIVO', 'BAJA'])],
            'materias'   => ['nullable', 'array'],
            'materias.*' => [
                'nullable',
                'integer',
                Rule::exists('materias', 'id_materia')->where('estado', 'ACTIVA'),
            ],
            'grupos'     => ['nullable', 'array'],
            'grupos.*'   => ['nullable', 'string', Rule::in(self::GRUPOS_PERMITIDOS)],
        ];
    }

    private function mensajesValidacion(): array
    {
        return [
            'required' => 'El campo :attribute es obligatorio.',
            'string'   => 'El campo :attribute debe contener texto.',
            'integer'  => 'El campo :attribute debe ser un número entero.',
            'array'    => 'El campo :attribute debe tener un formato válido.',
            'max'      => 'El campo :attribute no debe superar :max caracteres.',
            'size'     => 'El campo :attribute debe contener exactamente :size caracteres.',
            'regex'    => 'El formato del campo :attribute no es válido.',
            'unique'   => 'El valor de :attribute ya está registrado.',
            'exists'   => 'El valor seleccionado en :attribute no existe o no está disponible.',
            'in'       => 'El valor seleccionado en :attribute no es válido.',
        ];
    }

    private function atributosValidacion(): array
    {
        return [
            'id_usuario'         => 'cuenta de usuario',
            'numero_empleado'    => 'número de empleado',
            'nombres'            => 'nombre',
            'apellido_paterno'   => 'apellido paterno',
            'apellido_materno'   => 'apellido materno',
            'curp'               => 'CURP',
            'cedula_profesional' => 'cédula profesional',
            'estado'             => 'estado administrativo',
            'materias'           => 'materias',
            'materias.*'         => 'materia',
            'grupos'             => 'grupos',
            'grupos.*'           => 'grupo',
        ];
    }

    /**
     * Valida las asignaciones solicitadas y devuelve el mapa normalizado ["id_materia|grupo" => ['id_materia', 'grupo']].
     */
    private function validarYPrepararAsignaciones(array $datos, string $periodoActual, ?int $idDocenteActual = null): array
    {
        $materias = $datos['materias'] ?? [];
        $grupos = $datos['grupos'] ?? [];
        $combinaciones = [];

        foreach ($materias as $indice => $idMateria) {
            if ($idMateria === null || $idMateria === '') {
                continue;
            }

            $idMateriaInt = (int) $idMateria;
            $grupo = strtoupper(trim((string) ($grupos[$indice] ?? '')));

            if ($grupo === '') {
                throw ValidationException::withMessages([
                    "grupos.$indice" => 'Debes seleccionar un grupo para cada materia asignada.',
                ]);
            }

            if (!in_array($grupo, self::GRUPOS_PERMITIDOS, true)) {
                throw ValidationException::withMessages([
                    "grupos.$indice" => 'El grupo seleccionado no es válido. Solo se permiten los grupos A o B.',
                ]);
            }

            $clave = $idMateriaInt . '|' . $grupo;

            if (isset($combinaciones[$clave])) {
                throw ValidationException::withMessages([
                    "materias.$indice" => 'No puedes asignar dos veces la misma materia al mismo grupo.',
                ]);
            }

            // Verificar que otro docente no tenga ya esa misma materia en el mismo grupo y periodo.
            $ocupada = AsignacionAcademica::with(['docente', 'materia'])
                ->where('id_materia', $idMateriaInt)
                ->where('grupo', $grupo)
                ->where('periodo', $periodoActual)
                ->when($idDocenteActual !== null, fn ($q) => $q->where('id_docente', '!=', $idDocenteActual))
                ->first();

            if ($ocupada) {
                $nombreMateria = $ocupada->materia?->nombre ?? "Materia #{$idMateriaInt}";
                $semestre = $ocupada->materia?->semestre ? "{$ocupada->materia->semestre}° " : '';
                $nombreTitular = $ocupada->docente
                    ? trim("{$ocupada->docente->nombres} {$ocupada->docente->apellido_paterno} {$ocupada->docente->apellido_materno}")
                    : 'otro docente';

                throw ValidationException::withMessages([
                    "materias.$indice" => "La materia {$nombreMateria} ({$semestre}Grupo {$grupo}) ya está asignada a {$nombreTitular} en el periodo {$periodoActual}.",
                ]);
            }

            $combinaciones[$clave] = [
                'id_materia' => $idMateriaInt,
                'grupo'      => $grupo,
            ];
        }

        return $combinaciones;
    }

    public function store(Request $request)
    {
        $this->normalizarEntrada($request);

        $datos = $request->validate(
            $this->reglas(),
            $this->mensajesValidacion(),
            $this->atributosValidacion()
        );

        $periodoActual = $this->obtenerPeriodoActual();
        $asignacionesNuevas = $this->validarYPrepararAsignaciones($datos, $periodoActual);

        DB::transaction(function () use ($datos, $asignacionesNuevas, $periodoActual) {
            $docente = new Docente();
            $docente->id_usuario = $datos['id_usuario'] ?? null;
            $docente->numero_empleado = $datos['numero_empleado'];
            $docente->nombres = $datos['nombres'];
            $docente->apellido_paterno = $datos['apellido_paterno'];
            $docente->apellido_materno = $datos['apellido_materno'] ?? null;
            $docente->curp = $datos['curp'];
            $docente->cedula_profesional = $datos['cedula_profesional'] ?? null;
            $docente->estado = 'ACTIVO';
            $docente->save();

            foreach ($asignacionesNuevas as $item) {
                AsignacionAcademica::create([
                    'id_docente' => $docente->id_docente,
                    'id_materia' => $item['id_materia'],
                    'grupo'      => $item['grupo'],
                    'periodo'    => $periodoActual,
                ]);
            }
        });

        return redirect()
            ->route('admin.docentes')
            ->with('success', 'Docente y sus asignaciones registradas correctamente.');
    }

    public function edit($id)
    {
        $docente = Docente::where('id_docente', $id)->firstOrFail();

        $usuariosOcupados = Docente::whereNotNull('id_usuario')
            ->where('id_docente', '!=', $id)
            ->pluck('id_usuario');

        $usuarios = User::where('id_rol', 2)
            ->whereNotIn('id_usuario', $usuariosOcupados)
            ->get();

        $materias = Materia::where('estado', 'ACTIVA')->orderBy('semestre')->orderBy('nombre')->get();

        $periodoActual = $this->obtenerPeriodoActual();

        $asignacionesActuales = AsignacionAcademica::with('materia')
            ->withCount('calificaciones')
            ->where('id_docente', $docente->id_docente)
            ->where('periodo', $periodoActual)
            ->get();

        $otrosDocentes = Docente::where('id_docente', '!=', $docente->id_docente)
            ->where('estado', 'ACTIVO')
            ->orderBy('apellido_paterno')
            ->orderBy('apellido_materno')
            ->orderBy('nombres')
            ->get();

        return view(
            'admin.docentes.editorD',
            compact(
                'docente',
                'usuarios',
                'materias',
                'asignacionesActuales',
                'periodoActual',
                'otrosDocentes'
            )
        );
    }

    public function update(Request $request, $id)
    {
        $this->normalizarEntrada($request);

        $docente = Docente::where('id_docente', $id)->firstOrFail();

        $datos = $request->validate(
            $this->reglas((int) $docente->id_docente),
            $this->mensajesValidacion(),
            $this->atributosValidacion()
        );

        $periodoActual = $this->obtenerPeriodoActual();
        $deseadas = $this->validarYPrepararAsignaciones($datos, $periodoActual, (int) $docente->id_docente);

        $existentes = AsignacionAcademica::with('materia')
            ->where('id_docente', $docente->id_docente)
            ->where('periodo', $periodoActual)
            ->get()
            ->keyBy(fn ($a) => $a->id_materia . '|' . strtoupper($a->grupo));

        // Verificar que ninguna asignación a retirar tenga ya calificaciones capturadas.
        $aEliminar = $existentes->diffKeys($deseadas);

        foreach ($aEliminar as $asigVieja) {
            $tieneNotas = Calificacion::where('id_asignacion', $asigVieja->id_asignacion)->exists();

            if ($tieneNotas) {
                $nombreMateria = $asigVieja->materia?->nombre ?? "Materia #{$asigVieja->id_materia}";
                $semestre = $asigVieja->materia?->semestre ? "{$asigVieja->materia->semestre}° " : '';

                throw ValidationException::withMessages([
                    'materias' => "No puedes quitar la materia {$nombreMateria} ({$semestre}Grupo {$asigVieja->grupo}) porque ya tiene calificaciones capturadas en {$periodoActual}. Utiliza el botón «Sustituir docente» en la sección inferior para transferirla sin perder actas.",
                ]);
            }
        }

        DB::transaction(function () use ($docente, $datos, $existentes, $deseadas, $aEliminar, $periodoActual) {
            $docente->id_usuario = $datos['id_usuario'] ?? null;
            $docente->numero_empleado = $datos['numero_empleado'];
            $docente->nombres = $datos['nombres'];
            $docente->apellido_paterno = $datos['apellido_paterno'];
            $docente->apellido_materno = $datos['apellido_materno'] ?? null;
            $docente->curp = $datos['curp'];
            $docente->cedula_profesional = $datos['cedula_profesional'] ?? null;
            $docente->estado = $datos['estado'];
            $docente->save();

            // 1. Eliminar solo las asignaciones retiradas (sin notas).
            foreach ($aEliminar as $asigVieja) {
                $asigVieja->delete();
            }

            // 2. Insertar únicamente las asignaciones nuevas (conservando intactos los id_asignacion existentes).
            foreach ($deseadas as $clave => $item) {
                if (!$existentes->has($clave)) {
                    AsignacionAcademica::create([
                        'id_docente' => $docente->id_docente,
                        'id_materia' => $item['id_materia'],
                        'grupo'      => $item['grupo'],
                        'periodo'    => $periodoActual,
                    ]);
                }
            }
        });

        return redirect()
            ->route('admin.docentes')
            ->with('success', 'Expediente de docente y asignaciones actualizados correctamente.');
    }

    public function sustituirAsignacion(Request $request, $id_asignacion)
    {
        $periodoActual = $this->obtenerPeriodoActual();

        $asignacion = AsignacionAcademica::with(['materia', 'docente'])
            ->where('id_asignacion', $id_asignacion)
            ->firstOrFail();

        if ($asignacion->periodo !== $periodoActual) {
            return back()->withErrors([
                'sustitucion' => 'Solo es posible sustituir asignaciones correspondientes al periodo escolar actual.',
            ]);
        }

        $datos = $request->validate([
            'nuevo_id_docente' => [
                'required',
                'integer',
                Rule::exists('docentes', 'id_docente')->where('estado', 'ACTIVO'),
            ],
        ], [
            'nuevo_id_docente.required' => 'Selecciona al docente sustituto.',
            'nuevo_id_docente.exists'   => 'El docente sustituto debe existir y encontrarse en estado ACTIVO.',
        ]);

        if ((int) $datos['nuevo_id_docente'] === (int) $asignacion->id_docente) {
            return back()->withErrors([
                'sustitucion' => 'El docente sustituto debe ser distinto al docente actual.',
            ]);
        }

        $nuevoDocente = Docente::findOrFail($datos['nuevo_id_docente']);

        // Al conservar el mismo id_asignacion, el nuevo docente hereda todas las calificaciones ya capturadas.
        $asignacion->id_docente = $nuevoDocente->id_docente;
        $asignacion->save();

        $nombreMateria = $asignacion->materia?->nombre ?? "Materia #{$asignacion->id_materia}";
        $nombreNuevo = trim("{$nuevoDocente->nombres} {$nuevoDocente->apellido_paterno} {$nuevoDocente->apellido_materno}");

        return back()->with(
            'success',
            "La materia {$nombreMateria} (Grupo {$asignacion->grupo}) fue transferida a {$nombreNuevo} conservando sus calificaciones registradas."
        );
    }

    private function obtenerPeriodoActual(): string
    {
        return Configuracion::where('clave', 'periodo_actual')->value('valor') ?? 'No definido';
    }
}
