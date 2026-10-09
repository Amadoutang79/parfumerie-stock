<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    /**
     * Afficher le formulaire d'inscription.
     */
    public function show()
    {
        return view('auth.register');
    }

    /**
     * Traiter l'inscription.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        // Attribuer un rôle par défaut si Spatie est installé
        if (method_exists($user, 'assignRole') && \Spatie\Permission\Models\Role::where('name', 'Vendeur')->exists()) {
            $user->assignRole('Vendeur');
        }

        Auth::login($user);

        return redirect()->route('dashboard')
            ->with('success', 'Compte créé avec succès. Bienvenue !');
    }
}