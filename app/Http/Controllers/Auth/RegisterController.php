<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class RegisterController extends Controller
{
    public function create()
    {
        return view('auth.register');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:160', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:20'],
            'account_type' => ['required', 'in:b2c,b2b'],
            'company_name' => ['nullable', 'required_if:account_type,b2b', 'string', 'max:160'],
            'kra_pin' => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'type' => $data['account_type'],
            'company_name' => $data['company_name'] ?? null,
            'kra_pin' => $data['kra_pin'] ?? null,
            'b2b_status' => $data['account_type'] === 'b2b' ? 'pending' : 'none',
            'password' => Hash::make($data['password']),
        ]);

        event(new Registered($user));
        Auth::login($user);

        return redirect()->route('account')->with('status',
            $user->type === 'b2b'
                ? 'Account created. Your business account is pending approval — you can shop at standard prices meanwhile.'
                : 'Welcome to Pestone Technologies!'
        );
    }
}
