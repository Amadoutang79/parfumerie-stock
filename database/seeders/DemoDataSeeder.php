<?php
namespace Database\Seeders;

use App\Models\{Brand, Category, Product, Supplier, Customer, User};
use App\Services\StockInventory;
use Illuminate\Database\Seeder;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $brands = ['Chanel','Dior','Yves Saint Laurent','Gucci','Versace','Lancôme','Hugo Boss'];
        foreach ($brands as $b) Brand::firstOrCreate(['name' => $b]);

        $cats = ['Eau de Toilette','Eau de Parfum','Extrait','Coffret','Déodorant'];
        foreach ($cats as $c) Category::firstOrCreate(['name' => $c]);

        $suppliers = [
            ['name' => 'Parfums Distribution SARL', 'phone' => '+225 07 00 00 01', 'whatsapp' => '+225 07 00 00 01'],
            ['name' => 'Beauté Import', 'phone' => '+225 07 00 00 02', 'whatsapp' => '+225 07 00 00 02'],
        ];
        foreach ($suppliers as $s) Supplier::firstOrCreate(['name' => $s['name']], $s);

        $customers = [
            ['name' => 'Awa Traoré', 'phone' => '+225 01 02 03 04'],
            ['name' => 'Kouassi Jean', 'phone' => '+225 05 06 07 08'],
        ];
        foreach ($customers as $c) Customer::firstOrCreate(['name' => $c['name']], $c);

        $faker = \Faker\Factory::create('fr_FR');
        for ($i = 0; $i < 20; $i++) {
            $purchase = $faker->numberBetween(8000, 25000);
            $stock = $faker->numberBetween(0, 40);
            $product = Product::create([
                'name'             => $faker->randomElement(['Sauvage','Bleu','Chanel N°5','Miss Dior','Libre','Invictus','Eros','La Vie est Belle']).' '.$faker->numberBetween(1, 99),
                'reference'        => Product::generateReference(),
                'brand_id'         => Brand::inRandomOrder()->first()->id,
                'category_id'      => Category::inRandomOrder()->first()->id,
                'gender'           => $faker->randomElement(['homme','femme','unisexe']),
                'volume'           => $faker->randomElement([30,50,100]),
                'concentration'    => $faker->randomElement(['EDT','EDP','EXTRAIT','EDC']),
                'purchase_price'   => $purchase,
                'selling_price'    => $purchase + $faker->numberBetween(3000, 10000),
                'stock_quantity'   => 0,
                'alert_threshold'  => 5,
            ]);

            if ($stock > 0) {
                StockInventory::receive($product, $stock, $purchase, 'DEMO-'.$product->reference);
            }
        }
    }
}