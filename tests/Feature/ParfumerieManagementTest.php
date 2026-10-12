<?php

use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Setting;
use App\Models\StockMovement;
use App\Models\StockBatch;
use App\Services\StockInventory;
use Database\Seeders\DemoDataSeeder;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

function parfumerieTestProduct(int $stock = 5): Product
{
    $product = Product::create([
        'name' => 'Parfum test',
        'reference' => 'PRF-TEST-001',
        'gender' => 'unisexe',
        'volume' => 50,
        'purchase_price' => 40,
        'selling_price' => 100,
        'stock_quantity' => 0,
        'alert_threshold' => 2,
        'is_active' => true,
    ]);

    if ($stock > 0) {
        StockInventory::receive($product, $stock, 40, 'TEST-OPENING');
    }

    return $product;
}

function parfumerieUserWithRole(string $role): User
{
    $user = User::factory()->create();
    $user->assignRole($role);

    return $user;
}

test('authorized staff can open the dashboard and SQLite reports and download both exports', function () {
    $user = parfumerieUserWithRole('Administrateur');

    $this->actingAs($user)->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Évolution des ventes')
        ->assertSee('Ajouter un parfum')
        ->assertSee('7 jours');
    $this->get(route('dashboard', ['days' => 30]))->assertOk()->assertSee('30 jours');
    $this->get(route('dashboard', ['days' => 90]))->assertOk()->assertSee('90 jours');
    $this->get(route('dashboard', ['days' => 14]))->assertNotFound();
    $this->get(route('reports.index'))->assertOk();
    $this->get(route('reports.sales.export'))->assertOk();
    $this->get(route('reports.stock.export'))->assertOk();
});

test('welcome page describes the current application accurately', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee('Aperçu de démonstration')
        ->assertSee('export CSV.')
        ->assertDontSee('Laravel 12')
        ->assertDontSee('Sauvage Dior');
});

test('dashboard shows products that actually need replenishment', function () {
    $admin = parfumerieUserWithRole('Administrateur');
    $product = parfumerieTestProduct(stock: 1);

    $this->actingAs($admin)->get(route('dashboard'))
        ->assertOk()
        ->assertSee($product->name)
        ->assertSee('1 restant(s)')
        ->assertSee(route('products.show', $product));
});

test('all catalogue and management lists render for an administrator', function () {
    $admin = parfumerieUserWithRole('Administrateur');

    foreach ([
        route('products.index'),
        route('brands.index'),
        route('categories.index'),
        route('stock.index'),
        route('sales.index'),
        route('sales.create'),
        route('purchases.index'),
        route('purchases.create'),
        route('customers.index'),
        route('suppliers.index'),
        route('reports.index'),
    ] as $url) {
        $this->actingAs($admin)->get($url)->assertOk();
    }

    $this->get(route('sales.create'))->assertSee('Montant remis');
    $this->get(route('sales.create'))
        ->assertSee('Scanner avec la caméra')
        ->assertSee('douchette USB');
    $this->get(route('purchases.create'))->assertSee('Montant payé');
});

test('brands and categories can be searched and maintained from their catalogues', function () {
    $manager = parfumerieUserWithRole('Gérant');

    $this->actingAs($manager)->post(route('brands.store'), [
        'name' => 'Maison Test',
        'description' => 'Collection boisée',
    ])->assertRedirect();

    $this->post(route('categories.store'), [
        'name' => 'Boisé',
        'description' => 'Notes chaudes',
    ])->assertRedirect();

    $this->get(route('brands.index', ['search' => 'Maison Test']))
        ->assertOk()
        ->assertSee('Maison Test');
    $this->get(route('categories.index', ['search' => 'Boisé']))
        ->assertOk()
        ->assertSee('Boisé');
});

test('sale ticket is printable and includes transaction details', function () {
    $seller = parfumerieUserWithRole('Vendeur');
    Setting::current()->update(['company_name' => 'Maison Parfum Test']);
    $sale = Sale::create([
        'reference' => 'VTE-TEST-001',
        'user_id' => $seller->id,
        'subtotal' => 100,
        'discount' => 0,
        'total' => 100,
        'profit' => 60,
        'payment_method' => 'cash',
        'paid_amount' => 100,
        'change_amount' => 0,
        'sold_at' => now(),
    ]);
    SaleItem::create([
        'sale_id' => $sale->id,
        'product_name' => 'Parfum test',
        'quantity' => 1,
        'price' => 100,
        'purchase_price' => 40,
        'subtotal' => 100,
    ]);

    $this->actingAs($seller)->get(route('sales.ticket', $sale))
        ->assertOk()
        ->assertSee('Imprimer le ticket')
        ->assertSee('VTE-TEST-001')
        ->assertSee('Maison Parfum Test')
        ->assertSee('Parfum test');
});

test('role permissions protect administrative pages', function () {
    $seller = parfumerieUserWithRole('Vendeur');

    $this->actingAs($seller)->get(route('products.index'))->assertOk();
    $this->get(route('users.index'))->assertForbidden();
    $this->get(route('reports.index'))->assertForbidden();
});

test('sale submission cannot oversell a product repeated across cart lines', function () {
    $seller = parfumerieUserWithRole('Vendeur');
    $product = parfumerieTestProduct(stock: 2);

    $this->actingAs($seller)->post(route('sales.store'), [
        'items' => [
            ['product_id' => $product->id, 'quantity' => 2, 'price' => 1],
            ['product_id' => $product->id, 'quantity' => 1, 'price' => 1],
        ],
        'payment_method' => 'cash',
    ])->assertSessionHasErrors('items');

    expect($product->fresh()->stock_quantity)->toBe(2)
        ->and(Sale::count())->toBe(0);
});

test('sale uses the product price stored by the server, not the submitted cart price', function () {
    $seller = parfumerieUserWithRole('Vendeur');
    $product = parfumerieTestProduct();

    $response = $this->actingAs($seller)->post(route('sales.store'), [
        'items' => [
            ['product_id' => $product->id, 'quantity' => 1, 'price' => 1],
        ],
        'payment_method' => 'cash',
        'paid_amount' => 35,
    ]);
    $sale = Sale::firstOrFail();
    $response->assertRedirect(route('sales.ticket', $sale));

    expect((float) $sale->total)->toBe(118.0)
        ->and((float) $sale->items()->firstOrFail()->price)->toBe(100.0)
        ->and((float) $sale->paid_amount)->toBe(35.0)
        ->and((float) $sale->change_amount)->toBe(0.0)
        ->and($product->fresh()->stock_quantity)->toBe(4);
});

test('manual stock cannot remove more than is available', function () {
    $manager = parfumerieUserWithRole('Gérant');
    $product = parfumerieTestProduct(stock: 2);

    $this->actingAs($manager)->post(route('stock.store'), [
        'product_id' => $product->id,
        'type' => 'out',
        'quantity' => 3,
        'movement_date' => now()->toDateString(),
    ])->assertSessionHasErrors('quantity');

    expect($product->fresh()->stock_quantity)->toBe(2)
        ->and(StockMovement::count())->toBe(0);
});

test('editing a product stock level writes an adjustment into the stock history', function () {
    $manager = parfumerieUserWithRole('Gérant');
    $product = parfumerieTestProduct(stock: 2);

    $this->actingAs($manager)->put(route('products.update', $product), [
        'name' => $product->name,
        'reference' => $product->reference,
        'gender' => $product->gender,
        'volume' => $product->volume,
        'purchase_price' => $product->purchase_price,
        'selling_price' => $product->selling_price,
        'stock_quantity' => 3,
        'alert_threshold' => $product->alert_threshold,
    ])->assertRedirect(route('products.index'));

    $movement = StockMovement::firstOrFail();

    expect($product->fresh()->stock_quantity)->toBe(3)
        ->and($movement->type)->toBe('adjustment')
        ->and($movement->quantity)->toBe(1)
        ->and($movement->stock_before)->toBe(2)
        ->and($movement->stock_after)->toBe(3);
});

test('purchase receipts create traceable batches and update product stock', function () {
    $manager = parfumerieUserWithRole('Gérant');
    $product = parfumerieTestProduct(stock: 0);
    $expiry = today()->addDays(150)->toDateString();

    $this->actingAs($manager)->post(route('purchases.store'), [
        'purchased_at' => today()->toDateString(),
        'items' => [[
            'product_id' => $product->id,
            'quantity' => 4,
            'purchase_price' => 42,
            'lot_number' => 'LOT-ROSE-204',
            'expires_at' => $expiry,
        ]],
    ])->assertRedirect();

    $batch = StockBatch::firstOrFail();

    expect($product->fresh()->stock_quantity)->toBe(4)
        ->and($batch->lot_number)->toBe('LOT-ROSE-204')
        ->and($batch->quantity)->toBe(4)
        ->and($batch->expires_at->toDateString())->toBe($expiry);

    $this->get(route('stock.lots'))
        ->assertOk()
        ->assertSee('LOT-ROSE-204')
        ->assertSee($product->name);
});

test('sales use the nearest non-expired batch first and cancellation restores exact lots', function () {
    $admin = parfumerieUserWithRole('Administrateur');
    $product = parfumerieTestProduct(stock: 0);
    $firstExpiryBatch = StockInventory::receive($product, 2, 40, 'LOT-NEAR', today()->addDays(20)->toDateString());
    $nextExpiryBatch = StockInventory::receive($product, 3, 45, 'LOT-FAR', today()->addDays(60)->toDateString());

    $this->actingAs($admin)->post(route('sales.store'), [
        'items' => [['product_id' => $product->id, 'quantity' => 3]],
        'payment_method' => 'cash',
    ])->assertRedirect();

    $sale = Sale::firstOrFail();
    expect($firstExpiryBatch->fresh()->quantity)->toBe(0)
        ->and($nextExpiryBatch->fresh()->quantity)->toBe(2)
        ->and($product->fresh()->stock_quantity)->toBe(2);

    $this->get(route('sales.show', $sale))
        ->assertOk()
        ->assertSee('LOT-NEAR')
        ->assertSee('LOT-FAR');

    $this->delete(route('sales.destroy', $sale))->assertRedirect(route('sales.index'));

    expect($firstExpiryBatch->fresh()->quantity)->toBe(2)
        ->and($nextExpiryBatch->fresh()->quantity)->toBe(3)
        ->and($product->fresh()->stock_quantity)->toBe(5);
});

test('sales cannot allocate expired perfume batches', function () {
    $seller = parfumerieUserWithRole('Vendeur');
    $product = parfumerieTestProduct(stock: 0);
    StockInventory::receive($product, 2, 40, 'LOT-EXPIRED', today()->subDay()->toDateString());
    StockInventory::receive($product, 2, 40, 'LOT-VALID', today()->addDays(30)->toDateString());

    $this->actingAs($seller)->post(route('sales.store'), [
        'items' => [['product_id' => $product->id, 'quantity' => 3]],
        'payment_method' => 'cash',
    ])->assertSessionHasErrors('items');

    expect(Sale::count())->toBe(0)
        ->and($product->fresh()->stock_quantity)->toBe(4);
});

test('product records can be classified by concentration and show promo-aware margin', function () {
    $manager = parfumerieUserWithRole('Gérant');

    $this->actingAs($manager)->post(route('products.store'), [
        'name' => 'Parfum EDP',
        'reference' => 'PRF-EDP-001',
        'gender' => 'unisexe',
        'volume' => 50,
        'concentration' => 'EDP',
        'purchase_price' => 40,
        'selling_price' => 100,
        'promo_price' => 90,
        'stock_quantity' => 3,
        'alert_threshold' => 1,
        'is_active' => true,
    ])->assertRedirect(route('products.index'));

    $product = Product::where('reference', 'PRF-EDP-001')->firstOrFail();

    expect($product->concentration_label)->toBe('Eau de parfum (EDP)')
        ->and($product->margin)->toBe(50.0)
        ->and($product->margin_percent)->toBe(55.6)
        ->and($product->batches()->sum('quantity'))->toBe(3);

    $this->get(route('products.index', ['concentration' => 'EDP']))
        ->assertOk()
        ->assertSee('Parfum EDP')
        ->assertSee('Marge');
});

test('demo products receive opening batches that match their stock quantities', function () {
    $this->seed(DemoDataSeeder::class);

    $productsOutOfSync = Product::query()
        ->whereRaw('stock_quantity != (SELECT COALESCE(SUM(quantity), 0) FROM stock_batches WHERE stock_batches.product_id = products.id)')
        ->count();

    expect($productsOutOfSync)->toBe(0);
});
