<?php

namespace App\Http\Controllers\Admin;

use App\Models\Materia;
use App\Models\Configuracion;
use Illuminate\Http\Request;

class MateriaController
{
    // ==========================================
    // CONSULTAR MATERIAS
    // ==========================================

    public function index(Request $request)
    {
        $datos = $request->validate([
            'buscar' => 'nullable|string|max:100',
        ]);

        $buscar = trim($datos['buscar'] ?? '');

        // Consultar las materias registradas.
        $materias = Materia::query()
            ->when($buscar !== '', function ($query) use ($buscar) {
                $query->where('nombre', 'LIKE', "%{$buscar}%");
            })
            ->orderBy('semestre', 'asc')
            ->paginate(10)
            ->withQueryString();

        // Recuperar el periodo escolar guardado.
        $periodoActual = Configuracion::where(
            'clave',
            'periodo_actual'
        )->value('valor') ?? '';

        // Enviar la información a la vista.
        return view(
            'admin.materias.index',
            compact(
                'materias',
                'buscar',
                'periodoActual'
            )
        );
    }

    // ==========================================
    // FORMULARIO DE REGISTRO
    // ==========================================

    public function create()
    {
        return view('admin.materias.creadorM');
    }

    // ==========================================
    // REGISTRAR MATERIA
    // ==========================================

    public function store(Request $request)
    {
        $datos = $request->validate([
            'nombre' => 'required|string|max:100',
            'semestre' => 'required|integer|min:1|max:6',
        ]);

        Materia::create([
            'nombre' => strtoupper(trim($datos['nombre'])),
            'semestre' => $datos['semestre'],
            'estado' => 'ACTIVA',
        ]);

        return redirect()
            ->route('admin.materias')
            ->with(
                'success',
                'Materia registrada correctamente.'
            );
    }

    // ==========================================
    // FORMULARIO DE EDICIÓN
    // ==========================================

    public function edit($id_materia)
    {
        $materia = Materia::findOrFail($id_materia);

        return view(
            'admin.materias.editorM',
            compact('materia')
        );
    }

    // ==========================================
    // ACTUALIZAR MATERIA
    // ==========================================

    public function update(Request $request, $id_materia)
    {
        $datos = $request->validate([
            'nombre' => 'required|string|max:100',
            'semestre' => 'required|integer|min:1|max:6',
            'estado' => 'required|in:ACTIVA,INACTIVA',
        ]);

        $materia = Materia::findOrFail($id_materia);

        $materia->update([
            'nombre' => strtoupper(trim($datos['nombre'])),
            'semestre' => $datos['semestre'],
            'estado' => $datos['estado'],
        ]);

        return redirect()
            ->route('admin.materias')
            ->with(
                'success',
                'Materia actualizada correctamente.'
            );
    }

    // ==========================================
    // ACTUALIZAR PERIODO ESCOLAR
    // ==========================================

    public function actualizarPeriodo(Request $request)
    {
        $anioActual = (int) now()->year;
        $anioMinimo = $anioActual - 10;
        $anioMaximo = $anioActual + 10;

        $datos = $request->validate([
            'mes_inicio' => ['required', 'integer', 'between:1,12'],
            'anio_inicio' => ['required', 'integer', "between:{$anioMinimo},{$anioMaximo}"],
            'mes_fin' => ['required', 'integer', 'between:1,12'],
            'anio_fin' => ['required', 'integer', "between:{$anioMinimo},{$anioMaximo}"],
            'confirmar_periodo_pasado' => ['nullable', 'boolean'],
        ], [
            'mes_inicio.required' => 'Selecciona el mes de inicio del periodo.',
            'anio_inicio.required' => 'Selecciona el año de inicio del periodo.',
            'mes_fin.required' => 'Selecciona el mes de fin del periodo.',
            'anio_fin.required' => 'Selecciona el año de fin del periodo.',
        ]);

        $mesInicio = (int) $datos['mes_inicio'];
        $anioInicio = (int) $datos['anio_inicio'];
        $mesFin = (int) $datos['mes_fin'];
        $anioFin = (int) $datos['anio_fin'];

        $totalMesesInicio = ($anioInicio * 12) + $mesInicio;
        $totalMesesFin = ($anioFin * 12) + $mesFin;

        if ($totalMesesFin <= $totalMesesInicio) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors([
                    'mes_fin' => 'La fecha de fin debe ser posterior a la fecha de inicio.',
                ]);
        }

        $duracionMeses = ($totalMesesFin - $totalMesesInicio) + 1;

        if ($duracionMeses < 4 || $duracionMeses > 6) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors([
                    'mes_fin' => "El periodo debe tener una duración semestral de entre 4 y 6 meses (el rango seleccionado abarca {$duracionMeses} meses).",
                ]);
        }

        // 3. Pedir confirmación explícita si el periodo inicia en un año anterior al actual
        if ($anioInicio < $anioActual && !$request->boolean('confirmar_periodo_pasado')) {
            return redirect()
                ->back()
                ->withInput()
                ->with('requiere_confirmacion_pasado', true)
                ->withErrors([
                    'anio_inicio' => "El año de inicio ({$anioInicio}) es anterior al año actual ({$anioActual}). Marca la casilla de confirmación si realmente deseas establecer un periodo pasado.",
                ]);
        }

        $meses = [
            1 => 'Enero',
            2 => 'Febrero',
            3 => 'Marzo',
            4 => 'Abril',
            5 => 'Mayo',
            6 => 'Junio',
            7 => 'Julio',
            8 => 'Agosto',
            9 => 'Septiembre',
            10 => 'Octubre',
            11 => 'Noviembre',
            12 => 'Diciembre',
        ];

        $periodoFormateado = "{$meses[$mesInicio]} {$anioInicio} - {$meses[$mesFin]} {$anioFin}";

        if (\Illuminate\Support\Facades\Schema::hasColumn('configuraciones', 'clave')
            && \Illuminate\Support\Facades\Schema::hasColumn('configuraciones', 'valor')) {
            \Illuminate\Support\Facades\DB::table('configuraciones')->updateOrInsert(
                ['clave' => 'periodo_actual'],
                ['valor' => $periodoFormateado, 'updated_at' => now()]
            );
        } elseif (\Illuminate\Support\Facades\Schema::hasColumn('configuraciones', 'llave')
            && \Illuminate\Support\Facades\Schema::hasColumn('configuraciones', 'valor')) {
            \Illuminate\Support\Facades\DB::table('configuraciones')->updateOrInsert(
                ['llave' => 'periodo_actual'],
                ['valor' => $periodoFormateado, 'updated_at' => now()]
            );
        } elseif (\Illuminate\Support\Facades\Schema::hasColumn('configuraciones', 'nombre')
            && \Illuminate\Support\Facades\Schema::hasColumn('configuraciones', 'valor')) {
            \Illuminate\Support\Facades\DB::table('configuraciones')->updateOrInsert(
                ['nombre' => 'periodo_actual'],
                ['valor' => $periodoFormateado, 'updated_at' => now()]
            );
        } elseif (\Illuminate\Support\Facades\Schema::hasColumn('configuraciones', 'periodo_actual')) {
            $existe = \Illuminate\Support\Facades\DB::table('configuraciones')->exists();
            if ($existe) {
                \Illuminate\Support\Facades\DB::table('configuraciones')->update([
                    'periodo_actual' => $periodoFormateado,
                    'updated_at' => now(),
                ]);
            } else {
                \Illuminate\Support\Facades\DB::table('configuraciones')->insert([
                    'periodo_actual' => $periodoFormateado,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        return redirect()
            ->route('admin.materias')
            ->with('success', "Periodo escolar actualizado correctamente a: {$periodoFormateado}.");
    }
}
