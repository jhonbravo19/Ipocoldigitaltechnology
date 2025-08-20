<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    public function show()
    {
        $user = Auth::user();
        return view('user.profile', compact('user'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'first_name' => ['required', 'regex:/^[A-ZÁÉÍÓÚÜÑ ]+$/i', 'max:50'],
            'last_name'  => ['required', 'regex:/^[A-ZÁÉÍÓÚÜÑ ]+$/i', 'max:50'],
            'phone'      => ['nullable', 'regex:/^[0-9]{7,10}$/'],
            'address'    => 'nullable|string|max:150',
            'email'      => 'required|email|unique:users,email,' . $user->id,
            'current_password' => ['nullable', 'required_with:password', 'string'],
            'password'   => ['nullable', 'confirmed', Password::min(8)->letters()->numbers()],
        ], [
            'first_name.required' => 'El nombre es obligatorio.',
            'first_name.regex'    => 'El nombre solo puede contener letras y espacios.',
            'last_name.required'  => 'El apellido es obligatorio.',
            'last_name.regex'     => 'El apellido solo puede contener letras y espacios.',
            'phone.regex'         => 'El teléfono debe contener entre 7 y 10 números.',
            'email.required'      => 'El correo electrónico es obligatorio.',
            'email.email'         => 'Debes ingresar un correo electrónico válido.',
            'email.unique'        => 'El correo ya está registrado.',
            'current_password.required_with' => 'Debes ingresar tu contraseña actual para cambiarla.',
            'password.confirmed'  => 'La confirmación de la contraseña no coincide.',
            'password.min'        => 'La contraseña debe tener al menos 8 caracteres.',
        ]);

        $data = $request->only(['first_name', 'last_name', 'phone', 'address', 'email']);

        $data['first_name'] = strtoupper($data['first_name']);
        $data['last_name'] = strtoupper($data['last_name']);

        if ($request->filled('password')) {
            if (!Hash::check($request->current_password, $user->password)) {
                return back()->withErrors(['current_password' => 'La contraseña actual no es correcta.']);
            }
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return redirect()->route('user.profile')->with('success', 'Perfil actualizado correctamente.');
    }

    public function edit()
    {
        $user = Auth::user();
        return view('user.settings', compact('user'));
    }

}
