<?php

namespace App\Http\Controllers\Admin;
use App\Models\User;
use App\Models\Rol;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class UsuarioController
{
    public function index(Request $request)
    {
        $buscar = $request->get('buscar');

        $usuarios = User::query()
            ->when($buscar, function ($query, $buscar) {
                return $query->where('nombre', 'LIKE', "%{$buscar}%")
                             ->orWhere('correo', 'LIKE', "%{$buscar}%");
            })
            ->orderBy('id_usuario', 'desc')
            ->paginate(10); 

        return view('admin.usuarios.index', compact('usuarios', 'buscar'));
    }

    public function create()
    {
        $roles = Rol::all();
        return view('admin.usuarios.creadorU', compact('roles'));
    }

    public function store(Request $request)
    {
        $request->validate(
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
            'unique:usuarios,correo',
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
            'exists:roles,id_rol',
        ],
    ],
    [
        'required' => 'El campo :attribute es obligatorio.',
        'string' => 'El campo :attribute debe contener texto.',
        'email' => 'El correo electrónico no tiene un formato válido.',
        'min' => 'El campo :attribute debe contener al menos :min caracteres.',
        'max' => 'El campo :attribute no debe superar :max caracteres.',
        'unique' => 'El :attribute ya está registrado.',
        'integer' => 'El campo :attribute debe ser un número entero.',
        'exists' => 'El rol seleccionado no existe.',
    ],
    [
        'nombre' => 'nombre completo',
        'correo' => 'correo electrónico',
        'contrasena' => 'contraseña',
        'id_rol' => 'rol',
    ]
);

        User::create([
    'nombre'     => $request->nombre,
    'correo'     => $request->correo,
    'contrasena' => Hash::make($request->contrasena),
    'id_rol'     => $request->id_rol,
    'estado'     => 'ACTIVO'
]);

        return redirect()->route('admin.usuarios')->with('success', 'Usuario creado correctamente.');
    }

    public function edit($id)
    {
        $usuario = User::findOrFail($id); 
        $roles = \App\Models\Rol::all(); 

        return view('admin.usuarios.editorU', compact('usuario', 'roles'));
    }


public function update(Request $request, $id)
{
    // Comprobar que el usuario existe.
    $usuario = User::findOrFail($id);

    // Validar los datos del formulario.
    $datos = $request->validate(
        [
            'nombre' => [
                'required',
                'string',
                'max:255',
            ],

            'correo' => [
                'required',
                'email',
                'max:255',
                Rule::unique('usuarios', 'correo')
                    ->ignore($usuario->id_usuario, 'id_usuario'),
            ],

            'id_rol' => [
                'required',
                'integer',
                Rule::exists('roles', 'id_rol'),
            ],

            'estado' => [
                'required',
                Rule::in(['ACTIVO', 'INACTIVO']),
            ],

            'password' => [
                'nullable',
                'string',
                'min:12',
                'max:255',
            ],
        ],
        [
            'required' => 'El campo :attribute es obligatorio.',
            'string' => 'El campo :attribute debe contener texto.',
            'email' => 'El correo electrónico no tiene un formato válido.',
            'max' => 'El campo :attribute no debe superar :max caracteres.',
            'min' => 'El campo :attribute debe contener al menos :min caracteres.',
            'unique' => 'El :attribute ya está registrado.',
            'integer' => 'El campo :attribute debe ser un número entero.',
            'exists' => 'El rol seleccionado no existe.',
            'in' => 'El estado seleccionado no es válido.',
        ],
        [
            'nombre' => 'nombre completo',
            'correo' => 'correo electrónico',
            'id_rol' => 'rol',
            'estado' => 'estado',
            'password' => 'contraseña',
        ]
    );

    // Actualización protegida con una transacción.
    DB::transaction(function () use ($usuario, $datos) {

        $usuario = User::where('id_usuario', $usuario->id_usuario)
            ->lockForUpdate()
            ->firstOrFail();

        // Impedir que el administrador desactive
        // su propia cuenta o se quite el rol.
        if (
            (int) Auth::id() === (int) $usuario->id_usuario
            && (
                (int) $datos['id_rol'] !== 1
                || $datos['estado'] !== 'ACTIVO'
            )
        ) {
            throw ValidationException::withMessages([
                'estado' => 'No puedes desactivar tu propia cuenta ni quitarte el rol de administrador.',
            ]);
        }

        // Proteger al último administrador activo.
        if (
            (int) $usuario->id_rol === 1
            && $usuario->estado === 'ACTIVO'
            && (
                (int) $datos['id_rol'] !== 1
                || $datos['estado'] !== 'ACTIVO'
            )
        ) {
            $administradoresActivos = User::where('id_rol', 1)
                ->where('estado', 'ACTIVO')
                ->count();

            if ($administradoresActivos <= 1) {
                throw ValidationException::withMessages([
                    'estado' => 'No puedes desactivar o cambiar el rol del último administrador activo.',
                ]);
            }
        }

        // Actualizar información general.
        $usuario->nombre = $datos['nombre'];
        $usuario->correo = $datos['correo'];
        $usuario->id_rol = $datos['id_rol'];
        $usuario->estado = $datos['estado'];

        // Cambiar contraseña únicamente si se proporcionó.
        if (!empty($datos['password'])) {
            $usuario->contrasena = Hash::make($datos['password']);
        }

        $usuario->save();
    });

    return redirect()
        ->route('admin.usuarios')
        ->with('success', 'Usuario modificado correctamente.');
}

}
