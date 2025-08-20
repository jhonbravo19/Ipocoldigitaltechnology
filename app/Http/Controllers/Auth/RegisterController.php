<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class RegisterController extends Controller
{
    use RegistersUsers;

    protected $redirectTo = '/';

    public function __construct()
    {
        $this->middleware('guest');
    }

    protected function validator(array $data)
    {
        return Validator::make($data, [
            'first_name' => ['required', 'string', 'regex:/^[A-Za-zÀ-ÿñÑ\s]+$/u', 'max:100'],
            'last_name'  => ['required', 'string', 'regex:/^[A-Za-zÀ-ÿñÑ\s]+$/u', 'max:100'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'identification_type' => ['required', 'in:CC,CE,PA'],
            'identification_number' => [
                'required', 
                'string', 
                'regex:/^[0-9]+$/', 
                'max:50',
                function ($attribute, $value, $fail) use ($data) {
                    if (User::identificationExists($data['identification_type'] ?? '', $value)) {
                        $fail('Ya existe un usuario con esta identificación.');
                    }
                },
            ],
            'phone' => ['nullable', 'string', 'regex:/^[0-9\-\+\(\)\s]+$/', 'max:20'],
            'address' => ['nullable', 'string', 'max:500'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'first_name.regex' => 'El nombre solo puede contener letras y espacios.',
            'last_name.regex' => 'El apellido solo puede contener letras y espacios.',
            'identification_number.regex' => 'El número de identificación solo puede contener números.',
            'phone.regex' => 'El teléfono solo puede contener números y símbolos básicos.',
        ]);
    }

    protected function create(array $data)
    {
        return User::create([
            'first_name' => strtoupper(trim($data['first_name'])),
            'last_name' => strtoupper(trim($data['last_name'])),
            'email' => strtolower(trim($data['email'])),
            'password' => Hash::make($data['password']),
            'identification_type' => $data['identification_type'],
            'identification_number' => $data['identification_number'],
            'phone' => $data['phone'] ?? null,
            'address' => $data['address'] ?? null,
            'role' => 'user',
        ]);
    }

    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    protected function registered(Request $request, $user)
    {
        if ($user->role === 'admin') {
            return redirect()->route('admin.dashboard');
        }

        return redirect()->route('user.dashboard');
    }
}
