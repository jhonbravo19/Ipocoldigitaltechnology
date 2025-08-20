<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            if (auth()->user()->role !== 'admin') {
                abort(403, 'No tienes permisos de administrador');
            }
            return $next($request);
        });
    }

    public function show()
    {
        $user = Auth::user();
        return view('admin.profile.show', compact('user'));
    }

    public function edit()
    {
        $user = Auth::user();
        return view('admin.profile.edit', compact('user'));
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
        ]);

        $data = $request->only(['first_name', 'last_name', 'phone', 'address', 'email']);
        $data['first_name'] = strtoupper($data['first_name']);
        $data['last_name']  = strtoupper($data['last_name']);

        if ($request->filled('password')) {
            if (!Hash::check($request->current_password, $user->password)) {
                return back()->withErrors(['current_password' => 'La contraseña actual no es correcta.']);
            }
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return redirect()->route('admin.profile.show')->with('success', 'Perfil de administrador actualizado correctamente.');
    }
}
