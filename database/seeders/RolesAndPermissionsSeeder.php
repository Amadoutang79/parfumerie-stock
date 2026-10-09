<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'dashboard.view',
            // Parfums
            'products.view','products.create','products.edit','products.delete',
            // Stock
            'stock.view','stock.create','stock.adjust',
            // Ventes
            'sales.view','sales.create','sales.delete',
            // Achats
            'purchases.view','purchases.create','purchases.delete',
            // Fournisseurs
            'suppliers.view','suppliers.manage',
            // Clients
            'customers.view','customers.manage',
            // Rapports
            'reports.view',
            // Utilisateurs
            'users.manage',
        ];

        foreach ($permissions as $p) {
            Permission::firstOrCreate(['name' => $p]);
        }

        $admin  = Role::firstOrCreate(['name' => 'Administrateur']);
        $gerant = Role::firstOrCreate(['name' => 'Gérant']);
        $vendeur= Role::firstOrCreate(['name' => 'Vendeur']);

        $admin->syncPermissions(Permission::all());

        $gerant->syncPermissions([
            'dashboard.view',
            'products.view','products.create','products.edit',
            'stock.view','stock.create','stock.adjust',
            'sales.view','sales.create',
            'purchases.view','purchases.create',
            'suppliers.view','suppliers.manage',
            'customers.view','customers.manage',
            'reports.view',
        ]);

        $vendeur->syncPermissions([
            'dashboard.view',
            'products.view',
            'sales.view','sales.create',
            'customers.view','customers.manage',
        ]);
    }
}