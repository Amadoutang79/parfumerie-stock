<?php

use App\Models\Product;
use App\Models\Sale;
use App\Models\Setting;
use App\Models\StockBatch;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

function settingsTestAdmin(): User
{
    $admin = User::factory()->create();
    $admin->assignRole('Administrateur');

    return $admin;
}

function settingsTestValues(array $overrides = []): array
{
    return array_merge([
        'company_name' => 'Parfumerie Test',
        'tagline' => 'L’élégance au quotidien',
        'email' => 'contact@parfumerie.test',
        'website' => 'https://parfumerie.test',
        'phone' => '+223 70 00 00 00',
        'tax_id' => 'ML-123456',
        'address' => 'Rue du Commerce',
        'city' => 'Bamako',
        'country' => 'ML',
        'description' => 'Parfums de qualité.',
        'currency' => 'FCFA',
        'currency_symbol' => 'F',
        'vat_rate' => 18,
        'timezone' => 'Africa/Bamako',
        'date_format' => 'd/m/Y',
        'primary_color' => '#3B82F6',
        'invoice_email' => 'factures@parfumerie.test',
        'email_notifications' => '1',
        'tab' => 'information',
    ], $overrides);
}

test('only administrators can access company settings', function () {
    $admin = settingsTestAdmin();
    $seller = User::factory()->create();
    $seller->assignRole('Vendeur');

    $this->actingAs($admin)->get(route('settings.index'))
        ->assertOk()
        ->assertSee('Informations de l’entreprise')
        ->assertSee('Logo &amp; apparence', false);

    $this->actingAs($seller)->get(route('settings.index'))->assertForbidden();
});

test('company settings are validated, cached, and used for sale tax and display', function () {
    $admin = settingsTestAdmin();
    Storage::fake('public');

    $this->actingAs($admin)->put(route('settings.update'), settingsTestValues([
        'city' => 'Dakar',
        'country' => 'SN',
        'currency' => 'EUR',
        'currency_symbol' => '€',
        'vat_rate' => 20,
        'timezone' => 'Africa/Dakar',
        'date_format' => 'Y-m-d',
        'primary_color' => '#8B5CF6',
        'tab' => 'preferences',
        'logo' => UploadedFile::fake()->image('logo.png', 128, 128)->size(4096),
    ]))->assertRedirect(route('settings.index', ['tab' => 'preferences']));

    $settings = Setting::current();
    expect($settings->city)->toBe('Dakar')
        ->and($settings->country)->toBe('SN')
        ->and((float) $settings->vat_rate)->toBe(20.0)
        ->and($settings->primary_color)->toBe('#8B5CF6')
        ->and($settings->logo_path)->toStartWith('logos/')
        ->and(Storage::disk('public')->exists($settings->logo_path))->toBeTrue()
        ->and(money(1000))->toBe('1 000 €')
        ->and(company_date('2026-10-06'))->toBe('2026-10-06');

    $product = Product::create([
        'name' => 'Parfum taxe',
        'reference' => 'PRF-TAX-001',
        'gender' => 'unisexe',
        'volume' => 50,
        'purchase_price' => 40,
        'selling_price' => 100,
        'stock_quantity' => 5,
        'alert_threshold' => 2,
        'is_active' => true,
    ]);
    StockBatch::create([
        'product_id' => $product->id,
        'lot_number' => 'TEST-OPENING',
        'initial_quantity' => 5,
        'quantity' => 5,
        'unit_cost' => 40,
    ]);

    $response = $this->post(route('sales.store'), [
        'items' => [['product_id' => $product->id, 'quantity' => 1, 'price' => 1]],
        'payment_method' => 'cash',
    ]);

    $sale = Sale::firstOrFail();
    $response->assertRedirect(route('sales.ticket', $sale));
    expect((float) $sale->vat_rate)->toBe(20.0)
        ->and((float) $sale->vat_amount)->toBe(20.0)
        ->and((float) $sale->total)->toBe(120.0);

    $this->get(route('sales.ticket', $sale))
        ->assertOk()
        ->assertSee('Parfumerie Test')
        ->assertSee(Storage::disk('public')->url($settings->logo_path))
        ->assertSee('TVA (20%)');
});

test('invalid settings are rejected without changing the saved settings', function () {
    $admin = settingsTestAdmin();
    $initialName = Setting::current()->company_name;

    $this->actingAs($admin)->put(route('settings.update'), settingsTestValues([
        'primary_color' => 'blue',
        'vat_rate' => 101,
        'website' => 'javascript:alert(1)',
    ]))->assertSessionHasErrors(['primary_color', 'vat_rate', 'website']);

    expect(Setting::current()->company_name)->toBe($initialName);
});

test('company logos can be uploaded and removed', function () {
    Storage::fake('public');
    $admin = settingsTestAdmin();

    $this->actingAs($admin)->put(route('settings.update'), settingsTestValues([
        'logo' => UploadedFile::fake()->image('logo.png', 128, 128),
    ]))->assertRedirect();

    $logoPath = Setting::current()->logo_path;
    expect($logoPath)->toStartWith('logos/');
    Storage::disk('public')->assertExists($logoPath);

    $this->delete(route('settings.logo.destroy'))->assertRedirect();
    expect(Setting::current()->logo_path)->toBeNull();
    Storage::disk('public')->assertMissing($logoPath);
});
