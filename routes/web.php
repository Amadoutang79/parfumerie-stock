<?php

use App\Http\Controllers\{
    DashboardController,
    ProductController,
    BrandController,
    CategoryController,
    StockMovementController,
    SaleController,
    PurchaseController,
    SupplierController,
    CustomerController,
    ReportController,
    UserController,
    SettingController
};
use App\Http\Middleware\ApplyCompanyTimezone;
use Illuminate\Support\Facades\Route;

require __DIR__.'/auth.php';

Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : view('welcome');
})->name('home');

Route::middleware(['auth', ApplyCompanyTimezone::class])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->middleware('can:dashboard.view')
        ->name('dashboard');

    // Parfums
    Route::middleware('can:products.view')->group(function () {
        Route::get('products', [ProductController::class, 'index'])->name('products.index');
    });
    Route::middleware('can:products.create')->group(function () {
        Route::get('products/create', [ProductController::class, 'create'])->name('products.create');
        Route::post('products', [ProductController::class, 'store'])->name('products.store');
    });
    Route::get('products/{product}', [ProductController::class, 'show'])
        ->middleware('can:products.view')
        ->name('products.show');
    Route::middleware('can:products.edit')->group(function () {
        Route::get('products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
        Route::put('products/{product}', [ProductController::class, 'update'])->name('products.update');
        Route::patch('products/{product}', [ProductController::class, 'update']);
    });
    Route::delete('products/{product}', [ProductController::class, 'destroy'])
        ->middleware('can:products.delete')
        ->name('products.destroy');

    // Marques
    Route::get('brands', [BrandController::class, 'index'])->middleware('can:products.view')->name('brands.index');
    Route::post('brands', [BrandController::class, 'store'])->middleware('can:products.create')->name('brands.store');
    Route::put('brands/{brand}', [BrandController::class, 'update'])->middleware('can:products.create')->name('brands.update');
    Route::patch('brands/{brand}', [BrandController::class, 'update'])->middleware('can:products.create');
    Route::delete('brands/{brand}', [BrandController::class, 'destroy'])->middleware('can:products.create')->name('brands.destroy');

    // Catégories
    Route::get('categories', [CategoryController::class, 'index'])->middleware('can:products.view')->name('categories.index');
    Route::post('categories', [CategoryController::class, 'store'])->middleware('can:products.create')->name('categories.store');
    Route::put('categories/{category}', [CategoryController::class, 'update'])->middleware('can:products.create')->name('categories.update');
    Route::patch('categories/{category}', [CategoryController::class, 'update'])->middleware('can:products.create');
    Route::delete('categories/{category}', [CategoryController::class, 'destroy'])->middleware('can:products.create')->name('categories.destroy');

    // Stock
    Route::get('stock', [StockMovementController::class, 'index'])->middleware('can:stock.view')->name('stock.index');
    Route::get('stock/lots', [StockMovementController::class, 'lots'])->middleware('can:stock.view')->name('stock.lots');
    Route::get('stock/create', [StockMovementController::class, 'create'])->middleware('can:stock.adjust')->name('stock.create');
    Route::post('stock', [StockMovementController::class, 'store'])->middleware('can:stock.adjust')->name('stock.store');

    // Ventes
    Route::get('sales', [SaleController::class, 'index'])->middleware('can:sales.view')->name('sales.index');
    Route::get('sales-create', [SaleController::class, 'create'])->middleware('can:sales.create')->name('sales.create');
    Route::post('sales', [SaleController::class, 'store'])->middleware('can:sales.create')->name('sales.store');
    Route::get('sales/{sale}', [SaleController::class, 'show'])->middleware('can:sales.view')->name('sales.show');
    Route::get('sales/{sale}/ticket', [SaleController::class, 'ticket'])->middleware('can:sales.view')->name('sales.ticket');
    Route::delete('sales/{sale}', [SaleController::class, 'destroy'])->middleware('can:sales.delete')->name('sales.destroy');

    // Achats
    Route::get('purchases', [PurchaseController::class, 'index'])->middleware('can:purchases.view')->name('purchases.index');
    Route::get('purchases/create', [PurchaseController::class, 'create'])->middleware('can:purchases.create')->name('purchases.create');
    Route::post('purchases', [PurchaseController::class, 'store'])->middleware('can:purchases.create')->name('purchases.store');
    Route::get('purchases/{purchase}', [PurchaseController::class, 'show'])->middleware('can:purchases.view')->name('purchases.show');
    Route::delete('purchases/{purchase}', [PurchaseController::class, 'destroy'])->middleware('can:purchases.delete')->name('purchases.destroy');

    // Fournisseurs
    Route::get('suppliers', [SupplierController::class, 'index'])->middleware('can:suppliers.view')->name('suppliers.index');
    Route::get('suppliers/create', [SupplierController::class, 'create'])->middleware('can:suppliers.manage')->name('suppliers.create');
    Route::post('suppliers', [SupplierController::class, 'store'])->middleware('can:suppliers.manage')->name('suppliers.store');
    Route::get('suppliers/{supplier}', [SupplierController::class, 'show'])->middleware('can:suppliers.view')->name('suppliers.show');
    Route::get('suppliers/{supplier}/edit', [SupplierController::class, 'edit'])->middleware('can:suppliers.manage')->name('suppliers.edit');
    Route::put('suppliers/{supplier}', [SupplierController::class, 'update'])->middleware('can:suppliers.manage')->name('suppliers.update');
    Route::patch('suppliers/{supplier}', [SupplierController::class, 'update'])->middleware('can:suppliers.manage');
    Route::delete('suppliers/{supplier}', [SupplierController::class, 'destroy'])->middleware('can:suppliers.manage')->name('suppliers.destroy');

    // Clients
    Route::get('customers', [CustomerController::class, 'index'])->middleware('can:customers.view')->name('customers.index');
    Route::post('customers', [CustomerController::class, 'store'])->middleware('can:customers.manage')->name('customers.store');
    Route::get('customers/{customer}', [CustomerController::class, 'show'])->middleware('can:customers.view')->name('customers.show');
    Route::put('customers/{customer}', [CustomerController::class, 'update'])->middleware('can:customers.manage')->name('customers.update');
    Route::patch('customers/{customer}', [CustomerController::class, 'update'])->middleware('can:customers.manage');
    Route::delete('customers/{customer}', [CustomerController::class, 'destroy'])->middleware('can:customers.manage')->name('customers.destroy');

    // Rapports
    Route::get('reports', [ReportController::class, 'index'])->middleware('can:reports.view')->name('reports.index');
    Route::get('reports/sales/export', [ReportController::class, 'salesExport'])
        ->middleware('can:reports.view')
        ->name('reports.sales.export');
    Route::get('reports/stock/export', [ReportController::class, 'stockExport'])
        ->middleware('can:reports.view')
        ->name('reports.stock.export');

    // Utilisateurs
    Route::middleware('can:users.manage')->group(function () {
        Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
        Route::put('settings', [SettingController::class, 'update'])->name('settings.update');
        Route::delete('settings/logo', [SettingController::class, 'destroyLogo'])->name('settings.logo.destroy');

        Route::get('users', [UserController::class, 'index'])->name('users.index');
        Route::get('users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('users', [UserController::class, 'store'])->name('users.store');
        Route::get('users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::patch('users/{user}', [UserController::class, 'update']);
        Route::delete('users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    });

    Route::get('profile', [\App\Http\Controllers\ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('profile', [\App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
    Route::delete('profile', [\App\Http\Controllers\ProfileController::class, 'destroy'])->name('profile.destroy');
});

/*
|--------------------------------------------------------------------------
| ⚠️ ROUTE TEMPORAIRE - SETUP COMPLET + ADMIN PERSONNALISÉ
|--------------------------------------------------------------------------
| Cette route :
| 1. Crée les rôles et permissions
| 2. Crée / met à jour l'admin avec mkcollection00223@gmail.com / Bamako223
| 3. Crée le Gérant et le Vendeur
|
| ⚠️ À SUPPRIMER après usage !
*/
Route::get('/update-admin-once', function () {
    try {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Créer les permissions
        $perms = [
            'dashboard.view',
            'products.view','products.create','products.edit','products.delete',
            'stock.view','stock.create','stock.adjust',
            'sales.view','sales.create','sales.delete',
            'purchases.view','purchases.create','purchases.delete',
            'suppliers.view','suppliers.manage',
            'customers.view','customers.manage',
            'reports.view','users.manage',
        ];
        foreach ($perms as $p) {
            \Spatie\Permission\Models\Permission::firstOrCreate(['name' => $p]);
        }

        // 2. Créer les rôles
        $admin   = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Administrateur']);
        $gerant  = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Gérant']);
        $vendeur = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Vendeur']);

        $admin->syncPermissions(\Spatie\Permission\Models\Permission::all());

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
            'dashboard.view','products.view',
            'sales.view','sales.create',
            'customers.view','customers.manage',
        ]);

        // 3. Créer / mettre à jour l'ADMIN
        $adminUser = \App\Models\User::updateOrCreate(
            ['email' => 'mkcollection00223@gmail.com'],
            [
                'name'     => 'Administrateur',
                'password' => \Illuminate\Support\Facades\Hash::make('Bamako223'),
            ]
        );
        $adminUser->syncRoles(['Administrateur']);

        // 4. Créer les 2 autres utilisateurs
        $gerantUser = \App\Models\User::updateOrCreate(
            ['email' => 'gerant@parfumerie.test'],
            ['name' => 'Gérant', 'password' => \Illuminate\Support\Facades\Hash::make('123456789')]
        );
        $gerantUser->syncRoles(['Gérant']);

        $vendeurUser = \App\Models\User::updateOrCreate(
            ['email' => 'vendeur@parfumerie.test'],
            ['name' => 'Vendeur', 'password' => \Illuminate\Support\Facades\Hash::make('123456789')]
        );
        $vendeurUser->syncRoles(['Vendeur']);

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        return '✅ OK ! Connecte-toi avec <strong>mkcollection00223@gmail.com</strong> / <strong>Bamako223</strong> — ⚠️ SUPPRIME CETTE ROUTE MAINTENANT !';
    } catch (\Throwable $e) {
        return '❌ ' . $e->getMessage() . ' — Ligne ' . $e->getLine();
    }
});

/*
|--------------------------------------------------------------------------
| ⚠️ ROUTE TEMPORAIRE - CHANGER EMAIL + MOT DE PASSE ADMIN (FINAL)
|--------------------------------------------------------------------------
| ⚠️ À SUPPRIMER IMMÉDIATEMENT APRÈS USAGE !
*/
Route::get('/update-admin-final', function () {
    try {
        // Cherche l'admin actuel (quel que soit son ancien email)
        $user = \App\Models\User::where('email', 'mkcollection00223@gmail.com')->first() 
             ?? \App\Models\User::where('email', 'admin@parfumerie.test')->first() 
             ?? \App\Models\User::first();

        if (!$user) {
            return '❌ Aucun utilisateur trouvé en base.';
        }

        $user->email    = 'amadoutangara91@gmail.com';
        $user->password = \Illuminate\Support\Facades\Hash::make('123456789');
        $user->save();

        return '✅ OK ! Connecte-toi avec <strong>amadoutangara91@gmail.com</strong> / <strong>123456789</strong> — ⚠️ SUPPRIME CETTE ROUTE MAINTENANT !';
    } catch (\Throwable $e) {
        return '❌ ' . $e->getMessage() . ' — Ligne ' . $e->getLine();
    }
});