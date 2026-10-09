<?php
namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@parfumerie.test'],
            ['name' => 'Administrateur', 'password' => Hash::make('password')]
        );
        $admin->assignRole('Administrateur');

        $gerant = User::firstOrCreate(
            ['email' => 'gerant@parfumerie.test'],
            ['name' => 'Gérant', 'password' => Hash::make('password')]
        );
        $gerant->assignRole('Gérant');

        $vendeur = User::firstOrCreate(
            ['email' => 'vendeur@parfumerie.test'],
            ['name' => 'Vendeur', 'password' => Hash::make('password')]
        );
        $vendeur->assignRole('Vendeur');
    }
}