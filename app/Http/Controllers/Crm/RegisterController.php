<?php

namespace App\Http\Controllers\Crm;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class RegisterController extends Controller
{
    public function show()
    {
        if (Auth::check() && Auth::user()->canAccessCrm()) {
            return redirect()->route('crm.dashboard');
        }

        return view('crm.auth.register');
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => User::ROLE_MEMBER,
            'status' => User::STATUS_PENDING,
        ]);

        return redirect()->route('login')->with(
            'status',
            "Your account has been created. An administrator needs to approve it before you can sign in — we'll email you at {$data['email']} once it's approved."
        );
    }
}
