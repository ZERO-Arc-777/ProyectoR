<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\Rol;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class UsuarioController
{
    // ==========================================
    // CONSULTAR USUARIOS
    // ==========================================

    public function index(Request $request)
    {
        $datos = $request->validate([
            'buscar' => ['nullable', 'string', 'max:100'],
            'rol' => ['nullable', 'integer', Rule::exists('roles', 'id_rol')],
            'estado' => ['nullable', 'string', Rule::in(['ACTIVO', 'INACTIVO'])],
        ]);

        $buscar = trim($datos['buscar'] ?? '');
        $rol = isset($datos['rol']) ? (int) $datos['rol'] : null;
        $estado = $datos['estado'] ?? null;

        $usuarios = User::with('rol')
            ->when($buscar !== '', function ($query) use ($buscar) {
                $query->where(function ($q) use ($buscar) {
                    $q->where('nombre', 'LIKE', "%{$buscar}%")
                        ->orWhere('correo', 'LIKE', "%{$buscar}%");
                });
            })
            ->when($rol !== null, function ($query) use ($rol) {
                $query->where('id_rol', $rol);
            })
            ->when($estado !== null, function ($query) use ($estado) {
                $query->where('estado', $estado);
            })
            ->orderBy('id_usuario', 'desc')
            ->paginate(10)
            ->withQueryString();

        return view(
            'admin.usuarios.index',
            compact('usuarios', 'buscar', 'rol', 'estado')
        );
    }

    // ==========================================
    // MOSTRAR FORMULARIO DE REGISTRO
    // ==========================================

    public function create()
    {
        $roles = Rol::all();

        return view(
            'admin.usuarios.creadorU',
            compact('roles')
        );
    }

    // ==========================================
    // REGISTRAR USUARIO
    // ==========================================

    public function store(Request $request)
    {
        $datos = $request->validate(
            [
                'nombre' => [
                    'required',
                    'string',
                    'max:100',
                ],

                'correo' => [
                    'required',
                    'email',
                    'max:100',
                    Rule::unique('usuarios', 'correo'),
                ],

                'contrasena' => [
                    'required',
                    'string',
                    'min:12',
                    'max:255',
                ],

                'id_rol' => [
                    'required',
                    'integer',
                    Rule::exists('roles', 'id_rol'),
                ],
            ],
            $this->mensajesValidacion(),
            $this->atributosValidacion()
        );

        User::create([
            'nombre' => $datos['nombre'],
            'correo' => $datos['correo'],
            'contrasena' => Hash::make($datos['contrasena']),
            'id_rol' => $datos['id_rol'],
            'estado' => 'ACTIVO',
        ]);

        return redirect()
            ->route('admin.usuarios')
            ->with('success', 'Usuario creado correctamente.');
    }

    // ==========================================
    // MOSTRAR FORMULARIO DE EDICION
    // ==========================================

    public function edit($id)
    {
        $usuario = User::findOrFail($id);

        $roles = Rol::all();

        return view(
            'admin.usuarios.editorU',
            compact('usuario', 'roles')
        );
    }

    // ==========================================
    // ACTUALIZAR USUARIO
    // ==========================================

    public function update(Request $request, $id)
    {
        // Comprobar que el usuario existe.
        $usuario = User::findOrFail($id);

        // Aceptar ambos nombres de campo utilizados
        // por las versiones de los formularios.
        $datos = $request->validate(
            [
                'nombre' => [
                    'required',
                    'string',
                    'max:100',
                ],

                'correo' => [
                    'required',
                    'email',
                    'max:100',
                    Rule::unique('usuarios', 'correo')
                        ->ignore($usuario->id_usuario, 'id_usuario'),
                ],

                'id_rol' => [
                    'required',
                    'integer',
                    Rule::exists('roles', 'id_rol'),
                ],

                'estado' => [
                    'sometimes',
                    'required',
                    Rule::in(['ACTIVO', 'INACTIVO']),
                ],

                'contrasena' => [
                    'nullable',
                    'string',
                    'min:12',
                    'max:255',
                ],

                'password' => [
                    'nullable',
                    'string',
                    'min:12',
                    'max:255',
                ],
            ],
            $this->mensajesValidacion(),
            $this->atributosValidacion()
        );

        // Evitar recibir dos contraseñas distintas
        // en una misma solicitud.
        if (
            !empty($datos['contrasena']) &&
            !empty($datos['password'])
        ) {
            throw ValidationException::withMessages([
                'contrasena' =>
                    'Debes proporcionar la contraseña en un solo campo.',
            ]);
        }

        // Validar que se está utilizando una cuenta
        // de administrador autenticada.
        $idAdministrador = Auth::id();

        DB::transaction(function () use (
            $usuario,
            $datos,
            $idAdministrador
        ) {
            // Bloquear el registro durante la actualización.
            $usuario = User::where(
                'id_usuario',
                $usuario->id_usuario
            )
                ->lockForUpdate()
                ->firstOrFail();

            $nuevoRol = (int) $datos['id_rol'];

            // Conservar el estado actual cuando el
            // formulario no envía este campo.
            $nuevoEstado = $datos['estado']
                ?? $usuario->estado;

            // Impedir que un administrador modifique
            // su propia cuenta para quitarse el acceso.
            if (
                (int) $idAdministrador ===
                    (int) $usuario->id_usuario
                &&
                (
                    $nuevoRol !== 1 ||
                    $nuevoEstado !== 'ACTIVO'
                )
            ) {
                throw ValidationException::withMessages([
                    'estado' =>
                        'No puedes desactivar tu propia cuenta ni quitarte el rol de administrador.',
                ]);
            }

            // Impedir la desactivación del último
            // administrador activo del sistema.
            if (
                (int) $usuario->id_rol === 1
                &&
                $usuario->estado === 'ACTIVO'
                &&
                (
                    $nuevoRol !== 1 ||
                    $nuevoEstado !== 'ACTIVO'
                )
            ) {
                $administradoresActivos = User::where(
                    'id_rol',
                    1
                )
                    ->where('estado', 'ACTIVO')
                    ->count();

                if ($administradoresActivos <= 1) {
                    throw ValidationException::withMessages([
                        'estado' =>
                            'No puedes desactivar o cambiar el rol del último administrador activo.',
                    ]);
                }
            }

            // Actualizar solamente los datos validados.
            $usuario->nombre = $datos['nombre'];
            $usuario->correo = $datos['correo'];
            $usuario->id_rol = $nuevoRol;
            $usuario->estado = $nuevoEstado;

            // Detectar si se solicitó un cambio
            // de contraseña.
            $nuevaContrasena = $datos['contrasena']
                ?? $datos['password']
                ?? null;

            if (
                is_string($nuevaContrasena) &&
                $nuevaContrasena !== ''
            ) {
                $usuario->contrasena = Hash::make(
                    $nuevaContrasena
                );
            }

            $usuario->save();
        });

        return redirect()
            ->route('admin.usuarios')
            ->with(
                'success',
                'Usuario modificado correctamente.'
            );
    }

    // ==========================================
    // MENSAJES DE VALIDACION
    // ==========================================

    private function mensajesValidacion(): array
    {
        return [
            'required' =>
                'El campo :attribute es obligatorio.',

            'string' =>
                'El campo :attribute debe contener texto.',

            'email' =>
                'El correo electrónico no tiene un formato válido.',

            'min' =>
                'El campo :attribute debe contener al menos :min caracteres.',

            'max' =>
                'El campo :attribute no debe superar :max caracteres.',

            'unique' =>
                'El :attribute ya está registrado.',

            'integer' =>
                'El campo :attribute debe ser un número entero.',

            'exists' =>
                'El rol seleccionado no existe.',

            'in' =>
                'El estado seleccionado no es válido.',
        ];
    }

    // ==========================================
    // NOMBRES DE LOS CAMPOS
    // ==========================================

    private function atributosValidacion(): array
    {
        return [
            'nombre' => 'nombre completo',
            'correo' => 'correo electrónico',
            'contrasena' => 'contraseña',
            'password' => 'contraseña',
            'id_rol' => 'rol',
            'estado' => 'estado',
        ];
    }
}
