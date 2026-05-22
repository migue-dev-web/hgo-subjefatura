<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function index()
    {
        $currentUser = Auth::user()->load('profile');
        $currentNivel = $currentUser->profile?->nivel ?? 1;
        $currentSeccion = $currentUser->profile?->seccion ?? 'ninguna';

        // Seguridad elemental: Solo Nivel 2 y 3 pueden entrar aquí
        if ($currentNivel < 2) {
            abort(403, 'No tienes permisos para gestionar usuarios.');
        }

        // Definimos la consulta base
        $query = User::with('profile')->orderBy('name');

        // REGLA DE NEGOCIO: Si es Nivel 2, limitamos su universo de usuarios
        if ($currentNivel === 2) {
            $query->whereHas('profile', function($q) use ($currentSeccion) {
                $q->where('seccion', $currentSeccion)
                  ->where('nivel', '<', 3); // No puede ver ni alterar a un Nivel 3 (Súper Admin) de su sección
            });
        }

        $users = $query->paginate(15);

        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        if ((Auth::user()->profile?->nivel ?? 1) < 2) {
            abort(403);
        }
        return view('admin.users.create');
    }

    public function store(Request $request)
    {
        $currentUser = Auth::user()->load('profile');
        $currentNivel = $currentUser->profile?->nivel ?? 1;
        $currentSeccion = $currentUser->profile?->seccion ?? 'ninguna';

        if ($currentNivel < 2) { abort(403); }

        // Reglas de validación base
        $rules = [
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];

        // REGLA DE NEGOCIO: Si es Nivel 3 elige todo. Si es Nivel 2, se fuerza su sección y nivel 1 de forma interna.
        if ($currentNivel === 3) {
            $rules['seccion'] = ['required', 'string', Rule::in(['general', 'rh', 'TOCO-cir', 'Quirofano','ConExt', 'autoclaves'])];
            $rules['nivel']   = ['required', 'integer', Rule::in([1, 2, 3])];
        }

        $data = $request->validate($rules);

        DB::transaction(function () use ($data, $currentNivel, $currentSeccion) {
            $user = User::create([
                'name'     => $data['name'],
                'email'    => $data['email'],
                'password' => Hash::make($data['password']),
            ]);

            // Definimos qué valores se van a guardar en el perfil
            $seccionFinal = ($currentNivel === 3) ? $data['seccion'] : $currentSeccion;
            $nivelFinal   = ($currentNivel === 3) ? $data['nivel'] : 1; // Nivel 2 solo crea operadores (Nivel 1)

            $user->profile()->create([
                'seccion' => $seccionFinal,
                'nivel'   => $nivelFinal,
            ]);
        });

        return redirect()->route('admin.users.index')->with('status', 'Usuario creado correctamente.');
    }

    public function edit(User $user)
    {
        $currentUser = Auth::user()->load('profile');
        $user->load('profile');

        // REGLA DE SEGURIDAD: Evitar que un Nivel 2 edite a alguien fuera de su sección o a un superior
        if ($currentUser->profile?->nivel === 2) {
            if ($user->profile?->seccion !== $currentUser->profile?->seccion || $user->profile?->nivel >= 3) {
                abort(403, 'No tienes permiso para editar este usuario.');
            }
        }

        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $currentUser = Auth::user()->load('profile');
        $currentNivel = $currentUser->profile?->nivel ?? 1;

        // Misma protección en el procesado del formulario
        if ($currentNivel === 2) {
            if ($user->profile?->seccion !== $currentUser->profile?->seccion || $user->profile?->nivel >= 3) {
                abort(403);
            }
        }

        $rules = [
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ];

        // Solo validamos sección y nivel si es un administrador Nivel 3
        if ($currentNivel === 3) {
            $rules['seccion'] = ['required', 'string', Rule::in(['general', 'rh', 'finanzas', 'sistemas'])];
            $rules['nivel']   = ['required', 'integer', Rule::in([1, 2, 3])];
        }

        $data = $request->validate($rules);

        DB::transaction(function () use ($data, $currentNivel, $user) {
            $user->name  = $data['name'];
            $user->email = $data['email'];

            if (!empty($data['password'])) {
                $user->password = Hash::make($data['password']);
            }
            $user->save();

            // REGLA DE NEGOCIO: Si es Nivel 3 actualiza campos de perfil. Si es Nivel 2, NO se modifican.
            if ($currentNivel === 3) {
                $user->profile()->updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'seccion' => $data['seccion'],
                        'nivel'   => $data['nivel'],
                    ]
                );
            }
        });

        return redirect()->route('admin.users.index')->with('status', 'Usuario actualizado correctamente.');
    }

    public function destroy(User $user)
    {
        $currentUser = Auth::user()->load('profile');

        if (Auth::id() === $user->id) {
            return redirect()->route('admin.users.index')->with('status', 'No puedes eliminar tu propio usuario.');
        }

        // Restricción para nivel 2 al eliminar
        if ($currentUser->profile?->nivel === 2) {
            if ($user->profile?->seccion !== $currentUser->profile?->seccion || $user->profile?->nivel >= 2) {
                abort(403, 'No tienes permisos para eliminar a este usuario.');
            }
        }

        $user->delete();
        return redirect()->route('admin.users.index')->with('status', 'Usuario eliminado correctamente.');
    }
}