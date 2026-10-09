<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            ['name' => 'Administrateur', 'email' => 'admin@parfumerie.test',   'role' => 'Administrateur'],
            ['name' => 'Gérant',         'email' => 'gerant@parfumerie.test',  'role' => 'Gérant'],
            ['name' => 'Vendeur',        'email' => 'vendeur@parfumerie.test', 'role' => 'Vendeur'],
        ];

        foreach ($users as $u) {
            $user = User::firstOrCreate(
                ['email' => $u['email']],
                ['name' => $u['name'], 'password' => Hash::make('123456789')]
            );

            // Mettre à jour le mot de passe même si l'utilisateur existait déjà
            $user->update(['password' => Hash::make('123456789')]);

            if (method_exists($user, 'assignRole')
                && \Spatie\Permission\Models\Role::where('name', $u['role'])->exists()) {
                $user->assignRole($u['role']);
            }
        }

        $this->command->info('✅ 3 comptes créés (mot de passe : 123456789)');
    }
}